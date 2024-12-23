<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use App\Mailer\AppMailer;
use App\Services\ChangeSellingPriceService;
use App\Services\ProductQuantityService;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait EditProductQuantityTrait
{

    use UpdateOrderDetailsTrait;

    public function editProductQuantity()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $productQuantity = trim($this->getRequest()->getData('productQuantity'));
        $productQuantity = Configure::read('app.numberHelper')->parseFloatRespectingLocale($productQuantity);

        if (! is_numeric($orderDetailId) || !$productQuantity || $productQuantity < 0) {
            $message = __d('admin', 'The_delivered_quantity_is_not_valid.');
            if (! is_numeric($orderDetailId)) {
                $message = 'input format wrong';
            }
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            contain: [
                'Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailPurchasePrices',
                'OrderDetailUnits',
            ]
        )->first();

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $objectOrderDetailUnit = clone $oldOrderDetail->order_detail_unit;

        $newProductPrice = Configure::read('app.pricePerUnitHelper')->getPrice($oldOrderDetail->order_detail_unit->price_incl_per_unit, $oldOrderDetail->order_detail_unit->unit_amount, $productQuantity);
        if ($oldOrderDetail->order_detail_unit->product_quantity_in_units > 0) {
            $toleranceFactor = 100;
            $oldToNewQuantityRelation = $productQuantity / $oldOrderDetail->order_detail_unit->product_quantity_in_units;
            if ($oldToNewQuantityRelation < 1 / $toleranceFactor || $oldToNewQuantityRelation > $toleranceFactor) {
                $message = __d('admin', 'The_new_price_would_be_{0}_for_{1}_please_check_the_unit.', [
                    '<b>' . Configure::read('app.numberHelper')->formatAsCurrency($newProductPrice) . '</b>',
                    '<b>' . Configure::read('app.numberHelper')->formatUnitAsDecimal($productQuantity) . ' ' . $oldOrderDetail->order_detail_unit->unit_name . '</b>',
                ]);
                $this->set([
                    'status' => 0,
                    'msg' => $message,
                ]);
                $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
                return;
            }
        }

        $quantityWasChanged = $oldOrderDetail->order_detail_unit->product_quantity_in_units != $productQuantity;

        if ($quantityWasChanged && !empty($oldOrderDetail->order_detail_purchase_price)) {
            $productPurchasePrice = round((float) $oldOrderDetail->order_detail_unit->purchase_price_incl_per_unit / $oldOrderDetail->order_detail_unit->unit_amount * $productQuantity, 2);
            $this->changeOrderDetailPurchasePrice($oldOrderDetail->order_detail_purchase_price, $productPurchasePrice, $object->product_amount);
        }

        $newOrderDetail = $oldOrderDetail;
        if ($quantityWasChanged) {
            $newOrderDetail = (new ChangeSellingPriceService())->changeOrderDetailPriceDepositTax($object, $newProductPrice, $object->product_amount);
        }
        $this->changeOrderDetailQuantity($objectOrderDetailUnit, $productQuantity);

        $unitsTable = TableRegistry::getTableLocator()->get('Units');
        $unitObject = $unitsTable->getUnitsObject($oldOrderDetail->product_id, $oldOrderDetail->product_attribute_id);

        $productQuantityService = new ProductQuantityService();
        $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($oldOrderDetail->product, $unitObject);
        if ($isAmountBasedOnQuantityInUnits) {
            $increaseQuantity = $oldOrderDetail->order_detail_unit->product_quantity_in_units - $productQuantity;
            $productQuantityService->changeStockAvailable($oldOrderDetail, $increaseQuantity);
        }

        $message = __d('admin', 'The_weight_of_the_ordered_product_{0}_was_successfully_apapted_from_{1}_to_{2}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            Configure::read('app.numberHelper')->formatUnitAsDecimal($oldOrderDetail->order_detail_unit->product_quantity_in_units) . ' ' . $oldOrderDetail->order_detail_unit->unit_name,
            Configure::read('app.numberHelper')->formatUnitAsDecimal($productQuantity) . ' ' . $oldOrderDetail->order_detail_unit->unit_name
        ]);

        // send email to customer if price was changed
        if ($quantityWasChanged && Configure::read('app.sendEmailWhenOrderDetailQuantityChanged')) {
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_quantity_changed');
            $email->setTo($oldOrderDetail->customer->email)
            ->setSubject(__d('admin', 'Weight_adapted_for_"0":', [$oldOrderDetail->product_name]) . ' ' . Configure::read('app.numberHelper')->formatUnitAsDecimal($productQuantity) . ' ' . $oldOrderDetail->order_detail_unit->unit_name)
            ->setViewVars([
                'oldOrderDetail' => $oldOrderDetail,
                'newsletterCustomer' => $oldOrderDetail->customer,
                'newProductQuantityInUnits' => $productQuantity,
                'newOrderDetail' => $newOrderDetail,
                'identity' => $this->identity
            ]);
            $email->addToQueue();

            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->customer->name . '</b>']);

            $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
            $sendOrderedProductPriceChangedNotification = $manufacturersTable->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);

            if (! $this->identity->isManufacturer() && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $oldOrderDetail->customer->name . '</b>',
                    '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
                ]);
                $email->setTo($oldOrderDetail->product->manufacturer->address_manufacturer->email);
                $orderDetailForManufacturerEmail = $oldOrderDetail;
                $orderDetailForManufacturerEmail->customer = $oldOrderDetail->product->manufacturer->address_manufacturer;
                $email->setViewVars([
                    'oldOrderDetail' => $orderDetailForManufacturerEmail,
                ]);
                $email->addToQueue();
            }

            $message .= $emailMessage;

        }

        if ($quantityWasChanged) {
            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('order_detail_product_quantity_changed', $this->identity->getId(), $orderDetailId, 'order_details', $message);
            $this->Flash->success($message);
        }

        $this->getRequest()->getSession()->write('highlightedRowId', $orderDetailId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

}

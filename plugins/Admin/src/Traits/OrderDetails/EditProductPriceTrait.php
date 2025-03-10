<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Cake\Utility\Text;
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

trait EditProductPriceTrait 
{

    use UpdateOrderDetailsTrait;

    public function editProductPrice(): void
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $editPriceReason = strip_tags(html_entity_decode($this->getRequest()->getData('editPriceReason')));
        $sendEmailToCustomer = (bool) $this->getRequest()->getData('sendEmailToCustomer');

        $productPrice = trim($this->getRequest()->getData('productPrice'));
        $productPrice = Configure::read('app.numberHelper')->parseFloatRespectingLocale($productPrice);

        if (! is_numeric($orderDetailId) || $productPrice === false) {
            $message = __d('admin', 'The_price_is_not_valid.');
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
            ]
        )->first();

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = (new ChangeSellingPriceService())->changeOrderDetailPriceDepositTax($object, $productPrice, $object->product_amount);

        $unitsTable = TableRegistry::getTableLocator()->get('Units');
        $unitObject = $unitsTable->getUnitsObject($object->product_id, $object->product_attribute_id);

        $productQuantityService = new ProductQuantityService();
        $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($object->product, $unitObject);

        $message = __d('admin', 'The_price_of_the_ordered_product_{0}_was_successfully_apapted_from_{1}_to_{2}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            Configure::read('app.numberHelper')->formatAsCurrency($oldOrderDetail->total_price_tax_incl),
            Configure::read('app.numberHelper')->formatAsCurrency($productPrice),
        ]);

        $emailRecipients = [];

        if ($sendEmailToCustomer) {
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_price_changed');
            $email->setTo($oldOrderDetail->customer->email)
            ->setSubject(__d('admin', 'Ordered_price_adapted') . ': ' . $oldOrderDetail->product_name)
            ->setViewVars([
                'oldOrderDetail' => $oldOrderDetail,
                'newsletterCustomer' => $oldOrderDetail->customer,
                'newOrderDetail' => $newOrderDetail,
                'identity' => $this->identity,
                'editPriceReason' => $editPriceReason,
                'isAmountBasedOnQuantityInUnits' => $isAmountBasedOnQuantityInUnits,
            ]);
            $email->addToQueue();
            $emailRecipients[] = $oldOrderDetail->customer->name;
        }

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $sendOrderedProductPriceChangedNotification = $manufacturersTable->getOptionSendOrderedProductPriceChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_price_changed_notification);
        if (! $this->identity->isManufacturer() && $oldOrderDetail->total_price_tax_incl > 0.00 && $sendOrderedProductPriceChangedNotification) {
            $orderDetailForManufacturerEmail = $oldOrderDetail;
            $orderDetailForManufacturerEmail->customer = $oldOrderDetail->product->manufacturer->address_manufacturer;
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_price_changed');
            $email->setTo($oldOrderDetail->product->manufacturer->address_manufacturer->email)
            ->setSubject(__d('admin', 'Ordered_price_adapted') . ': ' . $oldOrderDetail->product_name)
            ->setViewVars([
                'oldOrderDetail' => $orderDetailForManufacturerEmail,
                'newOrderDetail' => $newOrderDetail,
                'identity' => $this->identity,
                'editPriceReason' => $editPriceReason,
                'isAmountBasedOnQuantityInUnits' => $isAmountBasedOnQuantityInUnits,
            ]);
            $email->addToQueue();
            $emailRecipients[] = $oldOrderDetail->product->manufacturer->name;
        }

        if (!$isAmountBasedOnQuantityInUnits) {
            $message .= ' ' . __d('admin', 'Amount').': <b>' . $oldOrderDetail->product_amount . '</b>';
        }

        if ($editPriceReason != '') {
            $message .= ' ' . __d('admin', 'Reason').': <b>"' . $editPriceReason . '"</b>';
        }

        if (!empty($emailRecipients)) {
            $message .= ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . Text::toList($emailRecipients) . '</b>']);
        }

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('order_detail_product_price_changed', $this->identity->getId(), $orderDetailId, 'order_details', $message);
        $this->Flash->success($message);

        $this->getRequest()->getSession()->write('highlightedRowId', $orderDetailId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}

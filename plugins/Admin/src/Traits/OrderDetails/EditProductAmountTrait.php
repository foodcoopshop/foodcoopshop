<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use App\Mailer\AppMailer;
use App\Services\ChangeSellingPriceService;
use App\Model\Entity\OrderDetail;

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

trait EditProductAmountTrait 
{

    use UpdateOrderDetailsTrait;

    public function editProductAmount()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $productAmount = trim($this->getRequest()->getData('productAmount'));
        $editAmountReason = strip_tags(html_entity_decode($this->getRequest()->getData('editAmountReason')));

        if (! is_numeric($orderDetailId) || ! is_numeric($productAmount) || $productAmount < 1) {
            $message = __d('admin', 'The_amount_is_not_valid.');
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

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all',
            conditions: [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            contain: [
                'Customers',
                'Products.Manufacturers',
                'Products.Manufacturers.AddressManufacturers',
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
            ]
        )->first();

        $productPrice = $oldOrderDetail->total_price_tax_incl / $oldOrderDetail->product_amount * $productAmount;

        if (!empty($oldOrderDetail->order_detail_purchase_price)) {
            $productPurchasePrice = $oldOrderDetail->order_detail_purchase_price->total_price_tax_incl / $oldOrderDetail->product_amount * $productAmount;
            $this->changeOrderDetailPurchasePrice($oldOrderDetail->order_detail_purchase_price, $productPurchasePrice, $productAmount);
        }

        $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
        $newOrderDetail = (new ChangeSellingPriceService())->changeOrderDetailPriceDepositTax($object, $productPrice, $productAmount);
        $newQuantity = $this->increaseQuantityForProduct($newOrderDetail, $oldOrderDetail->product_amount);

        if (!empty($object->order_detail_unit)) {
            $productQuantity = $oldOrderDetail->order_detail_unit->product_quantity_in_units / $oldOrderDetail->product_amount * $productAmount;
            $this->changeOrderDetailQuantity($object->order_detail_unit, $productQuantity);
        }

        $message = __d('admin', 'The_amount_of_the_ordered_product_{0}_was_successfully_changed_from_{1}_to_{2}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            $oldOrderDetail->product_amount,
            $productAmount
        ]);

        // send email to customer
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('Admin.order_detail_amount_changed');
        $email->setTo($oldOrderDetail->customer->email)
        ->setSubject(__d('admin', 'Ordered_amount_adapted') . ': ' . $oldOrderDetail->product_name)
        ->setViewVars([
            'oldOrderDetail' => $oldOrderDetail,
            'newsletterCustomer' => $oldOrderDetail->customer,
            'newOrderDetail' => $newOrderDetail,
            'identity' => $this->identity,
            'editAmountReason' => $editAmountReason
        ]);
        $email->addToQueue();

        $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $oldOrderDetail->customer->name . '</b>']);

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $sendOrderedProductAmountChangedNotification = $this->Manufacturer->getOptionSendOrderedProductAmountChangedNotification($oldOrderDetail->product->manufacturer->send_ordered_product_amount_changed_notification);

        if (! $this->identity->isManufacturer() && $oldOrderDetail->order_state == OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER && $sendOrderedProductAmountChangedNotification) {
            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                '<b>' . $oldOrderDetail->customer->name . '</b>',
                '<b>' . $oldOrderDetail->product->manufacturer->name . '</b>'
            ]);
            $orderDetailForManufacturerEmail = $oldOrderDetail;
            $orderDetailForManufacturerEmail->customer = $oldOrderDetail->product->manufacturer->address_manufacturer;
            $email->setViewVars([
                'oldOrderDetail' => $orderDetailForManufacturerEmail,
            ]);
            $email->setTo($oldOrderDetail->product->manufacturer->address_manufacturer->email);
            $email->addToQueue();
        }

        $message .= $emailMessage;

        if ($editAmountReason != '') {
            $message .= ' ' . __d('admin', 'Reason') . ': <b>"' . $editAmountReason . '"</b>';
        }

        if ($newQuantity !== false) {
            $message .= ' ' . __d('admin', 'The_stock_was_increased_to_{0}.', [
                Configure::read('app.numberHelper')->formatAsDecimal($newQuantity, 0)
            ]);
        }

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_detail_product_amount_changed', $this->identity->getId(), $orderDetailId, 'order_details', $message);

        $this->Flash->success($message);

        $this->getRequest()->getSession()->write('highlightedRowId', $orderDetailId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}

<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use App\Mailer\AppMailer;
use App\Model\Entity\OrderDetail;
use App\Services\ProductQuantityService;
use Cake\Datasource\FactoryLocator;

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

trait DeleteTrait
{

    use UpdateOrderDetailsTrait;

    public function delete()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $orderDetailIds = $this->getRequest()->getData('orderDetailIds');
        $cancellationReason = strip_tags(html_entity_decode($this->getRequest()->getData('cancellationReason')));

        if (!(is_array($orderDetailIds))) {
            $this->set([
                'status' => 0,
                'msg' => 'param needs to be an array, given: ' . $orderDetailIds,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $flashMessage = '';
        $message = '';

        foreach ($orderDetailIds as $orderDetailId) {
            $orderDetail = $this->OrderDetail->find('all',
                conditions: [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ],
                contain: [
                    'Customers',
                    'Products.StockAvailables',
                    'Products.Manufacturers',
                    'Products.Manufacturers.AddressManufacturers',
                    'ProductAttributes.StockAvailables',
                    'OrderDetailUnits',
                    'OrderDetailPurchasePrices',
                ]
            )->first();

            $message = __d('admin', 'Product_{0}_from_manufacturer_{1}_with_a_price_of_{2}_ordered_on_{3}_was_successfully_cancelled.', [
                '<b>' . $orderDetail->product_name . '</b>',
                '<b>' . $orderDetail->product->manufacturer->name . '</b>',
                Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_incl),
                $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'))
            ]);

            $this->OrderDetail->deleteOrderDetail($orderDetail);


            $unitsTable = FactoryLocator::get('Table')->get('Units');
            $unitObject = $unitsTable->getUnitsObjectByOrderDetail($orderDetail);

            $productQuantityService = new ProductQuantityService();
            $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($orderDetail->product, $unitObject);
            if ($isAmountBasedOnQuantityInUnits) {
                $newQuantity = $productQuantityService->changeStockAvailable($orderDetail, $orderDetail->order_detail_unit->product_quantity_in_units);
            } else {
                $newQuantity = $this->increaseQuantityForProduct($orderDetail, $orderDetail->product_amount * 2);
            }

            // send email to customer
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_deleted');
            $email->setTo($orderDetail->customer->email)
            ->setSubject(__d('admin', 'Product_was_cancelled').': ' . $orderDetail->product_name)
            ->setViewVars([
                'orderDetail' => $orderDetail,
                'newsletterCustomer' => $orderDetail->customer,
                'identity' => $this->identity,
                'cancellationReason' => $cancellationReason
            ]);
            $email->addToQueue();

            $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}.', ['<b>' . $orderDetail->customer->name . '</b>']);

            $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
            $sendOrderedProductDeletedNotification = $this->Manufacturer->getOptionSendOrderedProductDeletedNotification($orderDetail->product->manufacturer->send_ordered_product_deleted_notification);

            if (! $this->identity->isManufacturer() && $orderDetail->order_state == OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER && $sendOrderedProductDeletedNotification) {
                $emailMessage = ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_the_manufacturer_{1}.', [
                    '<b>' . $orderDetail->customer->name . '</b>',
                    '<b>' . $orderDetail->product->manufacturer->name . '</b>'
                ]);
                $email->setTo($orderDetail->product->manufacturer->address_manufacturer->email);
                $orderDetailForManufacturerEmail = $orderDetail;
                $orderDetailForManufacturerEmail->customer = $orderDetail->product->manufacturer->address_manufacturer;
                $email->setViewVars([
                    'orderDetail' => $orderDetailForManufacturerEmail,
                ]);
                $email->addToQueue();
            }

            $message .= $emailMessage;

            if ($cancellationReason != '') {
                $message .= ' '.__d('admin', 'Reason').': <b>"' . $cancellationReason . '"</b>';
            }

            if ($newQuantity !== false) {
                $message .= ' ' . __d('admin', 'The_stock_was_increased_to_{0}.', [
                    Configure::read('app.numberHelper')->formatAsDecimal($newQuantity, 0)
                ]);
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('order_detail_cancelled', $this->identity->getId(), $orderDetail->product_id, 'products', $message);
        }

        $flashMessage = $message;
        $orderDetailsCount = count($orderDetailIds);
        if ($orderDetailsCount > 1) {
            $flashMessage = $orderDetailsCount . ' ' . __d('admin', '{0,plural,=1{product_was_cancelled_succesfully.} other{products_were_cancelled_succesfully.}}', $orderDetailsCount);
        }
        $this->Flash->success($flashMessage);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}

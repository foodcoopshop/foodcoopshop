<?php
declare(strict_types=1);

namespace App\Services;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Mailer\AppMailer;
use Cake\Routing\Router;
use App\Model\Entity\OrderDetail;
use Admin\Traits\OrderDetails\UpdateOrderDetailsTrait;
use Cake\Utility\Text;
use PSpell\Config;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class OrderDetailCancellationService
{
    use UpdateOrderDetailsTrait;
    
    public function delete(array $orderDetailIds, string $cancellationReason): string
    {
        $identity = null;
        if (Router::getRequest() !== null) {
            $identity = Router::getRequest()->getAttribute('identity');
        }
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $flashMessage = '';
        $message = '';

        foreach ($orderDetailIds as $orderDetailId) {
            $orderDetail = $orderDetailsTable->find('all',
                conditions: [
                    'OrderDetails.id_order_detail' => $orderDetailId,
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

            $orderDetailsTable->deleteOrderDetail($orderDetail);

            $unitsTable = TableRegistry::getTableLocator()->get('Units');
            $unitObject = $unitsTable->getUnitsObject($orderDetail->product_id, $orderDetail->product_attribute_id);

            $productQuantityService = new ProductQuantityService();
            $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($orderDetail->product, $unitObject);
            if ($isAmountBasedOnQuantityInUnits) {
                $newQuantity = $productQuantityService->changeStockAvailable($orderDetail, $orderDetail->order_detail_unit->product_quantity_in_units);
            } else {
                $newQuantity = $this->increaseQuantityForProduct($orderDetail, $orderDetail->product_amount * 2);
            }

            $cancelledQuantity = $orderDetail->product_amount;
            if ($isAmountBasedOnQuantityInUnits) {
                $cancelledQuantity = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $orderDetail->order_detail_unit->product_quantity_in_units, $orderDetail->order_detail_unit->unit_name);
            }

            $recipientNames = [];
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.order_detail_deleted');
            $email->setSubject(__d('admin', 'Product_was_cancelled').': ' . $orderDetail->product_name);
            $email->setViewVars([
                'newsletterCustomer' => $orderDetail->customer,
                'identity' => $identity,
                'cancellationReason' => $cancellationReason,
                'cancelledQuantity' => $cancelledQuantity,
            ]);

            // send email to customer
            if ($orderDetail->customer->send_cancellation_email) {
                $email->setTo($orderDetail->customer->email);
                $recipientNames[] = $orderDetail->customer->name;
                $email->setViewVars([
                    'orderDetail' => $orderDetail,
                    'profileRoute' => Configure::read('app.slugHelper')->getCustomerProfile(),
                ]);
                $email->addToQueue();
            }

            $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
            $sendOrderedProductDeletedNotification = $manufacturersTable->getOptionSendOrderedProductDeletedNotification($orderDetail->product->manufacturer->send_ordered_product_deleted_notification);

            if (($identity !== null && !$identity->isManufacturer()) && $orderDetail->order_state == OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER && $sendOrderedProductDeletedNotification) {
                $recipientNames[] = $orderDetail->product->manufacturer->name;
                $email->setTo($orderDetail->product->manufacturer->address_manufacturer->email);
                $orderDetailForManufacturerEmail = $orderDetail;
                $orderDetailForManufacturerEmail->customer = $orderDetail->product->manufacturer->address_manufacturer;
                $email->setViewVars([
                    'orderDetail' => $orderDetailForManufacturerEmail,
                    'profileRoute' => Configure::read('app.slugHelper')->getManufacturerProfile(),
                ]);
                $email->addToQueue();
            }

            if (!empty($recipientNames)) {
                $message .= __d('admin', 'An_email_was_sent_to_{0}.', [Text::toList($recipientNames)]);
            }

            if ($cancellationReason != '') {
                $message .= ' '.__d('admin', 'Reason').': <b>"' . $cancellationReason . '"</b>';
            }

            if ($newQuantity !== false) {
                $formattedNewQuantity = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $newQuantity, $unitObject->name ?? '');
                $message .= ' ' . __d('admin', 'The_stock_was_increased_to_{0}.', [
                    $formattedNewQuantity,
                ]);
            }

            $actionLogsTable = TableRegistry::getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave('order_detail_cancelled', $identity?->getId(), $orderDetail->product_id, 'products', $message);
        }

        $flashMessage = $message;
        $orderDetailsCount = count($orderDetailIds);
        if ($orderDetailsCount > 1) {
            $flashMessage = $orderDetailsCount . ' ' . __d('admin', '{0,plural,=1{product_was_cancelled_succesfully.} other{products_were_cancelled_succesfully.}}', $orderDetailsCount);
        }

        return $flashMessage;
        
    }
}

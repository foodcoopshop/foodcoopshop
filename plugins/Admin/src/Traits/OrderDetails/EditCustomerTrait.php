<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use App\Mailer\AppMailer;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait EditCustomerTrait {

    use UpdateOrderDetailsTrait;

    protected $Customer;

    public function editCustomer()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $customerId = (int) $this->getRequest()->getData('customerId');
        $editCustomerReason = strip_tags(html_entity_decode($this->getRequest()->getData('editCustomerReason')));
        $amount = (int) $this->getRequest()->getData('amount');
        $sendEmailToCustomers = (bool) $this->getRequest()->getData('sendEmailToCustomers');

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all', [
            'conditions' => [
                'OrderDetails.id_order_detail' => $orderDetailId
            ],
            'contain' => [
                'Customers',
                'Products.Manufacturers',
                'OrderDetailUnits',
                'OrderDetailPurchasePrices',
            ]
        ])->first();

        $this->Customer = $this->getTableLocator()->get('Customers');
        $newCustomer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ]
        ])->first();

        $errors = [];
        if (empty($newCustomer)) {
            $errors[] = __d('admin', 'Please_select_a_new_member.');
        } else {
            if ($newCustomer->id_customer == $oldOrderDetail->id_customer) {
                $errors[] = __d('admin', 'The_same_member_must_not_be_selected.');
            }
        }

        if ($amount > $oldOrderDetail->product_amount || $amount < 1) {
            $errors[] = __d('admin', 'The_amount_is_not_valid.');
        }

        if ($editCustomerReason == '') {
            $errors[] = __d('admin', 'The_reason_for_changing_the_member_is_mandatory.');
        }

        if (!empty($errors)) {
            $this->set([
                'status' => 0,
                'msg' => join('<br />', $errors),
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $originalProductAmount = $oldOrderDetail->product_amount;
        $newAmountForOldOrderDetail = $oldOrderDetail->product_amount - $amount;

        if ($newAmountForOldOrderDetail > 0) {

            // order detail needs to be split up

            // 1) modify old order detail
            $pricePerUnit = $oldOrderDetail->total_price_tax_incl / $oldOrderDetail->product_amount;
            $productPrice = $pricePerUnit * $newAmountForOldOrderDetail;

            $object = clone $oldOrderDetail; // $oldOrderDetail would be changed if passed to function
            $this->changeOrderDetailPriceDepositTax($object, $productPrice, $newAmountForOldOrderDetail);

            if (!empty($object->order_detail_unit)) {
                $productQuantity = $oldOrderDetail->order_detail_unit->product_quantity_in_units / $originalProductAmount * $newAmountForOldOrderDetail;
                $this->changeOrderDetailQuantity($object->order_detail_unit, $productQuantity);
            }

            if (!empty($object->order_detail_purchase_price)) {
                $productPurchasePrice = $oldOrderDetail->order_detail_purchase_price->total_price_tax_incl / $oldOrderDetail->product_amount * $newAmountForOldOrderDetail;
                $this->changeOrderDetailPurchasePrice($object->order_detail_purchase_price, $productPurchasePrice, $newAmountForOldOrderDetail);
            }


            // 2) copy old order detail and modify it
            $newEntity = $oldOrderDetail;
            $newEntity->setNew(true);
            $newEntity->id_order_detail = null;
            $newEntity->id_customer = $customerId;
            $savedEntity = $this->OrderDetail->save($newEntity, [
                'associated' => false
            ]);

            $productPrice = $pricePerUnit * $amount;
            $this->changeOrderDetailPriceDepositTax($savedEntity, $productPrice, $amount);

            if (!empty($newEntity->order_detail_unit)) {
                $newEntity->order_detail_unit->id_order_detail = $savedEntity->id_order_detail;
                $newEntity->order_detail_unit->setNew(true);
                $newOrderDetailUnitEntity = $this->OrderDetail->OrderDetailUnits->save($newEntity->order_detail_unit);
                $savedEntity->order_detail_unit = $newOrderDetailUnitEntity;
                $productQuantity = $savedEntity->order_detail_unit->product_quantity_in_units / $originalProductAmount * $amount;
                $this->changeOrderDetailQuantity($savedEntity->order_detail_unit, $productQuantity);
            }

            if (!empty($newEntity->order_detail_purchase_price)) {
                $newEntity->order_detail_purchase_price->id_order_detail = $savedEntity->id_order_detail;
                $newEntity->order_detail_purchase_price->setNew(true);
                $newOrderDetailPurchasePriceEntity = $this->OrderDetail->OrderDetailPurchasePrices->save($newEntity->order_detail_purchase_price);
                $savedEntity->order_detail_purchase_price = $newOrderDetailPurchasePriceEntity;
                $productPurchasePrice = $productPurchasePrice / $newAmountForOldOrderDetail * $amount;
                $this->changeOrderDetailPurchasePrice($savedEntity->order_detail_purchase_price, $productPurchasePrice, $amount);
            }

        } else {

            // order detail does not need to be split up

            $this->OrderDetail->save(
                $this->OrderDetail->patchEntity(
                    $oldOrderDetail,
                    [
                        'id_customer' => $customerId
                    ]
                )
            );

        }

        $message = __d('admin', 'The_ordered_product_{0}_was_successfully_assigned_from_{1}_to_{2}.', [
            '<b>' . $oldOrderDetail->product_name . '</b>',
            Configure::read('app.htmlHelper')->getNameRespectingIsDeleted($oldOrderDetail->customer),
            '<b>' . $newCustomer->name . '</b>'
        ]);

        $amountString = '';
        if ($originalProductAmount != $amount) {
            $amountString = ' ' . __d('admin', 'Amount') . ': <b>' . $amount . '</b>';
            $message .= $amountString;
        }

        $message .= ' '.__d('admin', 'Reason').': <b>"' . $editCustomerReason . '"</b>';

        if ($sendEmailToCustomers) {
            $recipients = [
                [
                    'email' => $newCustomer->email,
                    'customer' => $newCustomer
                ],
                [
                    'email' => $oldOrderDetail->customer->email,
                    'customer' => $oldOrderDetail->customer
                ]
            ];
            // send email to customers
            foreach($recipients as $recipient) {
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('Admin.order_detail_customer_changed');
                $email->setTo($recipient['email'])
                ->setSubject(__d('admin', 'Assigned_to_another_member') . ': ' . $oldOrderDetail->product_name)
                ->setViewVars([
                    'oldOrderDetail' => $oldOrderDetail,
                    'customer' => $recipient['customer'],
                    'newsletterCustomer' => $recipient['customer'],
                    'newCustomer' => $newCustomer,
                    'editCustomerReason' => $editCustomerReason,
                    'amountString' => $amountString,
                    'appAuth' => $this->AppAuth
                ]);
                $email->addToQueue();
            }

            $message .= ' ' . __d('admin', 'An_email_was_sent_to_{0}_and_{1}.', [
                '<b>' . $oldOrderDetail->customer->name . '</b>',
                '<b>' . $newCustomer->name . '</b>'
            ]);
        }

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_detail_customer_changed', $this->AppAuth->getUserId(), $orderDetailId, 'order_details', $message);
        $this->Flash->success($message);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

}

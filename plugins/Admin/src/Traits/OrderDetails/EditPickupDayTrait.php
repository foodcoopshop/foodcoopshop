<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;
use App\Mailer\AppMailer;
use App\Model\Entity\OrderDetail;
use Cake\Http\Response;

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

trait EditPickupDayTrait 
{
    
    public function editPickupDay(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $orderDetailIds = $this->getRequest()->getData('orderDetailIds');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        $editPickupDayReason = htmlspecialchars_decode(strip_tags(trim($this->getRequest()->getData('editPickupDayReason')), '<strong><b>'));
        $sendEmail = (bool) $this->getRequest()->getData('sendEmail');
        $resetOrderState = (bool) $this->getRequest()->getData('resetOrderState');

        try {
            if (empty($orderDetailIds)) {
                throw new \Exception('error - no order detail id passed');
            }
            $errorMessages = [];
            $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
            $orderDetails = $orderDetailsTable->find('all',
                conditions: [
                    'OrderDetails.id_order_detail IN' => $orderDetailIds,
                ],
                contain: [
                    'Customers',
                    'Products.Manufacturers'
                ]
            );
            if ($orderDetails->count() != count($orderDetailIds)) {
                throw new \Exception('error - order details wrong');
            }

            $oldPickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($orderDetails->toArray()[0]->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'))));
            $newPickupDay = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($pickupDay));

            // validate only once for the first order detail
            $entity = $orderDetailsTable->patchEntity(
                $orderDetails->toArray()[0],
                [
                    'pickup_day' => $pickupDay,
                ],
                [
                    'validate' => 'pickupDay'
                ]
            );
            if ($entity->hasErrors()) {
                $errorMessages = array_merge($errorMessages, $orderDetailsTable->getAllValidationErrors($entity));
            }
            if (!empty($errorMessages)) {
                throw new \Exception(join('<br />', $errorMessages));
            }

            $customers = [];
            foreach ($orderDetails as $orderDetail) {

                $data = [
                    'pickup_day' => $pickupDay,
                ];

                if ($resetOrderState) {
                    $data['order_state'] = OrderDetail::STATE_OPEN;
                }

                $entity = $orderDetailsTable->patchEntity($orderDetail, $data);
                $orderDetailsTable->save($entity);
                if (!isset($customers[$orderDetail->id_customer])) {
                    $customers[$orderDetail->id_customer] = [];
                }
                $customers[$orderDetail->id_customer][] = $orderDetail;
            }

            if ($sendEmail) {
                foreach($customers as $orderDetails) {
                    $email = new AppMailer();
                    $email->viewBuilder()->setTemplate('Admin.order_detail_pickup_day_changed');
                    $email->setTo($orderDetails[0]->customer->email)
                    ->setSubject(__d('admin', 'The_pickup_day_of_your_order_was_changed_to').': ' . $newPickupDay)
                    ->setViewVars([
                        'orderDetails' => $orderDetails,
                        'customer' => $orderDetails[0]->customer,
                        'newsletterCustomer' => $orderDetails[0]->customer,
                        'identity' => $this->identity,
                        'oldPickupDay' => $oldPickupDay,
                        'newPickupDay' => $newPickupDay,
                        'editPickupDayReason' => $editPickupDayReason,
                    ]);
                    $email->addToQueue();
                }
            }

            $message = __d('admin', 'The_pickup_day_of_{0,plural,=1{1_product} other{#_products}}_was_changed_successfully_to_{1}.', [
                count($orderDetailIds),
                '<b>'.$newPickupDay.'</b>',
            ]);

            if ($sendEmail) {
                $message .= ' ' . __d('admin', '{0,plural,=1{1_customer} other{#_customers}}_were_notified.', [count($customers)]);
            }

            if ($editPickupDayReason != '') {
                $message .= ' ' . __d('admin', 'Reason') . ': <b>"' . $editPickupDayReason . '"</b>';
            }

            $this->Flash->success($message);

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave('order_detail_pickup_day_changed', $this->identity->getId(), 0, 'order_details', $message . ' Ids: ' . join(', ', $orderDetailIds));

            $this->set([
                'result' => [],
                'status' => true,
                'msg' => 'ok',
            ]);
            $this->viewBuilder()->setOption('serialize', ['result', 'status', 'msg']);

        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        return null;

    }

}

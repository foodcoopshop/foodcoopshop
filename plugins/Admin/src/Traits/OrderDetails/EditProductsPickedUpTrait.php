<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use Cake\Core\Configure;

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

trait EditProductsPickedUpTrait 
{

    public function editProductsPickedUp(): void
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $customerIds = $this->getRequest()->getData('customerIds');
        $state = (int) $this->getRequest()->getData('state');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $pickupDaysTable = $this->getTableLocator()->get('PickupDays');
        $pickupDaysTable->setPrimaryKey(['customer_id', 'pickup_day']);

        $errorMessages = [];
        $result = false;
        foreach($customerIds as $customerId) {

            if ($state) {

                $orderDetailsWithUnchangedWeight = $orderDetailsTable->find('all',
                    conditions: [
                        'OrderDetails.id_customer' => $customerId,
                        'OrderDetails.pickup_day' => $pickupDay,
                        'OrderDetailUnits.mark_as_saved' => APP_OFF,
                    ],
                    contain: [
                        'OrderDetailUnits',
                        'Customers',
                    ]
                )->toArray();

                if (count($orderDetailsWithUnchangedWeight) > 0) {
                    $errorMessages[] = [
                        'customerName' => $orderDetailsWithUnchangedWeight[0]->customer->name,
                        'orderDetailsWithUnchangedWeight' => count($orderDetailsWithUnchangedWeight),
                    ];
                }

            }
            $result = $pickupDaysTable->changeState($customerId, $pickupDay, $state);
        }

        if (!empty($errorMessages)) {
            if (count($errorMessages) == 1) {
                $message = __d('admin', 'The_customer_{0}_still_has_{1,plural,=1{1_product} other{#_products}}_with_unchanged_weight.', [
                    '<b>' . $errorMessages[0]['customerName'] . '</b>',
                    $errorMessages[0]['orderDetailsWithUnchangedWeight'],
                ]);
            } else {
                $message = __d('admin', 'The_following_customers_still_have_products_with_unchanged_weight:');
                $message .= '<ul>';
                foreach($errorMessages as $errorMessage) {
                    $message .= '<li>';
                        $message .= '<b>' . $errorMessage['customerName'] . '</b>: ';
                        $message .= __d('admin', '{0,plural,=1{1_product} other{#_products}}', [
                            $errorMessage['orderDetailsWithUnchangedWeight'],
                        ]);
                    $message .= '</li>';
                }
                $message .= '</ul>';
            }
            $this->Flash->error($message);
        }

        $message = '';
        if (empty($result)) {
            $message = __d('admin', 'Errors_while_saving!');
        }

        $redirectUrl = '';
        if (preg_match('/customerId\='.$customerIds[0].'/', $this->referer())) {
            $redirectUrl = '/admin/order-details?pickupDay[]='.$this->getRequest()->getData('pickupDay').'&groupBy=customer';
        }

        $this->set([
            'pickupDay' => $pickupDay,
            'result' => $result,
            'status' => !empty($result),
            'redirectUrl' => $redirectUrl,
            'msg' => $message,
        ]);
        $this->viewBuilder()->setOption('serialize', ['pickupDay', 'result', 'status', 'redirectUrl', 'msg']);

    }

}

<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;
use App\Services\DeliveryRhythmService;
use App\Services\SanitizeService;

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

trait EditDeliveryRhythmTrait 
{

    public function editDeliveryRhythm()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $productIds = $this->getRequest()->getData('productIds');
        $deliveryRhythmTypeCombined = $this->getRequest()->getData('deliveryRhythmType');
        $deliveryRhythmFirstDeliveryDay = $this->getRequest()->getData('deliveryRhythmFirstDeliveryDay');
        $deliveryRhythmOrderPossibleUntil = $this->getRequest()->getData('deliveryRhythmOrderPossibleUntil');
        $deliveryRhythmSendOrderListWeekday = $this->getRequest()->getData('deliveryRhythmSendOrderListWeekday');
        $deliveryRhythmSendOrderListDay = $this->getRequest()->getData('deliveryRhythmSendOrderListDay');

        $splittedDeliveryRhythmType = explode('-', $deliveryRhythmTypeCombined);

        $singleEditMode = false;
        if (count($productIds) == 1) {
            $singleEditMode = true;
            $productId = $productIds[0];
        }

        if ($singleEditMode) {
            $oldProduct = $this->Product->find('all',
                conditions: [
                    'Products.id_product' => $productId
                ],
                contain: [
                    'Manufacturers'
                ]
            )->first();
        }

        $deliveryRhythmCount = $splittedDeliveryRhythmType[0];
        $deliveryRhythmType = $splittedDeliveryRhythmType[1];

        $product2update = [
            'delivery_rhythm_count' => $deliveryRhythmCount,
            'delivery_rhythm_type' => $deliveryRhythmType,
        ];

        $isFirstDeliveryDayMandatory = in_array($deliveryRhythmTypeCombined, ['0-individual', '2-week', '4-week']);
        if ($deliveryRhythmFirstDeliveryDay != '' || $isFirstDeliveryDayMandatory) {
            $product2update['delivery_rhythm_first_delivery_day'] = Configure::read('app.timeHelper')->formatToDbFormatDate($deliveryRhythmFirstDeliveryDay);
        }
        if ($deliveryRhythmFirstDeliveryDay == '' && !$isFirstDeliveryDayMandatory) {
            $product2update['delivery_rhythm_first_delivery_day'] = '';
        }

        $product2update['delivery_rhythm_order_possible_until'] = '';
        $product2update['delivery_rhythm_send_order_list_day'] = '';
        if ($deliveryRhythmSendOrderListWeekday == '') {
            $deliveryRhythmSendOrderListWeekday = Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, (new DeliveryRhythmService())->getSendOrderListsWeekday());
        }
        $product2update['delivery_rhythm_send_order_list_weekday'] = Configure::read('app.timeHelper')->getNthWeekdayAfterWeekday(1, (int) $deliveryRhythmSendOrderListWeekday);

        if (in_array($deliveryRhythmTypeCombined, ['0-individual'])) {
            $product2update['delivery_rhythm_order_possible_until'] = Configure::read('app.timeHelper')->formatToDbFormatDate($deliveryRhythmOrderPossibleUntil);
            if ($deliveryRhythmSendOrderListDay != '') {
                $product2update['delivery_rhythm_send_order_list_day'] = Configure::read('app.timeHelper')->formatToDbFormatDate($deliveryRhythmSendOrderListDay);
            }
        }

        try {

            $products2update = [];
            foreach($productIds as $productId) {
                $products2update[] = [
                    $productId => $product2update
                ];
            }

            $this->Product->changeDeliveryRhythm($products2update);

            $additionalMessages = [];
            if ($deliveryRhythmFirstDeliveryDay != '') {
                if ($product2update['delivery_rhythm_order_possible_until'] != '') {
                    $additionalMessages[] = __d('admin', 'Order_possible_until') . ': <b>'. Configure::read('app.timeHelper')->formatToDateShort($deliveryRhythmOrderPossibleUntil) . '</b>';
                }
            }

            if ($deliveryRhythmType == 'individual') {
                if ($product2update['delivery_rhythm_send_order_list_day'] != '') {
                    $additionalMessages[] = __d('admin', 'Send_order_lists_day') . ': <b>'. Configure::read('app.timeHelper')->formatToDateShort($deliveryRhythmSendOrderListDay) . '</b>';
                } else {
                    $additionalMessages[] = __d('admin', 'Order_list_is_not_sent');
                }
            } else {
                if ($product2update['delivery_rhythm_send_order_list_weekday'] != (new DeliveryRhythmService())->getSendOrderListsWeekday()) {
                    $additionalMessages[] =  __d('admin', 'Last_order_weekday') . ': <b>' . Configure::read('app.timeHelper')->getWeekdayName(
                        $deliveryRhythmSendOrderListWeekday) . ' ' . __d('admin', 'midnight')
                        . '</b>';
                }
            }

            if ($deliveryRhythmFirstDeliveryDay != '') {
                $deliveryDayMessage = '';
                if ($deliveryRhythmType == 'individual') {
                    $deliveryDayMessage .= __d('admin', 'Delivery_day');
                } else {
                    $deliveryDayMessage .= __d('admin', 'First_delivery_day');
                }
                $deliveryDayMessage .= ': <b>'. Configure::read('app.timeHelper')->formatToDateShort($deliveryRhythmFirstDeliveryDay) . '</b>';
                $additionalMessages[] = $deliveryDayMessage;
            }

            if ($singleEditMode && isset($productId)) {
                $messageString = __d('admin', 'The_delivery_rhythm_of_the_product_{0}_from_manufacturer_{1}_was_changed_successfully_to_{2}.', [
                    '<b>' . $oldProduct->name . '</b>',
                    '<b>' . $oldProduct->manufacturer->name . '</b>',
                    '<b>' . Configure::read('app.htmlHelper')->getDeliveryRhythmString(
                        $oldProduct->is_stock_product && $oldProduct->manufacturer->stock_management_enabled,
                        $deliveryRhythmType,
                        $deliveryRhythmCount
                    ) . '</b>'
                ]);
                if (!empty($additionalMessages)) {
                    $messageString .= ' ' . join(', ', $additionalMessages);
                }
                $this->ActionLog->customSave('product_delivery_rhythm_changed', $this->identity->getId(), $productId, 'products', $messageString);
                $this->getRequest()->getSession()->write('highlightedRowId', $productId);
            } else {
                $messageString = __d('admin', 'Delivery_rhythm_of_{0}_products_has_been_changed_successfully_to_{1}.', [
                    count($productIds),
                    '<b>' . Configure::read('app.htmlHelper')->getDeliveryRhythmString(false, $deliveryRhythmType, $deliveryRhythmCount) . '</b>'
                ]);
                if (!empty($additionalMessages)) {
                    $messageString .= ' ' . join(', ', $additionalMessages);
                }
                $this->ActionLog->customSave('product_delivery_rhythm_changed', $this->identity->getId(), 0, 'products', $messageString . ' Ids: ' . join(', ', $productIds));
            }

            $this->Flash->success($messageString);

            $this->set([
                'status' => 1,
                'msg' => __d('admin', 'Saving_successful.'),
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

    }

}

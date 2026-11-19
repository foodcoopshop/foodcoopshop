<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * Moves single regular pickup days (eg. holidays) to an earlier day with an earlier order deadline.
 * Stored as json: [{"original_pickup_day":"2026-12-25","pickup_day":"2026-12-24","order_possible_until":"2026-12-21"}]
 * Order lists are sent the day after "order_possible_until". "Sammelbestellungen" are not moved.
 *
 * @phpstan-type PickupDayException array{original_pickup_day: string, pickup_day: string, order_possible_until: string}
 */
class PickupDayExceptionService
{

    public const CONFIGURATION_NAME = 'FCS_PICKUP_DAY_EXCEPTIONS';

    // allowed days relative to the regular pickup day
    public const PICKUP_DAY_OFFSETS = [-2, -1];
    // allowed days relative to the new pickup day
    public const ORDER_POSSIBLE_UNTIL_OFFSETS = [-3, -2];

    public static function isAvailable(): bool
    {
        return !Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')
            && !(new DeliveryRhythmService())->hasSaturdayToWednesdayOrThursdayConfig();
    }

    /**
     * @return list<PickupDayException>
     */
    public function getExceptions(): array
    {
        $value = (string) Configure::read('appDb.' . self::CONFIGURATION_NAME);
        if ($value === '' || !self::isAvailable()) {
            return [];
        }
        return $this->decode($value);
    }

    /**
     * @return list<PickupDayException>
     */
    public function decode(?string $value): array
    {
        return json_decode((string) $value, true) ?: [];
    }

    /**
     * @param list<int> $offsets
     * @return list<string>
     */
    public function getAllowedDays(string $day, array $offsets): array
    {
        return array_map(fn($offset) => $this->addDays($day, $offset), $offsets);
    }

    /**
     * @return PickupDayException|null
     */
    public function getByOriginalPickupDay(string $originalPickupDay): ?array
    {
        return $this->find('original_pickup_day', $originalPickupDay);
    }

    /**
     * @return PickupDayException|null
     */
    public function getByPickupDay(string $pickupDay): ?array
    {
        return $this->find('pickup_day', $pickupDay);
    }

    /**
     * @return PickupDayException|null
     */
    private function find(string $key, string $day): ?array
    {
        foreach($this->getExceptions() as $exception) {
            if ($exception[$key] == $day) {
                return $exception;
            }
        }
        return null;
    }

    public function addDays(string $date, int $days): string
    {
        return date('Y-m-d', strtotime($date . ' ' . $days . ' days'));
    }

    /**
     * form rows => json (empty rows and past exceptions are removed)
     */
    public function prepareValueFromRequest(mixed $rows): string
    {
        $today = date('Y-m-d');
        $result = [];
        foreach(is_array($rows) ? $rows : [] as $row) {
            $row = [
                'original_pickup_day' => (string) ($row['original_pickup_day'] ?? ''),
                'pickup_day' => (string) ($row['pickup_day'] ?? ''),
                'order_possible_until' => (string) ($row['order_possible_until'] ?? ''),
            ];
            if (implode('', $row) == '' || ($row['pickup_day'] != '' && $row['pickup_day'] < $today)) {
                continue;
            }
            $result[] = $row;
        }
        usort($result, fn($a, $b) => strcmp($a['original_pickup_day'], $b['original_pickup_day']));
        return empty($result) ? '' : (string) json_encode($result);
    }

    /**
     * @return list<string>
     */
    public function validate(string $value): array
    {
        $errors = [];
        $duplicateErrors = [];
        $originalPickupDays = [];
        $deliveryWeekday = (new DeliveryRhythmService())->getDeliveryWeekday();
        foreach($this->decode($value) as $row) {
            $originalPickupDay = $row['original_pickup_day'];
            if (in_array('', $row, true)) {
                $errors[] = __('Please_select_the_regular_pickup_day,_the_new_pickup_day_and_the_order_possible_until_date.');
                continue;
            }
            $prefix = Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($originalPickupDay)) . ': ';
            if (in_array($originalPickupDay, $originalPickupDays)) {
                $duplicateErrors[] = $prefix . __('The_regular_pickup_day_was_already_selected.');
            } elseif ($originalPickupDay < date('Y-m-d') || date('w', strtotime($originalPickupDay)) != $deliveryWeekday) {
                $errors[] = $prefix . __('The_regular_pickup_day_is_not_valid.');
            } elseif (in_array($originalPickupDay, Configure::read('app.htmlHelper')->getGlobalNoDeliveryDaysAsArray())) {
                $errors[] = $prefix . __('A_delivery_break_for_all_manufacturers_is_set_for_this_pickup_day.');
            }
            if (!in_array($row['pickup_day'], $this->getAllowedDays($originalPickupDay, self::PICKUP_DAY_OFFSETS))) {
                $errors[] = $prefix . __('The_new_pickup_day_needs_to_be_one_or_two_days_before_the_regular_pickup_day.');
            }
            if (!in_array($row['order_possible_until'], $this->getAllowedDays($row['pickup_day'], self::ORDER_POSSIBLE_UNTIL_OFFSETS))) {
                $errors[] = $prefix . __('The_order_possible_until_date_needs_to_be_two_or_three_days_before_the_new_pickup_day.');
            }
            $originalPickupDays[] = $originalPickupDay;
        }
        return array_values(array_unique(array_merge($duplicateErrors, $errors)));
    }

    public function getMovedPickupDayInfo(string $pickupDay): string
    {
        $exception = $this->getByPickupDay($pickupDay);
        if (is_null($exception)) {
            return '';
        }
        return __('Pickup_day_moved_regular_pickup_day_{0}', [
            Configure::read('app.timeHelper')->getDateFormattedWithWeekday(strtotime($exception['original_pickup_day'])),
        ]);
    }

    /**
     * products with an own order deadline that is later than the order deadline of the exception (it is moved forward)
     * @param PickupDayException $exception
     * @return list<array{manufacturer_name: string, product_count: int, regular_last_order_day: string, new_last_order_day: string}>
     */
    public function getProductsWithChangedOrderDeadline(array $exception): array
    {
        $deliveryRhythmService = new DeliveryRhythmService();
        $products = TableRegistry::getTableLocator()->get('Products')->find('all', contain: ['Manufacturers'], conditions: [
            'Products.active' => APP_ON,
            'Products.delivery_rhythm_type <>' => 'individual',
            'Products.delivery_rhythm_send_order_list_weekday <>' => $deliveryRhythmService->getSendOrderListsWeekday(),
        ], order: ['Manufacturers.name' => 'ASC']);
        $result = [];
        foreach($products as $product) {
            // product is not delivered on the moved pickup day (eg. every two weeks)
            if ($deliveryRhythmService->getNextPickupDayForProduct($product, $exception['order_possible_until']) != $exception['pickup_day']) {
                continue;
            }
            $regularLastOrderDay = $deliveryRhythmService->getLastOrderDay($exception['original_pickup_day'], 'week', $product->delivery_rhythm_send_order_list_weekday, null);
            if ($regularLastOrderDay <= $exception['order_possible_until']) {
                continue;
            }
            $key = $product->manufacturer->name . '-' . $regularLastOrderDay;
            $result[$key] ??= [
                'manufacturer_name' => $product->manufacturer->name,
                'product_count' => 0,
                'regular_last_order_day' => $regularLastOrderDay,
                'new_last_order_day' => $exception['order_possible_until'],
            ];
            $result[$key]['product_count']++;
        }
        return array_values($result);
    }

    /**
     * "Sammelbestellungen" are not moved: summary by manufacturer for the given regular pickup day
     * @return list<array{manufacturer_name: string, product_count: int, ordered_amount: int, order_value: float}>
     */
    public function getBulkOrders(string $originalPickupDay): array
    {
        $products = TableRegistry::getTableLocator()->get('Products')->find('all', contain: ['Manufacturers'], conditions: [
            'Products.delivery_rhythm_type' => 'individual',
            'Products.delivery_rhythm_first_delivery_day' => $originalPickupDay,
        ])->all()->groupBy('manufacturer.name');
        $result = [];
        foreach($products as $manufacturerName => $manufacturerProducts) {
            $orderDetails = TableRegistry::getTableLocator()->get('OrderDetails')->find('all', conditions: [
                'OrderDetails.product_id IN' => array_column($manufacturerProducts, 'id_product'),
                'OrderDetails.pickup_day' => $originalPickupDay,
            ])->all();
            $result[] = [
                'manufacturer_name' => (string) $manufacturerName,
                'product_count' => count($manufacturerProducts),
                'ordered_amount' => (int) array_sum($orderDetails->extract('product_amount')->toList()),
                'order_value' => (float) array_sum($orderDetails->extract('total_price_tax_incl')->toList()),
            ];
        }
        return $result;
    }

}

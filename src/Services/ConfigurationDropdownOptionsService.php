<?php
declare(strict_types=1);

namespace App\Services;

use Cake\Core\Configure;
use App\Model\Entity\Customer;
use Cake\ORM\TableRegistry;

class ConfigurationDropdownOptionsService
{
    /**
    * @return array<int|string, string|array<int, string>>
     */
    public function getMultipleDropdownOptions(string $name): array
    {
        switch ($name) {
            case 'FCS_INFO_CHANNELS':
                return Customer::CHANNEL_TYPE_MAP;
            case 'FCS_NO_DELIVERY_DAYS_GLOBAL':
                if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
                    return (new DeliveryRhythmService())->getNextDailyDeliveryDays(365);
                }
                return (new DeliveryRhythmService())->getNextWeeklyDeliveryDays();
            case 'FCS_MEMBER_FEE_PRODUCTS':
                return TableRegistry::getTableLocator()->get('Products')->getForDropdown(0);
        }

        return [];
    }

    /**
     * @param array<string> $values
     * @return list<string>
     */
    public function sortSelectedValues(string $name, array $values): array
    {
        $optionValues = [];
        foreach ($this->getMultipleDropdownOptions($name) as $value => $label) {
            if (is_array($label)) {
                foreach (array_keys($label) as $optionValue) {
                    $optionValues[] = (string) $optionValue;
                }
            } else {
                $optionValues[] = (string) $value;
            }
        }

        return array_values(array_merge(
            array_intersect($optionValues, $values),
            array_diff($values, $optionValues),
        ));
    }
}
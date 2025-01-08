<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class FeedbacksTable extends AppTable
{

    public const PRIVACY_TYPE_NO_PRIVACY_WITH_CITY = 10;
    public const PRIVACY_TYPE_PARTIAL_PRIVACY_WITH_CITY = 20;
    public const PRIVACY_TYPE_FULL_PRIVACY = 30;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
        ]);
    }

    public function validationEdit(Validator $validator): Validator
    {
        $values = [
            self::PRIVACY_TYPE_PARTIAL_PRIVACY_WITH_CITY,
            self::PRIVACY_TYPE_NO_PRIVACY_WITH_CITY,
            self::PRIVACY_TYPE_FULL_PRIVACY,
        ];
        $validator->inList('privacy_type', $values, __('The_following_values_are_valid:') . ' ' . implode(', ', $values));
        $range = [10, 1000];
        $formattedRange = [
            Configure::read('app.numberHelper')->formatAsDecimal($range[0], 0),
            Configure::read('app.numberHelper')->formatAsDecimal($range[1], 0),
        ];
        $validator->lengthBetween('text', $range, __('Please_enter_between_{0}_and_{1}_characters.', $formattedRange));
        return $validator;
    }

    public function getManufacturerPrivacyType($feedback): string
    {
        $privacyTypes = self::getManufacturerPrivacyTypes($feedback->manufacturer);
        $privacyType = $privacyTypes[$feedback->privacy_type];
        return $privacyType;
    }

    public function getManufacturerPrivacyTypes($manufacturer): array
    {
        $values = [
            self::PRIVACY_TYPE_NO_PRIVACY_WITH_CITY => $manufacturer->name . ', ' . $manufacturer->address_manufacturer->city,
            self::PRIVACY_TYPE_FULL_PRIVACY => __('Manufacturer'),
        ];
        return $values;
    }

    public function getCustomerPrivacyType($feedback): string
    {
        $privacyTypes = self::getCustomerPrivacyTypes($feedback->customer);
        $privacyType = $privacyTypes[$feedback->privacy_type];
        return $privacyType;
    }

    public function getCustomerPrivacyTypes($customer): array
    {
        if ($customer->is_company) {
            $values = [
                self::PRIVACY_TYPE_NO_PRIVACY_WITH_CITY => $customer->name . ', ' . $customer->address_customer->city,
            ];
        } else {
            $values = [
                self::PRIVACY_TYPE_PARTIAL_PRIVACY_WITH_CITY => $customer->firstname . ', ' . $customer->address_customer->city,
                self::PRIVACY_TYPE_NO_PRIVACY_WITH_CITY => $customer->name . ', ' . $customer->address_customer->city,
            ];
        }
        $values[self::PRIVACY_TYPE_FULL_PRIVACY] = __('Member');
        return $values;
    }

    public function isApproved($feedback): bool
    {
        $approvedDate = $feedback->approved->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
        $notApproved = Configure::read('app.timeHelper')->isDatabaseDateNotSet($approvedDate);
        return !$notApproved;
    }

}

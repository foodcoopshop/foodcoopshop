<?php

namespace App\Model\Table;

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
    public const PRIVACY_TYPE_NO_PRIVACY = 20;
    public const PRIVACY_TYPE_PARTIAL_PRIVACY_WITH_CITY = 30;
    public const PRIVACY_TYPE_PARTIAL_PRIVACY = 40;
    public const PRIVACY_TYPE_FULL_PRIVACY_WITH_CITY = 50;
    public const PRIVACY_TYPE_FULL_PRIVACY = 60;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('Timestamp');
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
        ]);
    }

    public function validationEdit(Validator $validator)
    {
        $validator->notEmptyString('text', __('Please_enter_your_feedback.'));
        $values = [
            self::PRIVACY_TYPE_NO_PRIVACY_WITH_CITY,
            self::PRIVACY_TYPE_NO_PRIVACY,
            self::PRIVACY_TYPE_PARTIAL_PRIVACY_WITH_CITY,
            self::PRIVACY_TYPE_PARTIAL_PRIVACY,
            self::PRIVACY_TYPE_FULL_PRIVACY_WITH_CITY,
            self::PRIVACY_TYPE_FULL_PRIVACY,
        ];
        $validator->inList('privacy_type', $values, __('The_following_values_are_valid:') . ' ' . implode(', ', $values)
        );
        return $validator;
    }

    public function getPrivacyTypesForDropdown($customer)
    {
        $values = [
            self::PRIVACY_TYPE_NO_PRIVACY_WITH_CITY => $customer->name . ', ' . $customer->address_customer->city,
            self::PRIVACY_TYPE_NO_PRIVACY => $customer->name,
            self::PRIVACY_TYPE_PARTIAL_PRIVACY_WITH_CITY => $customer->firstname . ', ' . $customer->address_customer->city,
            self::PRIVACY_TYPE_PARTIAL_PRIVACY => $customer->firstname,
            self::PRIVACY_TYPE_FULL_PRIVACY_WITH_CITY => __('Member') . ', ' . $customer->address_customer->city,
            self::PRIVACY_TYPE_FULL_PRIVACY => __('Member'),
        ];
        return $values;
    }

}

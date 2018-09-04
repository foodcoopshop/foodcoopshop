<?php

namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class AddressManufacturersTable extends AddressesTable
{

    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('firstname', __('Please_enter_the_first_name_of_the_invoice_holder.'));
        $validator->notEmpty('lastname', __('Please_enter_the_last_name_of_the_invoice_holder.'));
        $validator->notEmpty('email', __('Please_enter_an_email_address.'));
        $validator->email('email', false, __('The_email_address_is_not_valid.'));
        $validator->add('email', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => __('The_email_address_is_already_used_by_another_member_or_manufacturer.')
        ]);
        $validator->allowEmpty('postcode');
        $validator->add('postcode', 'validFormat', [
            'rule' => array('custom', ZIP_REGEX),
            'message' => __('The_zip_is_not_valid.')
        ]);
        $validator->allowEmpty('phone_mobile');
        $validator->add('phone_mobile', 'validFormat', [
            'rule' => array('custom', PHONE_REGEX),
            'message' => __('The_mobile_number_is_not_valid.')
        ]);
        $validator->allowEmpty('phone');
        $validator->add('phone', 'validFormat', [
            'rule' => array('custom', PHONE_REGEX),
            'message' => __('The_phone_number_is_not_valid.')
        ]);
        return $validator;
    }
}

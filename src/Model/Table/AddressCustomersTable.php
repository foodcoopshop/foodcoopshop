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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AddressCustomersTable extends AddressesTable
{

    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('email', 'Bitte gib deine E-Mail-Adresse an.');
        $validator->email('email', false, 'Die E-Mail-Adresse ist nicht gültig.');
        $validator->add('email', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.'
        ]);
        $validator->notEmpty('address1', 'Bitte gib deine Straße an.');
        $validator->notEmpty('city', 'Bitte gib deinen Ort an.');
        $validator->notEmpty('postcode', 'Bitte gib deine PLZ an.');
        $validator->add('postcode', 'validFormat', [
            'rule' => array('custom', ZIP_REGEX),
            'message' => 'Die PLZ ist nicht gültig.'
        ]);
        $validator->notEmpty('phone_mobile', 'Bitte gib deine Handynummer an.');
        $validator->add('phone_mobile', 'validFormat', [
            'rule' => array('custom', PHONE_REGEX),
            'message' => 'Die Handynummer ist nicht gültig.'
        ]);
        $validator->allowEmpty('phone');
        $validator->add('phone', 'validFormat', [
            'rule' => array('custom', PHONE_REGEX),
            'message' => 'Die Telefonnummer ist nicht gültig.'
        ]);
        return $validator;
    }
}

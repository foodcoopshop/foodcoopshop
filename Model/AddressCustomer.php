<?php

App::uses('Address', 'Model');

/**
 * AddressCustomer
 *
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
class AddressCustomer extends Address
{

    public $validate = array(
        'address1' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib deine Straße an.'
            )
        ),
        'postcode' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib deine PLZ an.'
            ),
            'regex' => array(
                'rule' => array(
                    'phone',
                    ZIP_REGEX
                ), // phone takes regex
                'message' => 'Die PLZ ist nicht gültig.'
            )
        ),
        'city' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib deinen Ort an.'
            )
        ),
        'phone_mobile' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib deine Handynummer an.'
            ),
            'phone' => array(
                'rule' => array(
                    'phone',
                    PHONE_REGEX
                ),
                'message' => 'Die Handynummer ist nicht gültig.'
            )
        ),
        'phone' => array(
            'phone' => array(
                'rule' => array(
                    'phone',
                    PHONE_REGEX
                ),
                'allowEmpty' => true,
                'message' => 'Die Telefonnummer ist nicht gültig.'
            )
        )
    );
}

?>
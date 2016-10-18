<?php

App::uses('Address', 'Model');

/**
 * AddressManufacturer
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
class AddressManufacturer extends Address
{

    public $validate = array(
        'firstname' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib den Vornamen des Rechnungsempfängers an.'
            )
        ),
        'lastname' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib den Nachnamen des Rechnungsempfängers an.'
            )
        ),
        'email' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib eine E-Mail-Adresse an.'
            ),
            'uniqueEmailWithFlagCheck' => array(
                'rule' => array(
                    'uniqueEmailWithFlagCheck'
                ),
                'message' => 'Diese E-Mail-Adresse existiert bereits.'
            ),
            'email' => array(
                'rule' => array(
                    'email'
                ),
                'message' => 'Diese E-Mail-Adresse ist nicht gültig.'
            )
        ),
        'postcode' => array(
            'regex' => array(
                'rule' => array(
                    'phone',
                    ZIP_REGEX
                ), // phone takes regex
                'message' => 'Die PLZ ist nicht gültig.',
                'allowEmpty' => true
            )
        ),
        'phone_mobile' => array(
            'phone' => array(
                'rule' => array(
                    'phone',
                    PHONE_REGEX
                ),
                'message' => 'Die Handynummer ist nicht gültig.',
                'allowEmpty' => true
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

    /**
     * for addresses only
     * 
     * @param array $check            
     * @return boolean
     */
    public function uniqueEmailWithFlagCheck($check)
    {
        $conditions = array(
            $this->alias . '.email' => $check['email'],
            $this->alias . '.deleted' => APP_OFF,
            $this->alias . '.active' => APP_ON
        );
        
        // if manufacturer address already exists
        if ($this->id > 0) {
            $conditions[] = $this->alias . '.id_address <> ' . $this->id;
        }
        
        $found = $this->find('count', array(
            'conditions' => $conditions
        ));
        if ($found == 0) {
            return true;
        }
        return false;
    }
}

?>
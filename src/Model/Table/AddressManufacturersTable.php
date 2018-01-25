<?php

use App\Model\Table\AddressesTable;

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

class AddressesManufacturer extends AddressesTable
{

    public $validate = [
        'firstname' => [
            'notBlank' => [
                'rule' => [
                    'notBlank'
                ],
                'message' => 'Bitte gib den Vornamen des Rechnungsempfängers an.'
            ]
        ],
        'lastname' => [
            'notBlank' => [
                'rule' => [
                    'notBlank'
                ],
                'message' => 'Bitte gib den Nachnamen des Rechnungsempfängers an.'
            ]
        ],
        'email' => [
            'notBlank' => [
                'rule' => [
                    'notBlank'
                ],
                'message' => 'Bitte gib eine E-Mail-Adresse an.'
            ],
            'uniqueEmailWithFlagCheck' => [
                'rule' => [
                    'uniqueEmailWithFlagCheck'
                ],
                'message' => 'Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.'
            ],
            'email' => [
                'rule' => [
                    'email'
                ],
                'message' => 'Diese E-Mail-Adresse ist nicht gültig.'
            ]
        ],
        'postcode' => [
            'regex' => [
                'rule' => [
                    'phone',
                    ZIP_REGEX
                ], // phone takes regex
                'message' => 'Die PLZ ist nicht gültig.',
                'allowEmpty' => true
            ]
        ],
        'phone_mobile' => [
            'phone' => [
                'rule' => [
                    'phone',
                    PHONE_REGEX
                ],
                'message' => 'Die Handynummer ist nicht gültig.',
                'allowEmpty' => true
            ]
        ],
        'phone' => [
            'phone' => [
                'rule' => [
                    'phone',
                    PHONE_REGEX
                ],
                'allowEmpty' => true,
                'message' => 'Die Telefonnummer ist nicht gültig.'
            ]
        ]
    ];

    /**
     * for addresses only
     *
     * @param array $check
     * @return boolean
     */
    public function uniqueEmailWithFlagCheck($check)
    {
        $conditions = [
            $this->getAlias() . '.email' => $check['email']
        ];

        // if manufacturer address already exists
        if ($this->id > 0) {
            $conditions[] = $this->getAlias() . '.id_address <> ' . $this->id;
        }

        $found = $this->find('count', [
            'conditions' => $conditions
        ]);
        if ($found == 0) {
            return true;
        }
        return false;
    }
}

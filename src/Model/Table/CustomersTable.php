<?php

namespace App\Model\Table;

use App\Auth\AppPasswordHasher;
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
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
class CustomersTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('customer');
        parent::initialize($config);
        $this->hasOne('AddressCustomers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasMany('ActiveOrders', [
            'className' => 'Orders',
            'foreignKey' => 'id_customer',
            'sort' => [
                'ActiveOrders.date_add' => 'DESC'
            ]
        ]);
        $this->hasMany('PaidCashFreeOrders', [
            'className' => 'Orders',
            'foreignKey' => 'id_customer',
            'sort' => [
                'PaidCashFreeOrders.date_add' => 'DESC'
            ]
        ]);
        // has many does not produce multiple records - this should be hasOne ideally...
        $this->hasMany('ValidOrders', [
            'className' => 'Orders',
            'foreignKey' => 'id_customer',
            'limit' => 1
        ]);
        $this->hasMany('Payments', [
            'foreignKey' => 'id_customer',
            'sort' => [
                'Payments.date_add' => 'desc'
            ],
            'conditions' => [
                'Payments.status' => APP_ON
            ]
        ]);
        $this->setPrimaryKey('id_customer');
    }

    public function validationEdit(Validator $validator)
    {
        $validator->notEmpty('firstname', 'Bitte gib deinen Vornamen an.');
        $validator->notEmpty('lastname', 'Bitte gib deinen Nachnamen an.');
        return $validator;
    }

    public function validationRegistration(Validator $validator)
    {
        $validator->notEmpty('firstname', 'Bitte gib deinen Vornamen an.');
        $validator->notEmpty('lastname', 'Bitte gib deinen Nachnamen an.');
        $validator = $this->getValidationTermsOfUse($validator);
        return $validator;
    }

    public function validationChangePassword($validator)
    {
        $validator
        ->notEmpty('passwd_old', 'Bitte gib dein altes Passwort ein.')
        ->add('passwd_old', 'custom', [
            'rule'=>  function ($value, $context) {
                $user = $this->get($context['data']['id_customer']);
                if ($user) {
                    if ((new AppPasswordHasher())->check($value, $user->passwd)) {
                        return true;
                    }
                }
                return false;
            },
            'message' => 'Dein altes Passwort ist leider falsch.',
        ])
        ->notEmpty('passwd_old');

        $validator
        ->notEmpty('passwd_1', 'Bitte gib ein neues Passwort ein.')
        ->add('passwd_1', [
            'length' => [
                'rule' => ['minLength', 8],
                'message' => 'Das Passwort muss aus mindestens 8 Zeichen bestehen.',
            ]
        ])
        ->add('passwd_1', [
            'match' => [
                'rule' => ['compareWith', 'passwd_2'],
                'message' => 'Die Passwörter stimmen nicht überein.',
            ]
        ])
        ->notEmpty('passwd_1');

        $validator
        ->notEmpty('passwd_2', 'Bitte gib ein neues Passwort ein.')
        ->add('passwd_2', [
            'length' => [
                'rule' => ['minLength', 8],
                'message' => 'Das Passwort muss aus mindestens 8 Zeichen bestehen.',
            ]
        ])
        ->add('passwd_2', [
            'match' => [
                'rule' => ['compareWith', 'passwd_1'],
                'message' => 'Die Passwörter stimmen nicht überein.',
            ]
        ])
        ->notEmpty('passwd_2');

        return $validator;
    }


    public function validationNewPasswordRequest(Validator $validator)
    {
        $validator->notEmpty('email', 'Bitte gib deine E-Mail-Adresse an.');
        $validator->email('email', false, 'Die E-Mail-Adresse ist nicht gültig.');
        $validator->add('email', 'exists', [
            'rule' => function ($value, $context) {
                $ct = TableRegistry::get('Customers');
                return $ct->exists([
                    'email' => $value
                ]);
            },
            'message' => 'Wir haben diese E-Mail-Adresse nicht gefunden.'
        ]);
        $validator->add('email', 'account_inactive', [
            'rule' => function ($value, $context) {
                $ct = TableRegistry::get('Customers');
                $record  = $ct->find('all', [
                    'conditions' => [
                        'email' => $value
                    ]
                ])->first();
                if (!empty($record) && !$record->active) {
                    return false;
                }
                return true;
            },
            'message' => 'Dein Mitgliedskonto ist nicht mehr aktiv. Falls du es wieder aktivieren möchtest, schreib uns bitte eine E-Mail.'
        ]);
        return $validator;
    }

    public function validationTermsOfUse(Validator $validator)
    {
        return $this->getValidationTermsOfUse($validator);
    }

    private function getValidationTermsOfUse(Validator $validator)
    {
        return $validator->equals('terms_of_use_accepted_date_checkbox', 1, 'Bitte akzeptiere die Nutzungsbedingungen.');
    }

    public function findAuth(\Cake\ORM\Query $query, array $options)
    {
        return $query->contain([
            'AddressCustomers'
        ]);
    }

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);

        $this->association('ValidOrders')->setConditions([
            'ValidOrders.current_state IN (' . join(',', Configure::read('app.htmlHelper')->getOrderStateIds()) . ')'
        ]);
        $this->association('ActiveOrders')->setConditions([
            'ActiveOrders.current_state IN (' . ORDER_STATE_OPEN . ')'
        ]);
        $this->association('PaidCashFreeOrders')->setConditions([
            'PaidCashFreeOrders.current_state IN (' . ORDER_STATE_CASH_FREE . ', ' . ORDER_STATE_OPEN . ')'
        ]);
    }

    public function getConditionToExcludeHostingUser()
    {
        return [
            'Customers.email != \'' . Configure::read('app.hostingEmail') . '\''
        ];
    }

    public function dropManufacturersInNextFind()
    {
        $this->association('AddressCustomers')->setJoinType('INNER');
    }

    /**
     * bindings with email as foreign key was tricky...
     *
     * @param array $customer
     * @return boolean
     */
    public function getManufacturerRecord($customer)
    {
        $mm = TableRegistry::get('Manufacturers');
        $manufacturer = $mm->find('all', [
            'conditions' => [
                'AddressManufacturers.email' => $customer->email
            ],
            'contain' => [
                'AddressManufacturers'
            ]
        ])->first();
        return $manufacturer;
    }

    /**
     * @param int $customerId
     * @return string
     */
    public function setNewPassword($customerId)
    {
        $ph = new AppPasswordHasher();
        $newPassword = StringComponent::createRandomString(12);

        // reset change password code
        $patchedEntity = $this->patchEntity(
            $this->get($customerId),
            [
                'passwd' => $ph->hash($newPassword),
                'change_password_code' => null
            ]
        );
        $this->save($patchedEntity);
        return $newPassword;
    }

    /**
     * @param int $customerId
     * @return array
     */
    public function getManufacturerByCustomerId($customerId)
    {
        $customer = $this->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ]
        ])->first();
        if (!empty($customer)) {
            return $this->getManufacturerRecord($customer);
        }
        return false;
    }

    public function getManufacturerIdByCustomerId($customerId)
    {
        $manufacturer = $this->getManufacturerByCustomerId($customerId);
        if (!empty($manufacturer)) {
            return $manufacturer->id_manufacturer;
        }
        return 0;
    }

    public function getCreditBalance($customerId)
    {
        $cp = TableRegistry::get('Payments');
        $paymentSumProduct = $cp->getSum($customerId, 'product');
        $paybackSumProduct = $cp->getSum($customerId, 'payback');
        $paymentSumDeposit = $cp->getSum($customerId, 'deposit');

        $o = TableRegistry::get('Orders');
        $productSum = $o->getSumProduct($customerId);
        $depositSum = $o->getSumDeposit($customerId);

        // rounding avoids problems with very tiny numbers (eg. 2.8421709430404E-14)
        return round($paymentSumProduct - $paybackSumProduct + $paymentSumDeposit - $productSum - $depositSum, 2);
    }

    public function getForDropdown($includeManufacturers = false, $index = 'id_customer', $includeOfflineCustomers = true)
    {
        $contain = [];
        if (! $includeManufacturers) {
            $this->dropManufacturersInNextFind();
            $contain[] = 'ValidOrders';
            $contain[] = 'AddressCustomers'; // to make exclude happen using dropManufacturersInNextFind
        }

        $customers = $this->find('all', [
            'conditions' => $this->getConditionToExcludeHostingUser(),
            'order' => Configure::read('app.htmlHelper')->getCustomerOrderBy(),
            'contain' => $contain
        ]);

        $offlineCustomers = [];
        $onlineCustomers = [];
        $notYetOrderedCustomers = [];
        $offlineManufacturers = [];
        $onlineManufacturers = [];
        foreach ($customers as $customer) {
            $userNameForDropdown = $customer->name;

            $manufacturerIncluded = false;
            if ($includeManufacturers) {
                $manufacturer = $this->getManufacturerRecord($customer);
                if ($manufacturer) {
                    if ($manufacturer->active) {
                        $onlineManufacturers[$customer->$index] = $manufacturer->name;
                    } else {
                        $offlineManufacturers[$customer->$index] = $manufacturer->name;
                    }
                    $manufacturerIncluded = true;
                }
            }

            if (! $manufacturerIncluded) {
                if ($customer->active == 0) {
                    $offlineCustomers[$customer->$index] = $userNameForDropdown;
                } else {
                    if (! $includeManufacturers) {
                        if (empty($customer->valid_orders)) {
                            $notYetOrderedCustomers[$customer->$index] = $userNameForDropdown;
                        } else {
                            $onlineCustomers[$customer->$index] = $userNameForDropdown;
                        }
                    } else {
                        $onlineCustomers[$customer->$index] = $userNameForDropdown;
                    }
                }
            }
        }

        $customersForDropdown = [];
        if (! empty($onlineCustomers)) {
            $customersForDropdown['Mitglieder: aktiv'] = $onlineCustomers;
        }
        if (! empty($notYetOrderedCustomers)) {
            $customersForDropdown['Mitglieder: noch nie bestellt'] = $notYetOrderedCustomers;
        }
        if (! empty($onlineManufacturers)) {
            asort($onlineManufacturers);
            $customersForDropdown['Hersteller: aktiv'] = $onlineManufacturers;
        }
        if (! empty($offlineManufacturers)) {
            asort($offlineManufacturers);
            $customersForDropdown['Hersteller: inaktiv'] = $offlineManufacturers;
        }
        if (! empty($offlineCustomers) && $includeOfflineCustomers) {
            $customersForDropdown['Mitglieder: inaktiv'] = $offlineCustomers;
        }
        return $customersForDropdown;
    }
}

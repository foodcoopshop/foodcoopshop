<?php

namespace App\Model\Table;
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use App\Auth\AppPasswordHasher;

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
            'order' => [
                'ActiveOrders.date_add' => 'DESC'
            ]
        ]);
        $this->hasMany('PaidCashFreeOrders', [
            'className' => 'Orders',
            'foreignKey' => 'id_customer',
            'order' => [
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
            'order' => [
                'Payments.date_add' => 'desc'
            ],
            'conditions' => [
                'Payments.status' => APP_ON
            ]
        ]);
        $this->setPrimaryKey('id_customer');
    }
    
    public function validationRegistration(Validator $validator)
    {
        $validator->notEmpty('firstname', 'Bitte gib deinen Vornamen an.');
        $validator->notEmpty('lastname', 'Bitte gib deinen Nachnamen an.');
        $validator = $this->getValidationTermsOfUse($validator);
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

    public function setNewPassword($customerId)
    {
        $newPassword = StringComponent::createRandomString(8);
        $ph = new AppPasswordHasher();
        $customer2save = [
            'passwd' => $ph->hash($newPassword)
        ];
        $this->id = $customerId;
        $this->save($customer2save, false); // false: keine validation();
        return $newPassword;
    }

    /**
     * check if the given string is the password of the logged in user
     */
    public function isCustomerPassword($customerId, $hashedPassword)
    {
        $customer = $this->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
            'fields' => [
                'Customers.passwd'
            ]
        ])->first();

        if ($hashedPassword == $customer['Customers']['passwd']) {
            return true;
        } else {
            return false;
        }
    }

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);

        $this->association('ValidOrders')->setConditions([
            'ValidOrders.current_state IN (' . Configure::read('app.htmlHelper')->getOrderStateIdsAsCsv() . ')'
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
                'Addresses.email' => $customer['Customers']['email']
            ]
        ])->first();

        return $manufacturer;
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
            return $manufacturer['Manufacturers']['id_manufacturer'];
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
            $contain[] = 'AddressCustomers'; // to make exclude happen using dropManufacturersInNextFind
        }

        $customers = $this->find('all', [
            'conditions' => $this->getConditionToExcludeHostingUser(),
            'fields' => [
                'Customers.' . $index,
                'Customers.name',
                'Customers.active',
                'Customers.email'
            ],
            'order' => Configure::read('app.htmlHelper')->getCustomerOrderBy(),
            'contain' => $contain
        ]);

        $offlineCustomers = [];
        $onlineCustomers = [];
        $notYetOrderedCustomers = [];
        $offlineManufacturers = [];
        $onlineManufacturers = [];
        foreach ($customers as $customer) {
            $userNameForDropdown = $customer['Customers']['name'];

            $manufacturerIncluded = false;
            if ($includeManufacturers) {
                $manufacturer = $this->getManufacturerRecord($customer);
                if ($manufacturer) {
                    if ($manufacturer['Manufacturers']['active']) {
                        $onlineManufacturers[$customer['Customers'][$index]] = $manufacturer['Manufacturers']['name'];
                    } else {
                        $offlineManufacturers[$customer['Customers'][$index]] = $manufacturer['Manufacturers']['name'];
                    }
                    $manufacturerIncluded = true;
                }
            }

            if (! $manufacturerIncluded) {
                if ($customer['Customers']['active'] == 0) {
                    $offlineCustomers[$customer['Customers'][$index]] = $userNameForDropdown;
                } else {
                    if (! $includeManufacturers) {
                        if (empty($customer['ValidOrder'])) {
                            $notYetOrderedCustomers[$customer['Customers'][$index]] = $userNameForDropdown;
                        } else {
                            $onlineCustomers[$customer['Customers'][$index]] = $userNameForDropdown;
                        }
                    } else {
                        $onlineCustomers[$customer['Customers'][$index]] = $userNameForDropdown;
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

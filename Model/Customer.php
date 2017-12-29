<?php

App::uses('AppModel', 'Model');

/**
 * Customer
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
class Customer extends AppModel
{

    public $useTable = 'customer';

    public $primaryKey = 'id_customer';

    public $actsAs = array(
        'Content'
    );

    // key needs to be called "address customer" for a working validation in customer::profile
    public $hasOne = array(
        'AddressCustomer' => array(
            'className' => 'AddressCustomer',
            'foreignKey' => 'id_customer'
        )
    );

    public $validate = array(
        'firstname' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib deinen Vornamen an.'
            )
        ),
        'lastname' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib deinen Nachnamen an.'
            )
        ),
        'email' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib deine E-Mail-Adresse an.'
            ),
            'email' => array(
                'rule' => array(
                    'email'
                ),
                'message' => 'Diese E-Mail-Adresse ist nicht gültig.'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'Ein anderes Mitglied oder ein anderer Hersteller verwendet diese E-Mail-Adresse bereits.'
            )
        )
    );

    public function setNewPassword($customerId)
    {
        App::uses('AppPasswordHasher', 'Controller/Component/Auth');
        $ph = new AppPasswordHasher();

        $newPassword = StringComponent::createRandomString(8);
        $customer2save = array(
            'passwd' => $ph->hash($newPassword)
        );
        $this->id = $customerId;
        $this->save($customer2save, false); // false: keine validation();
        return $newPassword;
    }

    /**
     * check if the given string is the password of the logged in user
     */
    public function isCustomerPassword($customerId, $hashedPassword)
    {
        $customer = $this->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $customerId
            ),
            'fields' => array(
                'Customer.passwd'
            )
        ));

        if ($hashedPassword == $customer['Customer']['passwd']) {
            return true;
        } else {
            return false;
        }
    }

    public $hasMany = array(
        'ActiveOrders' => array(
            'className' => 'Order',
            'foreignKey' => 'id_customer',
            'conditions' => array(),
            'order' => array(
                'ActiveOrders.date_add' => 'DESC'
            )
        ),
        'PaidCashFreeOrders' => array(
            'className' => 'Order',
            'foreignKey' => 'id_customer',
            'conditions' => array(),
            'order' => array(
                'PaidCashFreeOrders.date_add' => 'DESC'
            )
        ),
        // has many does not produce multiple records - this should be hasOne ideally...
        'ValidOrder' => array(
            'className' => 'Order',
            'limit' => 1,
            'foreignKey' => 'id_customer'
        ),
        'CakePayments' => array(
            'className' => 'CakePayment',
            'foreignKey' => 'id_customer',
            'order' => array(
                'CakePayments.date_add' => 'desc'
            ),
            'conditions' => array(
                'CakePayments.status' => APP_ON
            )
        )
    );

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);

        $virtualNameField = "`{$this->alias}`.`firstname`,' ',`{$this->alias}`.`lastname`)";
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            $virtualNameField = "`{$this->alias}`.`lastname`,' ',`{$this->alias}`.`firstname`)";
        }

        $this->virtualFields = array(
            'name' => "TRIM(CONCAT(" . $virtualNameField . ")"
        );
        $this->hasMany['ValidOrder']['conditions'] = array(
            'ValidOrder.current_state IN (' . Configure::read('htmlHelper')->getOrderStateIdsAsCsv() . ')'
        );
        $this->hasMany['ActiveOrders']['conditions'] = array(
            'ActiveOrders.current_state IN (' . ORDER_STATE_OPEN . ')'
        );
        $this->hasMany['PaidCashFreeOrders']['conditions'][] = 'PaidCashFreeOrders.current_state IN (' . ORDER_STATE_CASH_FREE . ', ' . ORDER_STATE_OPEN . ')';
    }

    public function getConditionToExcludeHostingUser()
    {
        return array(
            'Customer.email != \'' . Configure::read('app.hostingEmail') . '\''
        );
    }

    public function dropManufacturersInNextFind()
    {
        $this->hasOne['AddressCustomer']['type'] = 'INNER';
    }

    /**
     * bindings with email as foreign key was tricky...
     *
     * @param array $customer
     * @return boolean
     */
    public function getManufacturerRecord($customer)
    {
        $mm = ClassRegistry::init('Manufacturer');

        $mm->recursive = 1;
        $manufacturer = $mm->find('first', array(
            'conditions' => array(
                'Address.email' => $customer['Customer']['email']
            )
        ));

        return $manufacturer;
    }

    /**
     * @param int $customerId
     * @return array
     */
    public function getManufacturerByCustomerId($customerId)
    {
        $customer = $this->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $customerId
            )
        ));
        if (!empty($customer)) {
            return $this->getManufacturerRecord($customer);
        }
        return false;
    }

    public function getManufacturerIdByCustomerId($customerId)
    {
        $manufacturer = $this->getManufacturerByCustomerId($customerId);
        if (!empty($manufacturer)) {
            return $manufacturer['Manufacturer']['id_manufacturer'];
        }
        return 0;
    }

    public function getCreditBalance($customerId)
    {
        App::uses('CakePayment', 'Model');
        $cp = new CakePayment();
        $paymentSumProduct = $cp->getSum($customerId, 'product');
        $paybackSumProduct = $cp->getSum($customerId, 'payback');
        $paymentSumDeposit = $cp->getSum($customerId, 'deposit');

        App::uses('Order', 'Model');
        $o = new Order();
        $productSum = $o->getSumProduct($customerId);
        $depositSum = $o->getSumDeposit($customerId);

        // rounding avoids problems with very tiny numbers (eg. 2.8421709430404E-14)
        return round($paymentSumProduct - $paybackSumProduct + $paymentSumDeposit - $productSum - $depositSum, 2);
    }

    public function getForDropdown($includeManufacturers = false, $index = 'id_customer', $includeOfflineCustomers = true)
    {
        if (! $includeManufacturers) {
            $this->dropManufacturersInNextFind();
        }

        // TODO no other solution found to exclude those models (contain did not work)
        unset($this->hasMany['PaidCashFreeOrders']);
        unset($this->hasMany['ActiveOrders']);
        unset($this->hasMany['CakePayments']);

        $customers = $this->find('all', array(
            'conditions' => $this->getConditionToExcludeHostingUser(),
            'fields' => array(
                'Customer.' . $index,
                'Customer.name',
                'Customer.active',
                'Customer.email'
            ),
            'order' => Configure::read('htmlHelper')->getCustomerOrderBy()
        ));

        $offlineCustomers = array();
        $onlineCustomers = array();
        $notYetOrderedCustomers = array();
        $offlineManufacturers = array();
        $onlineManufacturers = array();
        foreach ($customers as $customer) {
            $userNameForDropdown = $customer['Customer']['name'];

            $manufacturerIncluded = false;
            if ($includeManufacturers) {
                $manufacturer = $this->getManufacturerRecord($customer);
                if ($manufacturer) {
                    if ($manufacturer['Manufacturer']['active']) {
                        $onlineManufacturers[$customer['Customer'][$index]] = $manufacturer['Manufacturer']['name'];
                    } else {
                        $offlineManufacturers[$customer['Customer'][$index]] = $manufacturer['Manufacturer']['name'];
                    }
                    $manufacturerIncluded = true;
                }
            }

            if (! $manufacturerIncluded) {
                if ($customer['Customer']['active'] == 0) {
                    $offlineCustomers[$customer['Customer'][$index]] = $userNameForDropdown;
                } else {
                    if (! $includeManufacturers) {
                        if (empty($customer['ValidOrder'])) {
                            $notYetOrderedCustomers[$customer['Customer'][$index]] = $userNameForDropdown;
                        } else {
                            $onlineCustomers[$customer['Customer'][$index]] = $userNameForDropdown;
                        }
                    } else {
                        $onlineCustomers[$customer['Customer'][$index]] = $userNameForDropdown;
                    }
                }
            }
        }

        $customersForDropdown = array();
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

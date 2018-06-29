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
        $this->hasMany('Manufacturers', [
            'foreignKey' => 'id_customer'
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
        $validator->notEmpty('firstname', __('Please_enter_your_first_name.'));
        $validator->notEmpty('lastname', __('Please_enter_your_last_name.'));
        return $validator;
    }

    public function validationRegistration(Validator $validator)
    {
        $validator->notEmpty('firstname', __('Please_enter_your_first_name.'));
        $validator->notEmpty('lastname', __('Please_enter_your_last_name.'));
        $validator = $this->getValidationTermsOfUse($validator);
        return $validator;
    }

    public function validationChangePassword($validator)
    {
        $validator
        ->notEmpty('passwd_old', __('Please_enter_your_old_password.'))
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
            'message' => __('Your_old_password_is_wrong.')
        ])
        ->notEmpty('passwd_old');

        $validator
        ->notEmpty('passwd_1', __('Please_enter_a_new_password.'))
        ->add('passwd_1', [
            'length' => [
                'rule' => ['minLength', 8],
                'message' => __('The_password_needs_to_be_at_least_8_characters_long.')
            ]
        ])
        ->add('passwd_1', [
            'match' => [
                'rule' => ['compareWith', 'passwd_2'],
                'message' => __('The_passwords_do_not_match.')
            ]
        ])
        ->notEmpty('passwd_1');

        $validator
        ->notEmpty('passwd_2', __('Please_enter_a_new_password.'))
        ->add('passwd_2', [
            'length' => [
                'rule' => ['minLength', 8],
                'message' => __('The_password_needs_to_be_at_least_8_characters_long.')
            ]
        ])
        ->add('passwd_2', [
            'match' => [
                'rule' => ['compareWith', 'passwd_1'],
                'message' => __('The_passwords_do_not_match.')
            ]
        ])
        ->notEmpty('passwd_2');

        return $validator;
    }


    public function validationNewPasswordRequest(Validator $validator)
    {
        $validator->notEmpty('email', __('Please_enter_your_email_address.'));
        $validator->email('email', false, __('The_email_address_is_not_valid.'));
        $validator->add('email', 'exists', [
            'rule' => function ($value, $context) {
                $ct = TableRegistry::getTableLocator()->get('Customers');
                return $ct->exists([
                    'email' => $value
                ]);
            },
            'message' => __('We_did_not_find_this_email_address.')
        ]);
        $validator->add('email', 'account_inactive', [
            'rule' => function ($value, $context) {
                $ct = TableRegistry::getTableLocator()->get('Customers');
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
            'message' => __('Your_account_is_not_active_any_more._If_you_want_to_reactivate_it_please_write_an_email.')
        ]);
        return $validator;
    }

    public function validationTermsOfUse(Validator $validator)
    {
        return $this->getValidationTermsOfUse($validator);
    }

    private function getValidationTermsOfUse(Validator $validator)
    {
        return $validator->equals('terms_of_use_accepted_date_checkbox', 1, __('Please_accept_the_terms_of_use.'));
    }

    public function findAuth(\Cake\ORM\Query $query, array $options)
    {
        $query->where([
            'Customers.active' => true
        ]);
        $query->contain([
            'AddressCustomers'
        ]);
        return $query;
    }

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);

        $this->getAssociation('ValidOrders')->setConditions([
            'ValidOrders.current_state IN (' . join(',', Configure::read('app.htmlHelper')->getOrderStateIds()) . ')'
        ]);
        $this->getAssociation('ActiveOrders')->setConditions([
            'ActiveOrders.current_state IN (' . ORDER_STATE_OPEN . ')'
        ]);
        $this->getAssociation('PaidCashFreeOrders')->setConditions([
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
        $this->getAssociation('AddressCustomers')->setJoinType('INNER');
    }

    /**
     * bindings with email as foreign key was tricky...
     *
     * @param array $customer
     * @return boolean
     */
    public function getManufacturerRecord($customer)
    {
        $mm = TableRegistry::getTableLocator()->get('Manufacturers');
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

    public function getProductBalanceForDeletedCustomers()
    {

        $productBalanceSum = 0;
        $orderTable = TableRegistry::getTableLocator()->get('Orders');

        $query = $orderTable->find('all', [
            'contain' => [
                'Customers'
            ],
            'conditions' => [
                'Customers.id_customer IS NULL'
            ]
        ]);
        $query->group('Orders.id_customer');

        $removedCustomerIds = [];
        foreach($query as $order) {
            $removedCustomerIds[] = $order->id_customer;
        }

        $productBalanceSum = $this->getProductBalanceSumForCustomerIds($removedCustomerIds);
        return $productBalanceSum;

    }

    private function getProductBalanceSumForCustomerIds($customerIds)
    {

        $paymentTable = TableRegistry::getTableLocator()->get('Payments');
        $orderTable = TableRegistry::getTableLocator()->get('Orders');

        $productBalanceSum = 0;
        foreach($customerIds as $customerId) {
            $productPaymentSum = $paymentTable->getSum($customerId, 'product');
            $paybackPaymentSum = $paymentTable->getSum($customerId, 'payback');
            $productOrderSum = $orderTable->getSumProduct($customerId);
            $productBalance = $productPaymentSum - $paybackPaymentSum - $productOrderSum;
            $productBalanceSum += $productBalance;
        }

        return round($productBalanceSum, 2);

    }

    public function getDepositBalanceForDeletedCustomers()
    {

        $paymentTable = TableRegistry::getTableLocator()->get('Payments');

        $query = $paymentTable->find('all', [
            'contain' => [
                'Customers'
            ],
            'conditions' => [
                'Customers.id_customer IS NULL'
            ]
        ]);
        $query->group('Payments.id_customer');

        $removedCustomerIds = [];
        foreach($query as $payment) {
            $removedCustomerIds[] = $payment->id_customer;
        }

        $depositBalanceSum = $this->getDepositBalanceSumForCustomerIds($removedCustomerIds);
        return $depositBalanceSum;

    }

    public function getProductBalanceForCustomers($status)
    {
        $customerIds = $this->getCustomerIdsWithStatus($status);
        $productBalanceSum = $this->getProductBalanceSumForCustomerIds($customerIds);
        return $productBalanceSum;

    }

    public function getDepositBalanceForCustomers($status)
    {
        $customerIds = $this->getCustomerIdsWithStatus($status);
        $depositBalanceSum = $this->getDepositBalanceSumForCustomerIds($customerIds);
        return $depositBalanceSum;
    }

    public function getCustomerIdsWithStatus($status)
    {
        $conditions = [
            'Customers.active' => $status
        ];
        $conditions[] = $this->getConditionToExcludeHostingUser();
        $this->dropManufacturersInNextFind();
        $query = $this->find('all', [
            'contain' => [
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ],
            'conditions' => $conditions
        ]);

        $customerIds = [];
        foreach($query as $customer) {
            $customerIds[] = $customer->id_customer;
        }
        return $customerIds;
    }

    private function getDepositBalanceSumForCustomerIds($customerIds)
    {

        $paymentTable = TableRegistry::getTableLocator()->get('Payments');
        $orderTable = TableRegistry::getTableLocator()->get('Orders');

        $depositBalanceSum = 0;
        foreach($customerIds as $customerId) {
            $paymentSumDeposit = $paymentTable->getSum($customerId, 'deposit');
            $depositSum = $orderTable->getSumDeposit($customerId);
            $depositBalance = $paymentSumDeposit - $depositSum;
            $depositBalanceSum += $depositBalance;
        }
        return round($depositBalanceSum, 2);
    }

    public function getCreditBalance($customerId)
    {
        $payment = TableRegistry::getTableLocator()->get('Payments');
        $paymentProductSum = $payment->getSum($customerId, 'product');
        $paybackProductSum = $payment->getSum($customerId, 'payback');
        $paymentDepositSum = $payment->getSum($customerId, 'deposit');

        $order = TableRegistry::getTableLocator()->get('Orders');
        $productSum = $order->getSumProduct($customerId);
        $depositSum = $order->getSumDeposit($customerId);

        // rounding avoids problems with very tiny numbers (eg. 2.8421709430404E-14)
        return round($paymentProductSum - $paybackProductSum + $paymentDepositSum - $productSum - $depositSum, 2);
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
            $customersForDropdown[__('Members:_active')] = $onlineCustomers;
        }
        if (! empty($notYetOrderedCustomers)) {
            $customersForDropdown[__('Members:_never_ordered')] = $notYetOrderedCustomers;
        }
        if (! empty($onlineManufacturers)) {
            asort($onlineManufacturers);
            $customersForDropdown[__('Manufacturers:_active')] = $onlineManufacturers;
        }
        if (! empty($offlineManufacturers)) {
            asort($offlineManufacturers);
            $customersForDropdown[__('Manufacturers:_inactive')] = $offlineManufacturers;
        }
        if (! empty($offlineCustomers) && $includeOfflineCustomers) {
            $customersForDropdown[__('Members:_inactive')] = $offlineCustomers;
        }
        return $customersForDropdown;
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Table;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Utility\Security;
use Cake\Validation\Validator;
use Cake\Database\Expression\QueryExpression;
use Cake\Utility\Hash;
use Cake\Routing\Router;
use App\Model\Entity\Customer;
use App\Model\Entity\Manufacturer;
use App\Model\Entity\OrderDetail;
use App\Model\Entity\Payment;
use App\Services\FormatterService;
use Cake\ORM\TableRegistry;
use Cake\ORM\Query;
use Cake\ORM\Query\SelectQuery;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 *
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\Customer>
 */
class CustomersTable extends AppTable
{

    public function initialize(array $config): void
    {
        $this->setTable('customer');
        parent::initialize($config);
        $this->hasOne('AddressCustomers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasOne('Feedbacks', [
            'foreignKey' => 'customer_id',
        ]);
        $this->hasMany('ActiveOrderDetails', [
            'className' => 'OrderDetails',
            'foreignKey' => 'id_customer',
            'sort' => [
                'ActiveOrderDetails.created' => 'DESC'
            ]
        ]);
        $this->hasMany('PaidCashlessOrderDetails', [
            'className' => 'OrderDetails',
            'foreignKey' => 'id_customer',
            'sort' => [
                'PaidCashlessOrderDetails.created' => 'DESC'
            ]
        ]);
        // has many does not produce multiple records - this should be hasOne ideally...
        $this->hasMany('ValidOrderDetails', [
            'className' => 'OrderDetails',
            'foreignKey' => 'id_customer',
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
        $this->hasMany('Invoices', [
            'foreignKey' => 'id_customer',
            'conditions' => [
                'id_customer > 0',
            ],
            'sort' => [
                'created' => 'DESC'
            ]
        ]);

        $this->setPrimaryKey('id_customer');

        if ($this->getSchema()->hasColumn('send_cancellation_email')) {
            $this->getSchema()->setColumnType('send_cancellation_email', 'boolean');
        }
    }

    public function validationEdit(Validator $validator): Validator
    {
        $validator->notEmptyString('firstname', __('Please_enter_your_first_name.'));
        $validator
        ->add('lastname', 'custom', [
            'rule'=>  function ($value, $context) {
                if ((isset($context['data']['is_company']) && $context['data']['is_company'])
                    || strlen($value) >= 2) {
                    return true;
                }
                return false;
            },
            'message' => __('Please_enter_your_last_name.'),
        ]);
        $validator->inList('shopping_price', array_keys(Configure::read('app.htmlHelper')->getShoppingPricesForDropdown()), __('The_shopping_price_is_not_valid.'));
        return $validator;
    }

    public function validationRegistration(Validator $validator): Validator
    {
        $validator = $this->validationEdit($validator);
        $validator = $this->getValidationTermsOfUse($validator);
        return $validator;
    }

    public function validationChangePassword(Validator $validator): Validator
    {
        $validator
        ->notEmptyString('passwd_old', __('Please_enter_your_old_password.'))
        ->add('passwd_old', 'custom', [
            'rule'=>  function ($value, $context) {
                $user = $this->get($context['data']['id_customer']);
                if ((new DefaultPasswordHasher())->check($value, $user->passwd)) {
                    return true;
                }
                return false;
            },
            'message' => __('Your_old_password_is_wrong.')
        ])
        ->notEmptyString('passwd_old');

        $validator
        ->notEmptyString('passwd_1', __('Please_enter_a_new_password.'))
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
        ->notEmptyString('passwd_1');

        $validator
        ->notEmptyString('passwd_2', __('Please_enter_a_new_password.'))
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
        ->notEmptyString('passwd_2');

        return $validator;
    }

    public function validationNewPasswordRequest(Validator $validator): Validator
    {
        $validator->notEmptyString('email', __('Please_enter_your_email_address.'));
        $validator->email('email', true, __('The_email_address_is_not_valid.'));
        $validator->add('email', 'exists', [
            'rule' => function ($value, $context) {
                $customersTable = TableRegistry::getTableLocator()->get('Customers');
                return $customersTable->exists([
                    'email' => $value
                ]);
            },
            'message' => __('We_did_not_find_this_email_address.')
        ]);
        $validator->add('email', 'account_inactive', [
            'rule' => function ($value, $context) {
                $customersTable = TableRegistry::getTableLocator()->get('Customers');
                $record  = $customersTable->find('all',
                    conditions: [
                        'email' => $value,
                    ],
                )->first();
                if (!empty($record) && !$record->active) {
                    return false;
                }
                return true;
            },
            'message' => __('Your_account_is_not_active_any_more._If_you_want_to_reactivate_it_please_write_an_email.')
        ]);
        return $validator;
    }

    public function validationTermsOfUse(Validator $validator): Validator
    {
        return $this->getValidationTermsOfUse($validator);
    }

    private function getValidationTermsOfUse(Validator $validator): Validator
    {
        return $validator->equals('terms_of_use_accepted_date_checkbox', 1, __('Please_accept_the_terms_of_use.'));
    }

    /**
     * @param array<string, mixed> $options
     * @param SelectQuery<\App\Model\Entity\Customer> $query
     * @return SelectQuery<\App\Model\Entity\Customer>
     */
    public function findAuth(SelectQuery $query, array $options): SelectQuery
    {
        $query->where([
            'Customers.active' => APP_ON
        ]);
        $query->contain([
            'AddressCustomers'
        ]);
        return $query;
    }

    /**
     * @template T of array|\Cake\Datasource\EntityInterface
     * @param SelectQuery<T> $query
     */
    public function sortByVirtualField(SelectQuery $query, string $name): object
    {
        $sortedObject = (object) Hash::sort($query->toArray(), '{n}.' . $name, 'ASC', [
            'type' => 'locale',
            'ignoreCase' => true,
        ]);
        return $sortedObject;
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->getAssociation('ValidOrderDetails')->setConditions([
            (new QueryExpression())->in('ValidOrderDetails.order_state', Configure::read('app.htmlHelper')->getOrderStateIds()),
        ]);
        $this->getAssociation('ActiveOrderDetails')->setConditions([
            (new QueryExpression())->in('ActiveOrderDetails.order_state', [
                OrderDetail::STATE_OPEN,
                OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER,
            ]),
        ]);
        $this->getAssociation('PaidCashlessOrderDetails')->setConditions([
            (new QueryExpression())->in('PaidCashlessOrderDetails.order_state', OrderDetail::ORDER_STATES_CASHLESS),
        ]);
    }

    public function getCustomerName(string $tableName = 'Customers'): string
    {
        $concat = $tableName . '.firstname, " ", ' . $tableName . '.lastname';
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            $concat = $tableName . '.lastname, " ", ' . $tableName . '.firstname';
        }
        $sql = 'IF(' . $tableName . '.is_company,' . $tableName . '.firstname,CONCAT('.$concat.'))';
        return $sql;
    }

    /**
     * @template T of array|\Cake\Datasource\EntityInterface
     * @param SelectQuery<T> $query
     * @return SelectQuery<T>
     */
    public function addCustomersNameForOrderSelect(SelectQuery $query): SelectQuery
    {
        $sql = $this->getCustomerName();
        return $query->select(['CustomerNameForOrder' => $sql]);
    }

    /**
     * @return array<string, string>
     */
    public function getCustomerOrderClause(string $direction): array
    {
        $result = [
            'CustomerNameForOrder' => $direction,
        ];
        return $result;
    }

    /**
     * @return array{price: string|float|int, price_incl_per_unit: string|float|int|null, deposit: float|int}
     */
    public function getModifiedProductPricesByShoppingPrice(int $productId, string $price, float|string|null $priceInclPerUnit, float $deposit, string|float $taxRate): array
    {

        $result = [
            'price' => $price,
            'price_incl_per_unit' => $priceInclPerUnit,
            'deposit' => $deposit,
        ];

        $identity = Router::getRequest()->getAttribute('identity');

        if ($identity === null) {
            return $result;
        }

        if ($identity->shopping_price == Customer::PURCHASE_PRICE) {
            $productsTable = TableRegistry::getTableLocator()->get('Products');
            $purchasePrices = $productsTable->find('all',
                conditions: [
                    'Products.id_product' => $productId,
                ],
                contain: [
                    'PurchasePriceProducts.Taxes',
                    'UnitProducts',
                ]
            )->first();

            if (!empty($purchasePrices->purchase_price_product)) {
                $result['price'] = $purchasePrices->purchase_price_product->price;
            }

            if (!empty($purchasePrices->unit_product) && !is_null($purchasePrices->unit_product->purchase_price_incl_per_unit)) {
                $purchasePriceTaxRate = !empty($purchasePrices->purchase_price_product->tax) ? $purchasePrices->purchase_price_product->tax->rate : 0;
                $priceInclPerUnitNet = $productsTable->getNetPrice($purchasePrices->unit_product->purchase_price_incl_per_unit, $purchasePriceTaxRate);
                $priceInclPerUnitGrossWithSellingPriceTax = $productsTable->getGrossPrice($priceInclPerUnitNet, $taxRate);
                $result['price_incl_per_unit'] = $priceInclPerUnitGrossWithSellingPriceTax;
            }
        }

        if ($identity->shopping_price == Customer::ZERO_PRICE) {
            $result['price'] = 0;
            $result['price_incl_per_unit'] = 0;
            $result['deposit'] = 0;
        }

        return $result;

    }

    /**
     * @return array{price: string|float|int, price_incl_per_unit: string|float|int|null, deposit: float|int}
     */
    public function getModifiedAttributePricesByShoppingPrice(int $productId, int $productAttributeId, string $price, string|float|null $priceInclPerUnit, float $deposit, string|float $taxRate): array
    {

        $result = [
            'price' => $price,
            'price_incl_per_unit' => $priceInclPerUnit,
            'deposit' => $deposit,
        ];

        $identity = Router::getRequest()->getAttribute('identity');

        if ($identity === null) {
            return $result;
        }

        if ($identity->shopping_price == Customer::PURCHASE_PRICE) {

            $productsTable = TableRegistry::getTableLocator()->get('Products');
            $purchasePrices = $productsTable->find('all',
                conditions: [
                    'Products.id_product' => $productId,
                ],
                contain: [
                    'PurchasePriceProducts.Taxes',
                    'ProductAttributes.PurchasePriceProductAttributes',
                    'ProductAttributes.UnitProductAttributes',
                ]
            )->first();

            $foundPurchasePriceProductAttribute = null;
            foreach ($purchasePrices->product_attributes as $purchasePriceProductAttribute) {
                if ($purchasePriceProductAttribute->id_product_attribute == $productAttributeId) {
                    $foundPurchasePriceProductAttribute = $purchasePriceProductAttribute;
                    continue;
                }
            }

            if (!empty($foundPurchasePriceProductAttribute)) {
                if (!empty($foundPurchasePriceProductAttribute->purchase_price_product_attribute)) {
                    $result['price'] = $foundPurchasePriceProductAttribute->purchase_price_product_attribute->price;
                }
                if (!empty($foundPurchasePriceProductAttribute->unit_product_attribute) && !is_null($foundPurchasePriceProductAttribute->unit_product_attribute->purchase_price_incl_per_unit)) {
                    $purchasePriceTaxRate = !empty($purchasePrices->purchase_price_product->tax) ? $purchasePrices->purchase_price_product->tax->rate : 0;
                    $priceInclPerUnitNet = $productsTable->getNetPrice($foundPurchasePriceProductAttribute->unit_product_attribute->purchase_price_incl_per_unit, $purchasePriceTaxRate);
                    $priceInclPerUnitGrossWithSellingPriceTax = $productsTable->getGrossPrice($priceInclPerUnitNet, $taxRate);
                    $result['price_incl_per_unit'] = $priceInclPerUnitGrossWithSellingPriceTax;
                }
            }

        }

        if ($identity->shopping_price == Customer::ZERO_PRICE) {
            $result['price'] = 0;
            $result['price_incl_per_unit'] = 0;
            $result['deposit'] = 0;
        }

        return $result;

    }

    public function getPersonalTransactionCode(int|string $customerId): string
    {
        $customer = $this->find('all',
        conditions: [
            'Customers.id_customer' => $customerId,
        ],
        fields: [
            'personalTransactionCode' => $this->getPersonalTransactionCodeField(),
        ])->first();
        return $customer->personalTransactionCode;
    }

    public function getPersonalTransactionCodeField(): string
    {
        return 'UPPER(SUBSTRING(SHA1(CONCAT(Customers.id_customer, "' .  Security::getSalt() . '", "personal-transaction-code")), 1, 8))';
    }

    /**
     * @return array<int, mixed>
     */
    public function getConditionToExcludeHostingUser(): array
    {
        $result = [];
        if (Configure::read('app.hostingEmail') != '') {
            $result = [
                (new QueryExpression())->notEq('Customers.email', Configure::read('app.hostingEmail')),
            ];
        }
        return $result;
    }

    public function dropManufacturersInNextFind(): void
    {
        $this->getAssociation('AddressCustomers')->setJoinType('INNER');
    }

    public function getBarcodeFieldString(): string
    {
        return 'SUBSTRING(SHA1(CONCAT(' . $this->aliasField('id_customer') .', "' .  Security::getSalt() . '", "customer")), 1, 6)';
    }

    public function getManufacturerRecord(Customer $customer): ?Manufacturer
    {
        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturer = $manufacturersTable->find('all',
            conditions: [
                'AddressManufacturers.email' => $customer->email,
            ],
            contain: [
                'AddressManufacturers',
            ]
        )->first();
        return $manufacturer;
    }

    public function setNewPassword(int $customerId): string
    {
        $ph = new DefaultPasswordHasher();
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

    public function getManufacturerByCustomerId(int $customerId): ?Manufacturer
    {
        $customer = $this->find('all', conditions: [
            $this->aliasField('id_customer') => $customerId,
        ])->first();
        if (!empty($customer)) {
            return $this->getManufacturerRecord($customer);
        }
        return null;
    }

    public function getManufacturerIdByCustomerId(int $customerId): int
    {
        $manufacturer = $this->getManufacturerByCustomerId($customerId);
        if (!empty($manufacturer)) {
            return $manufacturer->id_manufacturer;
        }
        return 0;
    }

    public function getProductBalanceForDeletedCustomers(): float
    {

        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $query = $orderDetailsTable->find('all',
            conditions: [
                'Customers.id_customer IS NULL',
            ],
            contain: [
                'Customers',
            ],
        );
        $query->select('OrderDetails.id_customer'); // avoids error if sql_mode = ONLY_FULL_GROUP_BY
        $query->groupBy('OrderDetails.id_customer');

        $removedCustomerIds = [];
        foreach($query as $orderDetail) {
            $removedCustomerIds[] = $orderDetail->id_customer;
        }

        return $this->getProductBalanceSumForCustomerIds($removedCustomerIds);
    }

    /**
     * @param array<int> $customerIds
     */
    private function getProductBalanceSumForCustomerIds(array $customerIds): float
    {

        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');

        $productBalanceSum = 0;
        foreach($customerIds as $customerId) {
            $productPaymentSum = $paymentsTable->getSum($customerId, Payment::TYPE_PRODUCT);
            $paybackPaymentSum = $paymentsTable->getSum($customerId, Payment::TYPE_PAYBACK);
            $productOrderSum = $orderDetailsTable->getSumProduct($customerId);
            $productBalance = $productPaymentSum - $paybackPaymentSum - $productOrderSum;
            $productBalanceSum += $productBalance;
        }

        return round($productBalanceSum, 2);

    }

    public function getDepositBalanceForDeletedCustomers(): float
    {

        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');
        $query = $paymentsTable->find('all',
            conditions: [
                'Customers.id_customer IS NULL'
            ],
            contain: [
                'Customers'
            ],
        );
        $query->select('Payments.id_customer'); // avoids error if sql_mode = ONLY_FULL_GROUP_BY
        $query->groupBy('Payments.id_customer');

        $removedCustomerIds = [];
        foreach($query as $payment) {
            $removedCustomerIds[] = $payment->id_customer;
        }

        return $this->getDepositBalanceSumForCustomerIds($removedCustomerIds);

    }

    public function getProductBalanceForCustomers(int $status): float
    {
        $customerIds = $this->getCustomerIdsWithStatus($status);
        $productBalanceSum = $this->getProductBalanceSumForCustomerIds($customerIds);
        return $productBalanceSum;

    }

    public function getDepositBalanceForCustomers(int $status): float
    {
        $customerIds = $this->getCustomerIdsWithStatus($status);
        $depositBalanceSum = $this->getDepositBalanceSumForCustomerIds($customerIds);
        return $depositBalanceSum;
    }

    /**
     * @return list<int>
     */
    public function getCustomerIdsWithStatus(int $status): array
    {
        $conditions = [
            $this->aliasField('active') => $status,
        ];
        $conditions[] = $this->getConditionToExcludeHostingUser();
        $this->dropManufacturersInNextFind();

        $query = $this->find('all',
            contain: [
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ],
            conditions: $conditions,
        );

        $customerIds = Hash::extract($query->toArray(), '{n}.id_customer');
        return $customerIds;
    }

    /**
     * @param array<int> $customerIds
     */
    private function getDepositBalanceSumForCustomerIds(array $customerIds): float
    {

        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');

        $depositBalanceSum = 0;
        foreach($customerIds as $customerId) {
            $paymentSumDeposit = $paymentsTable->getSum($customerId, Payment::TYPE_DEPOSIT);
            $depositSum = $orderDetailsTable->getSumDeposit($customerId);
            $depositBalance = $paymentSumDeposit - $depositSum;
            $depositBalanceSum += $depositBalance;
        }
        return round($depositBalanceSum, 2);
    }

    public function getDepositBalance(int $customerId): float
    {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');
        $paymentDepositSum = $paymentsTable->getSum($customerId, Payment::TYPE_DEPOSIT);
        $depositSum = $orderDetailsTable->getSumDeposit($customerId);

        $creditBalance = $paymentDepositSum - $depositSum;
        return FormatterService::assureCorrectFloat($creditBalance);

    }

    public function getCreditBalance(int $customerId): float
    {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');
        $paymentProductSum = $paymentsTable->getSum($customerId, Payment::TYPE_PRODUCT);
        $paymentPaybackSum = $paymentsTable->getSum($customerId, Payment::TYPE_PAYBACK);
        $paymentDepositSum = $paymentsTable->getSum($customerId, Payment::TYPE_DEPOSIT);

        $productSum = $orderDetailsTable->getSumProduct($customerId);
        $depositSum = $orderDetailsTable->getSumDeposit($customerId);

        $creditBalance = $paymentProductSum - $paymentPaybackSum + $paymentDepositSum - $productSum - $depositSum;
        return FormatterService::assureCorrectFloat($creditBalance);
    }


    /**
     * @param array<string, mixed> $conditions
     */
    /**
     * @param array<string, mixed> $conditions
     * @return array<string, array<int, string>>
     */
    public function getForDropdown(bool $includeManufacturers = false, bool $includeOfflineCustomers = true, array $conditions = []): array
    {

        $contain = [];
        if (! $includeManufacturers) {
            $this->dropManufacturersInNextFind();
            $contain[] = 'AddressCustomers'; // to make exclude happen using dropManufacturersInNextFind
        }

        $conditions = array_merge($conditions, $this->getConditionToExcludeHostingUser());

        $customers = $this->find('all',
        conditions: $conditions,
        order: $this->getCustomerOrderClause('ASC'),
        contain: $contain);
        $customers = $this->addCustomersNameForOrderSelect($customers);
        $customers->select($this);
        $addressCustomersTable = TableRegistry::getTableLocator()->get('AddressCustomers');
        if (! $includeManufacturers) {
            $customers->select($addressCustomersTable);
        }

        $customers = $customers->toArray();

        if (! $includeManufacturers) {
            $validOrderDetails = $this->getAssociation('ValidOrderDetails');
            $i = 0;
            foreach($customers as $customer) {
                $customers[$i]->validOrderDetailCount = $validOrderDetails->find('all', conditions: [
                    'id_customer' => $customers[$i]->id_customer
                ])->count();
                $i++;
            }
        }
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
                    $decodedManufacturerName = $manufacturer->decoded_name;
                    if ($manufacturer->active) {
                        $onlineManufacturers[$customer->id_customer] = $decodedManufacturerName;
                    } else {
                        $offlineManufacturers[$customer->id_customer] = $decodedManufacturerName;
                    }
                    $manufacturerIncluded = true;
                }
            }

            if (! $manufacturerIncluded) {
                if ($customer->active == 0) {
                    $offlineCustomers[$customer->id_customer] = $userNameForDropdown;
                } else {
                    if (! $includeManufacturers) {
                        if ($customer->validOrderDetailCount == 0) {
                            $notYetOrderedCustomers[$customer->id_customer] = $userNameForDropdown;
                        } else {
                            $onlineCustomers[$customer->id_customer] = $userNameForDropdown;
                        }
                    } else {
                        $onlineCustomers[$customer->id_customer] = $userNameForDropdown;
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

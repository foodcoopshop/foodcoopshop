<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class ManufacturersTable extends AppTable
{

    public function initialize(array $config): void
    {
        $this->setTable('manufacturer');
        parent::initialize($config);
        $this->setPrimaryKey('id_manufacturer');
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasOne('AddressManufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->hasMany('Invoices', [
            'foreignKey' => 'id_manufacturer',
            'sort' => [
                'created' => 'DESC'
            ]
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', __('Please_enter_a_name.'));
        $range = [3, 64];
        $validator->lengthBetween('name', $range, __('Please_enter_between_{0}_and_{1}_characters.', $range));
        $validator->allowEmptyString('iban');
        $validator->add('iban', 'iban', [
            'rule' => 'iban',
            'message' => __('Please_enter_a_valid_IBAN.')
        ]);
        $validator->allowEmptyString('bic');
        $validator->add('bic', 'validFormat', [
            'rule' => ['custom', BIC_REGEX],
            'message' => __('Please_enter_a_valid_BIC.')
        ]);
        $validator->allowEmptyString('homepage');
        $validator->urlWithProtocol('homepage', __('Please_enter_a_valid_internet_address.'));
        return $validator;
    }

    public function validationEditOptions(Validator $validator)
    {
        $validator->allowEmptyString('send_order_list_cc');
        $validator->add('send_order_list_cc', 'multipleEmails', [
            'rule' => 'ruleMultipleEmails',
            'provider' => 'table',
            'message' => __('At_least_one_email_is_not_valid._Please_separate_multiple_with_comma_without_space.')
        ]);

        $validator->allowEmptyString('no_delivery_days');
        $validator->add('no_delivery_days', 'noDeliveryDaysOrdersExist', [
            'provider' => 'table',
            'rule' => 'noDeliveryDaysOrdersExist'
        ]);

        $validator->numeric('timebased_currency_max_percentage', __('Decimals_are_not_allowed.'));
        $validator = $this->getNumberRangeValidator($validator, 'timebased_currency_max_percentage', 0, 100);
        $validator->numeric('timebased_currency_max_credit_balance', __('Decimals_are_not_allowed.'));
        $validator = $this->getNumberRangeValidator($validator, 'timebased_currency_max_credit_balance', 0, 400);
        return $validator;
    }

    public function getManufacturerByIdForSendingOrderListsOrInvoice($manufacturerId)
    {
        $manufacturer = $this->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId,
            ],
            'order' => [
                'Manufacturers.name' => 'ASC',
            ],
            'contain' => [
                'AddressManufacturers',
                'Customers.AddressCustomers',
            ],
        ])->first();
        return $manufacturer;
    }

    public function hasManufacturerReachedTimebasedCurrencyLimit($manufacturerId)
    {
        $manufacturer = $this->find('all', [
            'conditions' => ['id_manufacturer' => $manufacturerId]
        ])->first();

        $timebasedCurrencyOrderDetailsTable = FactoryLocator::get('Table')->get('TimebasedCurrencyOrderDetails');
        $creditBalance = $timebasedCurrencyOrderDetailsTable->getCreditBalance($manufacturerId, null);

        $activeLimit = Configure::read('appDb.FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_MANUFACTURER') * 3600;

        if ($manufacturer->timebased_currency_max_credit_balance > 0) {
            $activeLimit = $manufacturer->timebased_currency_max_credit_balance;
        }

        if ($activeLimit > $creditBalance) {
            return false;
        }

        return true;

    }

    public function getTimebasedCurrencyMoney($price, $percentage)
    {
        return $price * $percentage / 100;
    }

    public function getCartTimebasedCurrencySeconds($price, $percentage)
    {
        $result = $this->getTimebasedCurrencyMoney($price, $percentage) * (int) Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE') / 100 * 3600;
        $result = round($result, 0);
        return $result;
    }

    /**
     * @param $boolean $sendOrderedProductDeletedNotification
     * @return boolean
     */
    public function getOptionSendOrderedProductDeletedNotification($sendOrderedProductDeletedNotification)
    {
        $result = $sendOrderedProductDeletedNotification;
        if (is_null($sendOrderedProductDeletedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductDeletedNotification');
        }
        return (boolean) $result;
    }

    /**
     * @param $boolean $sendOrderedProductPriceChangedNotification
     * @return boolean
     */
    public function getOptionSendOrderedProductPriceChangedNotification($sendOrderedProductPriceChangedNotification)
    {
        $result = $sendOrderedProductPriceChangedNotification;
        if (is_null($sendOrderedProductPriceChangedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductPriceChangedNotification');
        }
        return (boolean) $result;
    }

    /**
     * @param $boolean $sendOrderedProductAmountChangedNotification
     * @return boolean
     */
    public function getOptionSendOrderedProductAmountChangedNotification($sendOrderedProductAmountChangedNotification)
    {
        $result = $sendOrderedProductAmountChangedNotification;
        if (is_null($sendOrderedProductAmountChangedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductAmountChangedNotification');
        }
        return (boolean) $result;
    }

    /**
     * @param $boolean $sendInvoice
     * @return boolean
     */
    public function getOptionSendInstantOrderNotification($sendInstantOrderNotification)
    {
        $result = $sendInstantOrderNotification;
        if (is_null($sendInstantOrderNotification)) {
            $result = Configure::read('app.defaultSendInstantOrderNotification');
        }
        return (boolean) $result;
    }

    /**
     * @param $boolean $sendInvoice
     * @return boolean
     */
    public function getOptionSendInvoice($sendInvoice)
    {
        $result = $sendInvoice;
        if (is_null($sendInvoice)) {
            $result = Configure::read('app.defaultSendInvoice');
        }
        return (boolean) $result;
    }

    /**
     * @param int $defaultTaxId
     * @return int
     */
    public function getOptionTimebasedCurrencyEnabled($timebasedCurrencyEnabled)
    {
        $result = false;
        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $timebasedCurrencyEnabled) {
            $result = true;
        }
        return $result;
    }
    /**
     * @param int $defaultTaxId
     * @return int
     */
    public function getOptionDefaultTaxId($defaultTaxId)
    {
        $result = $defaultTaxId;
        if (is_null($defaultTaxId)) {
            $result = Configure::read('app.defaultTaxId');
        }
        return $result;
    }

    /**
     * @param int $variableMemberFee
     * @return int
     */
    public function getOptionVariableMemberFee($variableMemberFee)
    {
        $result = $variableMemberFee;
        if (is_null($variableMemberFee)) {
            $result = Configure::read('appDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE');
        }
        return $result;
    }

    /**
     * @param $boolean $sendOrderList
     * @return boolean
     */
    public function getOptionSendOrderList($sendOrderList)
    {
        $result = $sendOrderList;
        if (is_null($sendOrderList)) {
            $result = Configure::read('app.defaultSendOrderList');
        }
        return $result;
    }

    /**
     * @param $string $sendOrderListCc
     * @return array
     */
    public function getOptionSendOrderListCc($sendOrderListCc)
    {
        $ccRecipients = [];
        if (is_null($sendOrderListCc) || $sendOrderListCc == '') {
            return $ccRecipients;
        }

        $ccs = explode(',', $sendOrderListCc);
        foreach ($ccs as $cc) {
            $ccRecipients[] = $cc;
        }
        return $ccRecipients;
    }

    /**
     * @param string $email
     */
    public function getCustomerRecord($email)
    {
        $cm = FactoryLocator::get('Table')->get('Customers');

        if (empty($email)) {
            return [];
        }

        $customer = $cm->find('all', [
            'conditions' => [
                'Customers.email' => $email
            ]
        ])->first();

        if (empty($customer->address_customer->id_address)) {
            return $customer;
        }

        if (!empty($customer->address_customer)) {
            return [];
        }

        return $customer;
    }

    public function getForMenu($appAuth)
    {

        $conditions = [
            'Manufacturers.active' => APP_ON
        ];
        if (! $appAuth->user()) {
            $conditions['Manufacturers.is_private'] = APP_OFF;
        }

        $manufacturers = $this->find('all', [
            'fields' => [
                'Manufacturers.id_manufacturer',
                'Manufacturers.name',
                'Manufacturers.no_delivery_days'
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'conditions' => $conditions
        ]);

        $manufacturersForMenu = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerName = $manufacturer->name;
            $additionalInfo = '';
            if ($appAuth->user() || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                $additionalInfo = $this->getProductsByManufacturerId($appAuth, $manufacturer->id_manufacturer, true);
            }
            $noDeliveryDaysString = Configure::read('app.htmlHelper')->getManufacturerNoDeliveryDaysString($manufacturer);
            if ($noDeliveryDaysString != '') {
                $noDeliveryDaysString = __('Delivery_break') . ': ' . $noDeliveryDaysString;
                if ($appAuth->user() || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                    $additionalInfo .= ' - ';
                }
                $additionalInfo .= $noDeliveryDaysString;
            }
            if ($additionalInfo != '') {
                $manufacturerName .= ' <span class="additional-info">('.$additionalInfo.')</span>';
            }
            $manufacturersForMenu[] = [
                'name' => $manufacturerName,
                'slug' => Configure::read('app.slugHelper')->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name)
            ];
        }
        return $manufacturersForMenu;
    }

    /**
     * @param float $price
     * @param integer $variableMemberFee
     * @return float
     */
    public function increasePriceWithVariableMemberFee($price, $variableMemberFee)
    {
        return $price + $this->getVariableMemberFeeAsFloat($price, $variableMemberFee);
    }

    /**
     * @param float $price
     * @param integer $variableMemberFee
     * @return float
     */
    public function decreasePriceWithVariableMemberFee($price, $variableMemberFee)
    {
        return $price - $this->getVariableMemberFeeAsFloat($price, $variableMemberFee);
    }

    /**
     * @param float $price
     * @param integer $variableMemberFee
     * @return float
     */
    public function getVariableMemberFeeAsFloat($price, $variableMemberFee)
    {
        return round($price * $variableMemberFee / 100, 2);
    }

    public function getTimebasedCurrencyManufacturersForDropdown()
    {
        $manufacturers = $this->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'conditions' => [
                'Manufacturers.timebased_currency_enabled' => APP_ON
            ]
        ]);
        $result = [];
        foreach ($manufacturers as $manufacturer) {
            $result[$manufacturer->id_manufacturer] = $manufacturer->name;
        }
        return $result;
    }

    public function getForDropdown()
    {
        $manufacturers = $this->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ]);

        $offlineManufacturers = [];
        $onlineManufacturers = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerNameForDropdown = html_entity_decode($manufacturer->name);
            if ($manufacturer->active == 0) {
                $offlineManufacturers[$manufacturer->id_manufacturer] = $manufacturerNameForDropdown;
            } else {
                $onlineManufacturers[$manufacturer->id_manufacturer] = $manufacturerNameForDropdown;
            }
        }
        $manufacturersForDropdown = [];
        if (! empty($onlineManufacturers)) {
            $manufacturersForDropdown[__('online')] = $onlineManufacturers;
        }
        if (! empty($offlineManufacturers)) {
            $manufacturersForDropdown[__('offline')] = $offlineManufacturers;
        }

        return $manufacturersForDropdown;
    }

    public function getProductsByManufacturerId($appAuth, $manufacturerId, $countMode = false)
    {
        $sql = "SELECT ";
        $sql .= $this->getFieldsForProductListQuery();
        $sql .= "FROM ".$this->tablePrefix."product Products ";
        $sql .= $this->getJoinsForProductListQuery();
        $sql .= $this->getConditionsForProductListQuery($appAuth);
        $sql .= "AND Manufacturers.id_manufacturer = :manufacturerId";
        $sql .= $this->getOrdersForProductListQuery();

        $params = [
            'manufacturerId' => $manufacturerId,
            'active' => APP_ON
        ];
        if (empty($appAuth->user())) {
            $params['isPrivate'] = APP_OFF;
        }

        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $products = $statement->fetchAll('assoc');
        $products = $this->hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($appAuth, $products);

        if (! $countMode) {
            return $products;
        } else {
            return count($products);
        }

    }

    public function getDataForInvoiceOrOrderList($manufacturerId, $order, $dateFrom, $dateTo, $orderState, $includeStockProductsInInvoices, $orderDetailIds = [])
    {
        switch ($order) {
            case 'product':
                $orderClause = 'od.product_name ASC, t.rate ASC, ' . Configure::read('app.htmlHelper')->getCustomerNameForSql() . ' ASC';
                break;
            case 'customer':
                $orderClause = Configure::read('app.htmlHelper')->getCustomerNameForSql() . ' ASC, od.product_name ASC';
                break;
        }

        // do not use params for $orderState, it will result in IN ('3,2,1') which is wrong
        $params = [
            'manufacturerId' => $manufacturerId
        ];

        $includeStockProductCondition = '';

        if (is_null($dateTo)) {
            // order list
            $orderDetailCondition = "AND od.id_order_detail IN (" . join(',', $orderDetailIds) . ")" ;
            $dateConditions = "";
        } else {
            // invoice
            $dateConditions  = "AND DATE_FORMAT(od.pickup_day, '%Y-%m-%d') >= :dateFrom ";
            $dateConditions .= "AND DATE_FORMAT(od.pickup_day, '%Y-%m-%d') <= :dateTo" ;
            $params['dateFrom'] = Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom);
            $params['dateTo'] = Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo);
            if (!$includeStockProductsInInvoices) {
                $includeStockProductCondition = "AND (p.is_stock_product = 0 OR m.stock_management_enabled = 0)";
            }
            $orderDetailCondition = "";
        }

        $orderStateCondition = "";
        if (!empty($orderState)) {
            $orderStateCondition = "AND od.order_state IN (" . join(',', $orderState) . ")";
        }

        $customerNameAsSql = Configure::read('app.htmlHelper')->getCustomerNameForSql();

        $sql = "SELECT
        m.id_manufacturer ManufacturerId,
        m.name AS ManufacturerName,
        m.uid_number as ManufacturerUidNumber,
        m.additional_text_for_invoice as ManufacturerAdditionalTextForInvoice,
        ma.firstname as ManufacturerFirstname, ma.lastname as ManufacturerLastname, ma.address1 as ManufacturerAddress1, ma.postcode as ManufacturerPostcode, ma.city as ManufacturerCity,
        t.rate as TaxRate,
        odt.total_amount AS OrderDetailTaxAmount,
        od.id_order_detail AS OrderDetailId,
        od.product_id AS ProductId,
        od.product_name AS ProductName,
        od.product_amount AS OrderDetailAmount,
        od.total_price_tax_incl AS OrderDetailPriceIncl,
        od.total_price_tax_excl as OrderDetailPriceExcl,
        odu.quantity_in_units as OrderDetailUnitQuantityInUnits,
        odu.product_quantity_in_units as OrderDetailUnitProductQuantityInUnits,
        odu.unit_name as OrderDetailUnitUnitName,
        od.created as OrderDetailCreated,
        od.pickup_day as OrderDetailPickupDay,
        c.id_customer AS CustomerId,
        {$customerNameAsSql} AS CustomerName
        FROM ".$this->tablePrefix."order_detail od
            LEFT JOIN ".$this->tablePrefix."product p ON p.id_product = od.product_id
            LEFT JOIN ".$this->tablePrefix."order_detail_tax odt ON odt.id_order_detail = od.id_order_detail
            LEFT JOIN ".$this->tablePrefix."order_detail_units odu ON od.id_order_detail = odu.id_order_detail
            LEFT JOIN ".$this->tablePrefix."customer c ON c.id_customer = od.id_customer
            LEFT JOIN ".$this->tablePrefix."manufacturer m ON m.id_manufacturer = p.id_manufacturer
            LEFT JOIN ".$this->tablePrefix."address ma ON m.id_manufacturer = ma.id_manufacturer
            LEFT JOIN ".$this->tablePrefix."tax t ON od.id_tax = t.id_tax
            WHERE 1
            {$dateConditions}
            AND m.id_manufacturer = :manufacturerId
            AND ma.id_manufacturer > 0
            {$orderStateCondition}
            {$includeStockProductCondition}
            {$orderDetailCondition}
            ORDER BY {$orderClause}, DATE_FORMAT (od.created, '%d.%m.%Y, %H:%i') DESC;";

        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetchAll('assoc');
        return $result;
    }
}

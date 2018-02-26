<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
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

class ManufacturersTable extends AppTable
{

    public function initialize(array $config)
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
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->addBehavior('Timestamp');
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('name', 'Bitte gib einen Namen an.');
        $validator->lengthBetween('name', [3, 64], 'Bitte gib zwischen 3 und 64 Zeichen ein.');
        $validator->allowEmpty('iban');
        $validator->add('iban', 'validFormat', [
            'rule' => array('custom', IBAN_REGEX),
            'message' => 'Bitte gib einen gültigen IBAN ein.'
        ]);
        $validator->allowEmpty('bic');
        $validator->add('bic', 'validFormat', [
            'rule' => array('custom', BIC_REGEX),
            'message' => 'Bitte gib einen gültigen BIC ein.'
        ]);
        $validator->allowEmpty('homepage');
        $validator->urlWithProtocol('homepage', 'Bitte gibt eine gültige Internet-Adresse an.');
        return $validator;
    }
    
    public function validationEditOptions(Validator $validator)
    {
        $validator->allowEmpty('send_order_list_cc');
        $validator->add('send_order_list_cc', 'multipleEmails', [
            'rule' => 'ruleMultipleEmails',
            'provider' => 'table',
            'message' => 'Mindestens eine E-Mail-Adresse ist nicht gültig. Mehrere bitte mit , trennen (ohne Leerzeichen).'
        ]);
        return $validator;
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
     * @param $boolean $sendOrderedProductQuantityChangedNotification
     * @return boolean
     */
    public function getOptionSendOrderedProductQuantityChangedNotification($sendOrderedProductQuantityChangedNotification)
    {
        $result = $sendOrderedProductQuantityChangedNotification;
        if (is_null($sendOrderedProductQuantityChangedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductQuantityChangedNotification');
        }
        return (boolean) $result;
    }

    /**
     * @param $boolean $sendInvoice
     * @return boolean
     */
    public function getOptionSendShopOrderNotification($sendShopOrderNotification)
    {
        $result = $sendShopOrderNotification;
        if (is_null($sendShopOrderNotification)) {
            $result = Configure::read('app.defaultSendShopOrderNotification');
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
     * @param $boolean $bulkOrdersAllowed
     * @return boolean
     */
    public function getOptionBulkOrdersAllowed($bulkOrdersAllowed)
    {
        $result = $bulkOrdersAllowed;
        if (is_null($bulkOrdersAllowed)) {
            $result = Configure::read('app.defaultBulkOrdersAllowed');
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
        if (is_null($defaultTaxId)) { // !sic
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
        if (is_null($sendOrderListCc)) {
            return $ccRecipients;
        }

        $ccs = explode(',', $sendOrderListCc);
        foreach ($ccs as $cc) {
            $ccRecipients[] = $cc;
        }
        return $ccRecipients;
    }

    /**
     * bindings with email as foreign key was tricky...
     *
     * @param array $manufacturer
     * @return boolean
     */
    public function getCustomerRecord($manufacturer)
    {
        $cm = TableRegistry::get('Customers');

        if (empty($manufacturer->address_manufacturer)) {
            return [];
        }
        
        $customer = $cm->find('all', [
            'conditions' => [
                'Customers.email' => $manufacturer->address_manufacturer->email
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

    /**
     * @param int $manufacturerId
     * @return array
     */
    public function getCustomerByManufacturerId($manufacturerId)
    {
        $manufacturer = $this->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();
        if (!empty($manufacturer)) {
            return $this->getCustomerRecord($manufacturer);
        }
        return false;
    }

    public function getCustomerIdByManufacturerId($manufacturerId)
    {
        $customer = $this->getCustomerByManufacturerId($manufacturerId);
        if (!empty($customer)) {
            return $$customer['Customers']['id_customer'];
        }
        return 0;
    }

    public function hasCustomerRecord($manufacturer)
    {
        return (boolean) count($this->getCustomerRecord($manufacturer));
    }

    public function getForMenu($appAuth)
    {
        
        if ($appAuth->user() || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            $productModel = TableRegistry::get('Products');
        }
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
                'Manufacturers.holiday_from',
                'Manufacturers.holiday_to',
                'is_holiday_active' => '!' . $this->getManufacturerHolidayConditions()
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
                $additionalInfo = $productModel->getCountByManufacturerId($manufacturer->id_manufacturer);
            }
            $holidayInfo = Configure::read('app.htmlHelper')->getManufacturerHolidayString($manufacturer->holiday_from, $manufacturer->holiday_to, $manufacturer->is_holiday_active);
            if ($holidayInfo != '') {
                $holidayInfo = 'Lieferpause ' . $holidayInfo;
                if ($manufacturer->iss_holiday_active) {
                    $additionalInfo = $holidayInfo;
                } else {
                    if ($appAuth->user() || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                        $additionalInfo .= ' - ';
                    }
                    $additionalInfo .= $holidayInfo;
                }
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
            $manufacturerNameForDropdown = $manufacturer->name;
            if ($manufacturer->active == 0) {
                $offlineManufacturers[$manufacturer->id_manufacturer] = $manufacturerNameForDropdown;
            } else {
                $onlineManufacturers[$manufacturer->id_manufacturer] = $manufacturerNameForDropdown;
            }
        }
        $manufacturersForDropdown = [];
        if (! empty($onlineManufacturers)) {
            $manufacturersForDropdown['online'] = $onlineManufacturers;
        }
        if (! empty($offlineManufacturers)) {
            $manufacturersForDropdown['offline'] = $offlineManufacturers;
        }

        return $manufacturersForDropdown;
    }

    public function getProductsByManufacturerId($manufacturerId)
    {
        $sql = "SELECT ";
        $sql .= $this->getFieldsForProductListQuery();
        $sql .= "FROM ".$this->tablePrefix."product Products ";
        $sql .= $this->getJoinsForProductListQuery();
        $sql .= $this->getConditionsForProductListQuery();
        $sql .= "AND Manufacturers.id_manufacturer = :manufacturerId";
        $sql .= $this->getOrdersForProductListQuery();

        $params = [
            'manufacturerId' => $manufacturerId,
            'active' => APP_ON,
            'shopId' => Configure::read('app.shopId')
        ];
        if (! $this->user()) {
            $params['isPrivate'] = APP_OFF;
        }

        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $products = $statement->fetchAll('assoc');
        
        return $products;
    }

    /**
     * turns eg 24 into 0024
     *
     * @param int $invoiceNumber
     */
    public function formatInvoiceNumber($invoiceNumber)
    {
        return str_pad($invoiceNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getOrderList($manufacturerId, $order, $from, $to, $orderState)
    {
        switch ($order) {
            case 'product':
                $orderClause = 'od.product_name ASC, t.rate ASC, ' . Configure::read('app.htmlHelper')->getCustomerNameForSql() . ' ASC';
                break;
            case 'customer':
                $orderClause = Configure::read('app.htmlHelper')->getCustomerNameForSql() . ' ASC, od.product_name ASC';
                break;
        }

        $customerNameAsSql = Configure::read('app.htmlHelper')->getCustomerNameForSql();

        $sql = "SELECT
        m.id_manufacturer HerstellerID,
        m.name AS Hersteller,
        m.uid_number as UID, m.additional_text_for_invoice as Zusatztext,
        ma.*,
        t.rate as Steuersatz,
        odt.total_amount AS MWSt,
        od.product_id AS ProduktID,
        od.product_name AS ProduktName,
        od.product_quantity AS Menge,
        od.total_price_tax_incl AS PreisIncl,
        od.total_price_tax_excl as PreisExcl,
        DATE_FORMAT (o.date_add, '%d.%m.%Y') as Bestelldatum,
        pl.description_short as Produktbeschreibung,
        c.id_customer AS Kundennummer,
        {$customerNameAsSql} AS Kunde
        FROM ".$this->tablePrefix."order_detail od
                LEFT JOIN ".$this->tablePrefix."product p ON p.id_product = od.product_id
                LEFT JOIN ".$this->tablePrefix."orders o ON o.id_order = od.id_order
                LEFT JOIN ".$this->tablePrefix."order_detail_tax odt ON odt.id_order_detail = od.id_order_detail
                LEFT JOIN ".$this->tablePrefix."product_lang pl ON p.id_product = pl.id_product
                LEFT JOIN ".$this->tablePrefix."customer c ON c.id_customer = o.id_customer
                LEFT JOIN ".$this->tablePrefix."manufacturer m ON m.id_manufacturer = p.id_manufacturer
                LEFT JOIN ".$this->tablePrefix."address ma ON m.id_manufacturer = ma.id_manufacturer
                LEFT JOIN ".$this->tablePrefix."tax t ON od.id_tax = t.id_tax
                WHERE 1
                AND m.id_manufacturer = :manufacturerId
                AND DATE_FORMAT(o.date_add, '%Y-%m-%d') >= :dateFrom
                AND DATE_FORMAT(o.date_add, '%Y-%m-%d') <= :dateTo
                AND pl.id_lang = 1
                AND ma.id_manufacturer > 0
                AND o.current_state IN(:orderStates)
                ORDER BY {$orderClause}, DATE_FORMAT (o.date_add, '%d.%m.%Y, %H:%i') DESC;";

        $params = [
            'manufacturerId' => $manufacturerId,
            'dateFrom' => "'" . Configure::read('app.timeHelper')->formatToDbFormatDate($from) . "'",
            'dateTo' => "'" . Configure::read('app.timeHelper')->formatToDbFormatDate($to) . "'",
            'orderStates' => join(',', $orderState)
        ];
        // strange behavior: if $this->getDataSource()->fetchAll is used, $results is empty
        // problem seems to be caused by date fields
        // with interpolateQuery and normal fire of sql statemt, result is not empty and works...
        $replacedQuery = $this->interpolateQuery($sql, $params);
        $results = $this->getConnection()->query($replacedQuery);
        return $results;
    }
}

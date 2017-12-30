<?php

App::uses('AppModel', 'Model');

/**
 * Manufacturer
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
class Manufacturer extends AppModel
{

    public $useTable = 'manufacturer';

    public $primaryKey = 'id_manufacturer';

    public $actsAs = array(
        'Content'
    );

    public $belongsTo = array(
        'Customer' => array(
            'foreignKey' => 'id_customer'
        )
    );

    public $hasOne = array(
        'Address' => array(
            'className' => 'AddressManufacturer',
            'conditions' => array(
                'Address.id_manufacturer > ' . APP_OFF
            ),
            'foreignKey' => 'id_manufacturer'
        ),
        'ManufacturerLang' => array(
            'foreignKey' => 'id_manufacturer'
        )
    );

    public $hasMany = array(
        'Invoices' => array(
            'className' => 'Invoice',
            'foreignKey' => 'id_manufacturer',
            'order' => array(
                'Invoices.send_date DESC'
            ),
            'limit' => 1
        )
    );

    public $validate = array(
        'name' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte gib einen Namen an.'
            ),
            'minLength' => array(
                'rule' => array(
                    'between',
                    3,
                    64
                ), // 64 is set in db
                'message' => 'Bitte gib zwischen 3 und 64 Zeichen ein.'
            )
        ),
        'iban' => array(
            'regex' => array(
                'rule' => array(
                    'phone',
                    IBAN_REGEX
                ), // phone takes regex
                'allowEmpty' => true,
                'message' => 'Bitte gib einen gültigen IBAN ein.'
            )
        ),
        'bic' => array(
            'regex' => array(
                'rule' => array(
                    'phone',
                    BIC_REGEX
                ), // phone takes regex
                'allowEmpty' => true,
                'message' => 'Bitte gib einen gültigen BIC ein.'
            )
        ),
        'homepage' => array(
            'allowEmpty' => true,
            'rule' => array(
                'url',
                true
            ),
            'message' => 'Bitte gibt eine gültige Internet-Adresse an.'
        )
    );

    /**
     * @param $boolean $sendOrderedProductDeletedNotification
     * @return boolean
     */
    public function getOptionSendOrderedProductDeletedNotification($sendOrderedProductDeletedNotification)
    {
        $result = $sendOrderedProductDeletedNotification;
        if ($sendOrderedProductDeletedNotification == '') {
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
        if ($sendOrderedProductPriceChangedNotification == '') {
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
        if ($sendOrderedProductQuantityChangedNotification == '') {
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
        if ($sendShopOrderNotification == '') {
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
        if ($sendInvoice == '') {
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
        if ($bulkOrdersAllowed == '') {
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
        if ($defaultTaxId == '') {
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
        if ($variableMemberFee == '') {
            $result = Configure::read('app.db_config_FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE');
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
        if ($sendOrderList == '') {
            $result = Configure::read('app.defaultSendOrderList');
        }
        return (boolean) $result;
    }

    /**
     * @param $string $sendOrderListCc
     * @return array
     */
    public function getOptionSendOrderListCc($sendOrderListCc)
    {
        $ccRecipients = array();
        if ($sendOrderListCc == '') {
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
        $cm = ClassRegistry::init('Customer');

        $cm->recursive = 1;
        $customer = $cm->find('first', array(
            'conditions' => array(
                'Customer.email' => $manufacturer['Address']['email']
            )
        ));

        if (empty($customer['AddressCustomer']['id_address'])) {
            return $customer;
        }

        if (!empty($customer['AddressCustomer'])) {
            return array();
        }

        return $customer;
    }

    /**
     * @param int $manufacturerId
     * @return array
     */
    public function getCustomerByManufacturerId($manufacturerId)
    {
        $manufacturer = $this->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        if (!empty($manufacturer)) {
            return $this->getCustomerRecord($manufacturer);
        }
        return false;
    }

    public function getCustomerIdByManufacturerId($manufacturerId)
    {
        $customer = $this->getCustomerByManufacturerId($manufacturerId);
        if (!empty($customer)) {
            return $$customer['Customer']['id_customer'];
        }
        return 0;
    }

    public function hasCustomerRecord($manufacturer)
    {
        return (boolean) count($this->getCustomerRecord($manufacturer));
    }

    public function getForMenu($appAuth)
    {
        if ($appAuth->loggedIn() || Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            $productModel = ClassRegistry::init('Product');
        }
        $this->recursive = - 1;
        $conditions = array(
            'Manufacturer.active' => APP_ON
        );
        if (! $this->loggedIn()) {
            $conditions['Manufacturer.is_private'] = APP_OFF;
        }

        $manufacturers = $this->find('all', array(
            'fields' => array(
                'Manufacturer.id_manufacturer',
                'Manufacturer.name',
                'Manufacturer.holiday_from',
                'Manufacturer.holiday_to',
                '!'.$this->getManufacturerHolidayConditions().' as IsHolidayActive'
            ),
            'order' => array(
                'Manufacturer.name' => 'ASC'
            ),
            'conditions' => $conditions
        ));

        $manufacturersForMenu = array();
        foreach ($manufacturers as $manufacturer) {
            $manufacturerName = $manufacturer['Manufacturer']['name'];
            $additionalInfo = '';
            if ($appAuth->loggedIn() || Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                $additionalInfo = $productModel->getCountByManufacturerId($manufacturer['Manufacturer']['id_manufacturer']);
            }
            $holidayInfo = Configure::read('htmlHelper')->getManufacturerHolidayString($manufacturer['Manufacturer']['holiday_from'], $manufacturer['Manufacturer']['holiday_to'], $manufacturer[0]['IsHolidayActive']);
            if ($holidayInfo != '') {
                $holidayInfo = 'Lieferpause ' . $holidayInfo;
                if ($manufacturer[0]['IsHolidayActive']) {
                    $additionalInfo = $holidayInfo;
                } else {
                    if ($appAuth->loggedIn() || Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                        $additionalInfo .= ' - ';
                    }
                    $additionalInfo .= $holidayInfo;
                }
            }
            if ($additionalInfo != '') {
                $manufacturerName .= ' <span class="additional-info">('.$additionalInfo.')</span>';
            }
            $manufacturersForMenu[] = array(
                'name' => $manufacturerName,
                'slug' => Configure::read('slugHelper')->getManufacturerDetail($manufacturer['Manufacturer']['id_manufacturer'], $manufacturer['Manufacturer']['name'])
            );
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
        $this->recursive = - 1;
        $manufacturers = $this->find('all', array(
            'fields' => array(
                'Manufacturer.id_manufacturer',
                'Manufacturer.name',
                'Manufacturer.active'
            ),
            'order' => array(
                'Manufacturer.name' => 'ASC'
            )
        ));

        $offlineManufacturers = array();
        $onlineManufacturers = array();
        foreach ($manufacturers as $manufacturer) {
            $manufacturerNameForDropdown = $manufacturer['Manufacturer']['name'];
            if ($manufacturer['Manufacturer']['active'] == 0) {
                $offlineManufacturers[$manufacturer['Manufacturer']['id_manufacturer']] = $manufacturerNameForDropdown;
            } else {
                $onlineManufacturers[$manufacturer['Manufacturer']['id_manufacturer']] = $manufacturerNameForDropdown;
            }
        }
        $manufacturersForDropdown = array();
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
        $sql .= "FROM ".$this->tablePrefix."product Product ";
        $sql .= $this->getJoinsForProductListQuery();
        $sql .= $this->getConditionsForProductListQuery();
        $sql .= "AND Manufacturer.id_manufacturer = :manufacturerId";
        $sql .= $this->getOrdersForProductListQuery();

        $params = array(
            'manufacturerId' => $manufacturerId,
            'active' => APP_ON,
            'langId' => Configure::read('app.langId'),
            'shopId' => Configure::read('app.shopId')
        );
        if (! $this->loggedIn()) {
            $params['isPrivate'] = APP_OFF;
        }

        $products = $this->getDataSource()->fetchAll($sql, $params);

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
                $orderClause = 'od.product_name ASC, t.rate ASC, ' . Configure::read('htmlHelper')->getCustomerNameForSql() . ' ASC';
                break;
            case 'customer':
                $orderClause = Configure::read('htmlHelper')->getCustomerNameForSql() . ' ASC, od.product_name ASC';
                break;
        }

        $customerNameAsSql = Configure::read('htmlHelper')->getCustomerNameForSql();

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

        $params = array(
            'manufacturerId' => $manufacturerId,
            'dateFrom' => "'" . Configure::read('timeHelper')->formatToDbFormatDate($from) . "'",
            'dateTo' => "'" . Configure::read('timeHelper')->formatToDbFormatDate($to) . "'",
            'orderStates' => join(',', $orderState)
        );
        // strange behavior: if $this->getDataSource()->fetchAll is used, $results is empty
        // problem seems to be caused by date fields
        // with interpolateQuery and normal fire of sql statemt, result is not empty and works...
        $replacedQuery = $this->interpolateQuery($sql, $params);
        $results = $this->query($replacedQuery);
        return $results;
    }
}

<?php

namespace App\Model\Table;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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
    }
    
    public $primaryKey = 'id_manufacturer';

    public $actsAs = [
        'Content'
    ];

    public $belongsTo = [
        'Customers' => [
            'foreignKey' => 'id_customer'
        ]
    ];

    public $hasOne = [
        'Addresses' => [
            'className' => 'AddressManufacturer',
            'conditions' => [
                'Addresses.id_manufacturer > 0'
            ],
            'foreignKey' => 'id_manufacturer'
        ]
    ];

    public $hasMany = [
        'Invoices' => [
            'className' => 'Invoices',
            'foreignKey' => 'id_manufacturer',
            'order' => [
                'Invoices.send_date DESC'
            ],
            'limit' => 1
        ]
    ];

    public $validate = [
        'name' => [
            'notBlank' => [
                'rule' => [
                    'notBlank'
                ],
                'message' => 'Bitte gib einen Namen an.'
            ],
            'minLength' => [
                'rule' => [
                    'between',
                    3,
                    64
                ], // 64 is set in db
                'message' => 'Bitte gib zwischen 3 und 64 Zeichen ein.'
            ]
        ],
        'iban' => [
            'regex' => [
                'rule' => [
                    'phone',
                    IBAN_REGEX
                ], // phone takes regex
                'allowEmpty' => true,
                'message' => 'Bitte gib einen gültigen IBAN ein.'
            ]
        ],
        'bic' => [
            'regex' => [
                'rule' => [
                    'phone',
                    BIC_REGEX
                ], // phone takes regex
                'allowEmpty' => true,
                'message' => 'Bitte gib einen gültigen BIC ein.'
            ]
        ],
        'homepage' => [
            'allowEmpty' => true,
            'rule' => [
                'url',
                true
            ],
            'message' => 'Bitte gibt eine gültige Internet-Adresse an.'
        ]
    ];

    /**
     * @param $boolean $sendOrderedProductDeletedNotification
     * @return boolean
     */
    public function getOptionSendOrderedProductDeletedNotification($sendOrderedProductDeletedNotification)
    {
        $result = $sendOrderedProductDeletedNotification;
        if ($sendOrderedProductDeletedNotification == '') {
            $result = Configure::read('AppConfig.defaultSendOrderedProductDeletedNotification');
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
            $result = Configure::read('AppConfig.defaultSendOrderedProductPriceChangedNotification');
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
            $result = Configure::read('AppConfig.defaultSendOrderedProductQuantityChangedNotification');
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
            $result = Configure::read('AppConfig.defaultSendShopOrderNotification');
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
            $result = Configure::read('AppConfig.defaultSendInvoice');
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
            $result = Configure::read('AppConfig.defaultBulkOrdersAllowed');
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
            $result = Configure::read('AppConfig.defaultTaxId');
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
            $result = Configure::read('AppConfigDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE');
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
            $result = Configure::read('AppConfig.defaultSendOrderList');
        }
        return (boolean) $result;
    }

    /**
     * @param $string $sendOrderListCc
     * @return array
     */
    public function getOptionSendOrderListCc($sendOrderListCc)
    {
        $ccRecipients = [];
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
        $cm = TableRegistry::get('Customers');

        $customer = $cm->find('all', [
            'conditions' => [
                'Customers.email' => $manufacturer['Addresses']['email']
            ]
        ])->first();

        if (empty($customer['AddressCustomer']['id_address'])) {
            return $customer;
        }

        if (!empty($customer['AddressCustomer'])) {
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
        return [];
        
        if ($appAuth->user() || Configure::read('AppConfigDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
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
                '!'.$this->getManufacturerHolidayConditions().' as IsHolidayActive'
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'conditions' => $conditions
        ]);
        
        $manufacturersForMenu = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerName = $manufacturer['Manufacturers']['name'];
            $additionalInfo = '';
            if ($appAuth->user() || Configure::read('AppConfigDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                $additionalInfo = $productModel->getCountByManufacturerId($manufacturer['Manufacturers']['id_manufacturer']);
            }
            $holidayInfo = Configure::read('AppConfig.htmlHelper')->getManufacturerHolidayString($manufacturer['Manufacturers']['holiday_from'], $manufacturer['Manufacturers']['holiday_to'], $manufacturer[0]['IsHolidayActive']);
            if ($holidayInfo != '') {
                $holidayInfo = 'Lieferpause ' . $holidayInfo;
                if ($manufacturer[0]['IsHolidayActive']) {
                    $additionalInfo = $holidayInfo;
                } else {
                    if ($appAuth->user() || Configure::read('AppConfigDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
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
                'slug' => Configure::read('AppConfig.slugHelper')->getManufacturerDetail($manufacturer['Manufacturers']['id_manufacturer'], $manufacturer['Manufacturers']['name'])
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
            'fields' => [
                'Manufacturers.id_manufacturer',
                'Manufacturers.name',
                'Manufacturers.active'
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ]);

        $offlineManufacturers = [];
        $onlineManufacturers = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerNameForDropdown = $manufacturer['Manufacturers']['name'];
            if ($manufacturer['Manufacturers']['active'] == 0) {
                $offlineManufacturers[$manufacturer['Manufacturers']['id_manufacturer']] = $manufacturerNameForDropdown;
            } else {
                $onlineManufacturers[$manufacturer['Manufacturers']['id_manufacturer']] = $manufacturerNameForDropdown;
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
            'langId' => Configure::read('AppConfig.langId'),
            'shopId' => Configure::read('AppConfig.shopId')
        ];
        if (! $this->user()) {
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
                $orderClause = 'od.product_name ASC, t.rate ASC, ' . Configure::read('AppConfig.htmlHelper')->getCustomerNameForSql() . ' ASC';
                break;
            case 'customer':
                $orderClause = Configure::read('AppConfig.htmlHelper')->getCustomerNameForSql() . ' ASC, od.product_name ASC';
                break;
        }

        $customerNameAsSql = Configure::read('AppConfig.htmlHelper')->getCustomerNameForSql();

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
            'dateFrom' => "'" . Configure::read('AppConfig.timeHelper')->formatToDbFormatDate($from) . "'",
            'dateTo' => "'" . Configure::read('AppConfig.timeHelper')->formatToDbFormatDate($to) . "'",
            'orderStates' => join(',', $orderState)
        ];
        // strange behavior: if $this->getDataSource()->fetchAll is used, $results is empty
        // problem seems to be caused by date fields
        // with interpolateQuery and normal fire of sql statemt, result is not empty and works...
        $replacedQuery = $this->interpolateQuery($sql, $params);
        $results = $this->query($replacedQuery);
        return $results;
    }
}

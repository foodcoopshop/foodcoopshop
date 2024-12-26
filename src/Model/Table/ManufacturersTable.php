<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use App\Model\Traits\MultipleEmailsRuleTrait;
use App\Model\Traits\NoDeliveryDaysOrdersExistTrait;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;

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
 */

class ManufacturersTable extends AppTable
{

    use MultipleEmailsRuleTrait;
    use NoDeliveryDaysOrdersExistTrait;
    use ProductCacheClearAfterSaveAndDeleteTrait;

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

        return $validator;
    }

    public function getManufacturerByIdForSendingOrderListsOrInvoice($manufacturerId)
    {
        $manufacturer = $this->find('all',
        conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId,
        ],
        order: [
            'Manufacturers.name' => 'ASC',
        ],
        contain: [
            'AddressManufacturers',
            'Customers.AddressCustomers',
        ])->first();
        return $manufacturer;
    }

    public function getOptionSendOrderedProductDeletedNotification($sendOrderedProductDeletedNotification)
    {
        $result = $sendOrderedProductDeletedNotification;
        if (is_null($sendOrderedProductDeletedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductDeletedNotification');
        }
        return (bool) $result;
    }

    public function getOptionSendOrderedProductPriceChangedNotification($sendOrderedProductPriceChangedNotification)
    {
        $result = $sendOrderedProductPriceChangedNotification;
        if (is_null($sendOrderedProductPriceChangedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductPriceChangedNotification');
        }
        return (bool) $result;
    }

    public function getOptionSendOrderedProductAmountChangedNotification($sendOrderedProductAmountChangedNotification)
    {
        $result = $sendOrderedProductAmountChangedNotification;
        if (is_null($sendOrderedProductAmountChangedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductAmountChangedNotification');
        }
        return (bool) $result;
    }

    public function getOptionSendInstantOrderNotification($sendInstantOrderNotification)
    {
        $result = $sendInstantOrderNotification;
        if (is_null($sendInstantOrderNotification)) {
            $result = Configure::read('app.defaultSendInstantOrderNotification');
        }
        return (bool) $result;
    }

    public function getOptionSendInvoice($sendInvoice)
    {
        $result = $sendInvoice;
        if (is_null($sendInvoice)) {
            $result = Configure::read('app.defaultSendInvoice');
        }
        return (bool) $result;
    }

    public function getOptionDefaultTaxId(?int $defaultTaxId): int
    {
        $result = $defaultTaxId;
        if (is_null($defaultTaxId)) {
            $result = (int) Configure::read('app.defaultTaxId');
        }
        return $result;
    }

    public function getOptionDefaultTaxIdPurchasePrice(?int $defaultTaxIdPurchasePrice): int
    {
        $result = $defaultTaxIdPurchasePrice;
        if (is_null($defaultTaxIdPurchasePrice)) {
            $result = (int) Configure::read('app.defaultTaxIdPurchasePrice');
        }
        return $result;
    }

    public function getOptionVariableMemberFee(?int $variableMemberFee): int
    {
        $result = $variableMemberFee;
        if (is_null($variableMemberFee)) {
            $result = (int) Configure::read('appDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE');
        }
        return $result;
    }

    public function getOptionSendOrderList($sendOrderList)
    {
        $result = $sendOrderList;
        if (is_null($sendOrderList)) {
            $result = Configure::read('app.defaultSendOrderList');
        }
        return $result;
    }

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

    public function getCustomerRecord($email)
    {
        $customersTable = TableRegistry::getTableLocator()->get('Customers');

        if (empty($email)) {
            return [];
        }

        $customer = $customersTable->find('all',
            conditions: [
                'Customers.email' => $email,
            ]
        )->first();

        if (empty($customer->address_customer->id_address)) {
            return $customer;
        }

        if (!empty($customer->address_customer)) {
            return [];
        }

        return $customer;
    }

    public function getForMenu()
    {

        $conditions = [
            'Manufacturers.active' => APP_ON
        ];
        $identity = Router::getRequest()->getAttribute('identity');
        if ($identity === null) {
            $conditions['Manufacturers.is_private'] = APP_OFF;
        }

        $manufacturers = $this->find('all',
        fields: [
            'Manufacturers.id_manufacturer',
            'Manufacturers.name',
            'Manufacturers.no_delivery_days'
        ],
        order: [
            'Manufacturers.name' => 'ASC'
        ],
        conditions: $conditions);

        $manufacturersForMenu = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerName = $manufacturer->name;
            $additionalInfo = '';
            if ($identity !== null || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
            }
            $noDeliveryDaysString = Configure::read('app.htmlHelper')->getManufacturerNoDeliveryDaysString($manufacturer, false, 1);
            if ($noDeliveryDaysString != '') {
                $noDeliveryDaysString = __('Delivery_break') . ': ' . $noDeliveryDaysString;
                if ($identity !== null || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
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

    public function increasePriceWithVariableMemberFee($price, $variableMemberFee)
    {
        return $price + $this->getVariableMemberFeeAsFloat($price, $variableMemberFee);
    }

    public function decreasePriceWithVariableMemberFee($price, $variableMemberFee)
    {
        return $price - $this->getVariableMemberFeeAsFloat($price, $variableMemberFee);
    }

    public function getVariableMemberFeeAsFloat($price, $variableMemberFee)
    {
        return round($price * $variableMemberFee / 100, 2);
    }

    public function getForDropdown()
    {
        $manufacturers = $this->find('all', order: [
            'Manufacturers.name' => 'ASC'
        ]);

        $offlineManufacturers = [];
        $onlineManufacturers = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerNameForDropdown = $manufacturer->decoded_name;
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

    public function anonymizeCustomersInInvoiceOrOrderList($results)
    {
        return array_map(function ($data) {
            $data['CustomerName'] = Configure::read('app.htmlHelper')->anonymizeCustomerName($data['CustomerName'], (int) $data['CustomerId']);
            return $data;
        }, $results);
    }

    public function getDataForInvoiceOrOrderList($manufacturerId, $order, $dateFrom, $dateTo, $orderState, $includeStockProducts, $orderDetailIds = [])
    {
        $customersTable = TableRegistry::getTableLocator()->get('Customers');
        $orderClause = match($order) {
            'product' => 'od.product_name ASC, od.tax_rate ASC, ' . $customersTable->getCustomerName('c') . ' ASC',
            'customer' => $customersTable->getCustomerName('c') . ' ASC, od.product_name ASC',
            default => '',
        };
        $params = [
            'manufacturerId' => $manufacturerId
        ];

        if (is_null($dateTo)) {
            // order list
            // do not use params for $orderState, it will result in IN ('3,2,1') which is wrong
            $orderDetailCondition = "AND od.id_order_detail IN (" . join(',', $orderDetailIds) . ")" ;
            $dateConditions = "";
        } else {
            // invoice
            $dateConditions  = "AND DATE_FORMAT(od.pickup_day, '%Y-%m-%d') >= :dateFrom ";
            $dateConditions .= "AND DATE_FORMAT(od.pickup_day, '%Y-%m-%d') <= :dateTo" ;
            $params['dateFrom'] = Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom);
            $params['dateTo'] = Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo);
            $orderDetailCondition = "";
        }

        $includeStockProductCondition = '';
        if (!$includeStockProducts) {
            $includeStockProductCondition = "AND (p.is_stock_product = 0 OR m.stock_management_enabled = 0)";
        }

        $orderStateCondition = "";
        if (!empty($orderState)) {
            // do not use params for $orderState, it will result in IN ('3,2,1') which is wrong
            $orderStateCondition = "AND od.order_state IN (" . join(',', $orderState) . ")";
        }

        $customerNameAsSql = $customersTable->getCustomerName('c');

        $sql = "SELECT
        m.id_manufacturer ManufacturerId,
        m.name AS ManufacturerName,
        m.uid_number as ManufacturerUidNumber,
        m.additional_text_for_invoice as ManufacturerAdditionalTextForInvoice,
        ma.firstname as ManufacturerFirstname, ma.lastname as ManufacturerLastname, ma.address1 as ManufacturerAddress1, ma.postcode as ManufacturerPostcode, ma.city as ManufacturerCity,
        od.tax_rate as TaxRate,
        od.tax_total_amount as OrderDetailTaxAmount,
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
            LEFT JOIN ".$this->tablePrefix."order_detail_units odu ON od.id_order_detail = odu.id_order_detail
            LEFT JOIN ".$this->tablePrefix."customer c ON c.id_customer = od.id_customer
            LEFT JOIN ".$this->tablePrefix."manufacturer m ON m.id_manufacturer = p.id_manufacturer
            LEFT JOIN ".$this->tablePrefix."address ma ON m.id_manufacturer = ma.id_manufacturer
            WHERE 1
            {$dateConditions}
            AND m.id_manufacturer = :manufacturerId
            AND ma.id_manufacturer > 0
            {$orderStateCondition}
            {$includeStockProductCondition}
            {$orderDetailCondition}
            ORDER BY {$orderClause}, DATE_FORMAT(od.created, '%d.%m.%Y, %H:%i') DESC;";

        $statement = $this->getConnection()->getDriver()->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetchAll('assoc');
        return $result;
    }
}

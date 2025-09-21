<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use App\Model\Entity\Manufacturer;
use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use App\Model\Traits\MultipleEmailsRuleTrait;
use App\Model\Traits\NoDeliveryDaysOrdersExistTrait;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;
use App\Model\Entity\Customer;
use Cake\Event\EventInterface;
use ArrayObject;
use App\Services\FormatterService;

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

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        if (isset($data['send_order_list_cc'])) {
            $data['send_order_list_cc'] = StringComponent::removeWhitespace($data['send_order_list_cc']);
        }
        if (isset($data['min_order_value'] ) && $data['min_order_value'] == '') {
            $data['min_order_value'] = 0;
        }
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

    public function validationEditOptions(Validator $validator): Validator
    {
        $validator->allowEmptyString('send_order_list_cc');
        $validator->add('send_order_list_cc', 'multipleEmails', [
            'rule' => 'ruleMultipleEmails',
            'provider' => 'table',
            'message' => __('At_least_one_email_is_not_valid._Please_separate_multiple_with_comma.')
        ]);

        $validator->allowEmptyString('no_delivery_days');
        $validator->add('no_delivery_days', 'noDeliveryDaysOrdersExist', [
            'provider' => 'table',
            'rule' => 'noDeliveryDaysOrdersExist'
        ]);
        $validator->allowEmptyString('min_order_value');
        $validator->range('min_order_value', [-1, 501], __('Please_enter_a_number_between_{0}_and_{1}.', [0,500]));
        return $validator;
    }

    public function getManufacturerByIdForSendingOrderListsOrInvoice(int $manufacturerId): Manufacturer
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

    /**
     * TODO add type bool to all getOption methods, in this step remove the fallbacks (and set the values in the database before)
     */
    /** @phpstan-ignore-next-line */
    public function getOptionSendOrderedProductDeletedNotification($sendOrderedProductDeletedNotification): bool
    {
        $result = $sendOrderedProductDeletedNotification;
        if (is_null($sendOrderedProductDeletedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductDeletedNotification');
        }
        return (bool) $result;
    }

    /** @phpstan-ignore-next-line */
    public function getOptionSendOrderedProductPriceChangedNotification($sendOrderedProductPriceChangedNotification): bool
    {
        $result = $sendOrderedProductPriceChangedNotification;
        if (is_null($sendOrderedProductPriceChangedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductPriceChangedNotification');
        }
        return (bool) $result;
    }

    /** @phpstan-ignore-next-line */
    public function getOptionSendOrderedProductAmountChangedNotification($sendOrderedProductAmountChangedNotification): bool
    {
        $result = $sendOrderedProductAmountChangedNotification;
        if (is_null($sendOrderedProductAmountChangedNotification)) {
            $result = Configure::read('app.defaultSendOrderedProductAmountChangedNotification');
        }
        return (bool) $result;
    }

    /** @phpstan-ignore-next-line */
    public function getOptionSendInstantOrderNotification($sendInstantOrderNotification): bool
    {
        $result = $sendInstantOrderNotification;
        if (is_null($sendInstantOrderNotification)) {
            $result = Configure::read('app.defaultSendInstantOrderNotification');
        }
        return (bool) $result;
    }

    /** @phpstan-ignore-next-line */
    public function getOptionSendInvoice($sendInvoice): bool
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

    /** @phpstan-ignore-next-line */
    public function getOptionSendOrderList( $sendOrderList): bool
    {
        $result = $sendOrderList;
        if (is_null($sendOrderList)) {
            $result = Configure::read('app.defaultSendOrderList');
        }
        return (bool) $result;
    }

    /**
     * @return list<string>
     */
    public function getOptionSendOrderListCc(?string $sendOrderListCc): array
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
     * @return Customer|array<never>|null
     */
    public function getCustomerRecord(string $email): Customer|array|null
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

    /**
     * @return list<array{name: string, slug: string}>
     */
    public function getForMenu(): array
    {

        $conditions = [
            $this->aliasField('active') => APP_ON
        ];
        $identity = Router::getRequest()->getAttribute('identity');
        if ($identity === null) {
            $conditions[$this->aliasField('is_private')] = APP_OFF;
        }

        $manufacturers = $this->find('all',
            fields: [
                $this->aliasField('id_manufacturer'),
                $this->aliasField('name'),
                $this->aliasField('no_delivery_days'),
            ],
            order: [
                $this->aliasField('name') => 'ASC'
            ],
            conditions: $conditions,
        );

        $manufacturersForMenu = [];
        foreach ($manufacturers as $manufacturer) {
            $manufacturerName = $manufacturer->name;
            $additionalInfo = '';
            $noDeliveryDaysString = Configure::read('app.htmlHelper')->getManufacturerNoDeliveryDaysString($manufacturer, false, 1);
            if ($noDeliveryDaysString != '') {
                $noDeliveryDaysString = __('Delivery_break') . ': ' . $noDeliveryDaysString;
                $additionalInfo .= $noDeliveryDaysString;
            }
            if ($additionalInfo != '') {
                $manufacturerName .= ' <span class="additional-info">- ('.$additionalInfo.')</span>';
            }
            $manufacturersForMenu[] = [
                'name' => $manufacturerName,
                'slug' => Configure::read('app.slugHelper')->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name)
            ];
        }
        return $manufacturersForMenu;
    }

    public function increasePriceWithVariableMemberFee(float $price, int $variableMemberFee): float
    {
        return $price + $this->getVariableMemberFeeAsFloat($price, $variableMemberFee);
    }

    public function decreasePriceWithVariableMemberFee(float $price, int $variableMemberFee): float
    {
        return $price - $this->getVariableMemberFeeAsFloat($price, $variableMemberFee);
    }

    public function getVariableMemberFeeAsFloat(float $price, int $variableMemberFee): float
    {
        return round($price * $variableMemberFee / 100, 2);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getForDropdown(): array
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

    /**
     * @param array<int, mixed> $results
     */
    /**
     * @param list<array<string, mixed>> $results
     * @return list<array<string, mixed>>
     */
    public function anonymizeCustomersInInvoiceOrOrderList(array $results): array
    {
        return array_map(function ($data) {
            $data['CustomerName'] = Configure::read('app.htmlHelper')->anonymizeCustomerName($data['CustomerName'], (int) $data['CustomerId']);
            return $data;
        }, $results);
    }

    public function getDepositBalance(int $manufacturerId): float
    {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');
        $sumDepositReturned = $paymentsTable->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $sumDepositDelivered = $orderDetailsTable->getDepositSum($manufacturerId, false);

        $depositBalance = $sumDepositReturned[0]['sumDepositReturned'] - $sumDepositDelivered[0]['sumDepositDelivered'];
        return FormatterService::assureCorrectFloat($depositBalance);
    }

    /**
     * @param list<int> $orderStates
     * @param list<int> $orderDetailIds
     */
    /**
     * @param list<int> $orderStates
     * @param list<int> $orderDetailIds
     * @return list<array<string, mixed>>
     */
    public function getDataForInvoiceOrOrderList(
        int $manufacturerId,
        string $order,
        string $dateFrom,
        ?string $dateTo,
        array $orderStates,
        bool $includeStockProducts,
        array $orderDetailIds = [],
        ): array
    {
        $customersTable = TableRegistry::getTableLocator()->get('Customers');
        $orderClause = match($order) {
            'product' => 'od.product_name ASC, od.tax_rate ASC, ' . $customersTable->getCustomerName('c') . ' ASC',
            'customer' => $customersTable->getCustomerName('c') . ' ASC, od.product_name ASC',
            default => '',
        };
        $params = [
            'manufacturerId' => $manufacturerId,
        ];

        if (is_null($dateTo)) {
            // order list
            // do not use params for $orderStates, it will result in IN ('3,2,1') which is wrong
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

        $orderStatesCondition = "";
        if (!empty($orderStates)) {
            // do not use params for $orderStates, it will result in IN ('3,2,1') which is wrong
            $orderStatesCondition = "AND od.order_state IN (" . join(',', $orderStates) . ")";
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
            {$orderStatesCondition}
            {$includeStockProductCondition}
            {$orderDetailCondition}
            ORDER BY {$orderClause}, DATE_FORMAT(od.created, '%d.%m.%Y, %H:%i') DESC;";

        $statement = $this->getConnection()->getDriver()->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetchAll('assoc');
        return $result;
    }
}

<?php

namespace App\Model\Table;

use App\ORM\AppMarshaller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Marshaller;
use Cake\ORM\Table;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Cake\I18n\FrozenDate;

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
class AppTable extends Table
{

    public $tablePrefix = 'fcs_';

    public function initialize(array $config): void
    {
        $this->setTable($this->tablePrefix . $this->getTable());
        if ((php_sapi_name() == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]))) {
            $this->setConnection(ConnectionManager::get('test'));
        }
        parent::initialize($config);
    }

    public function getAllowOnlyOneWeekdayValidator(Validator $validator, $field, $fieldName)
    {
        $validator->add($field, 'allow-only-one-weekday', [
            'rule' => function ($value, $context) {
            if (Configure::read('app.timeHelper')->getDeliveryWeekday() != Configure::read('app.timeHelper')->formatAsWeekday(strtotime($value))) {
                return false;
            }
            return true;
            },
            'message' => __('{0}_needs_to_be_a_{1}.', [
                $fieldName,
                Configure::read('app.timeHelper')->getWeekdayName(Configure::read('app.timeHelper')->getDeliveryWeekday())
            ])
        ]);
        return $validator;
    }

    public function clearZeroArray($array)
    {
        foreach($array as $key => $value) {
            if (array_sum($value) == 0) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    public function noDeliveryDaysOrdersExist ($value, $context) {

        $manufacturerId = null;
        if (!empty($context['data']) && !empty($context['data']['id_manufacturer'])) {
            $manufacturerId = $context['data']['id_manufacturer'];
        }

        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');

        if (!is_null($manufacturerId)) {
            $productsAssociation = $orderDetailsTable->getAssociation('Products');
            $productsAssociation->setJoinType('INNER'); // necessary to apply condition
            $productsAssociation->setConditions([
                'Products.id_manufacturer' => $manufacturerId
            ]);
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        $query = $orderDetailsTable->find('all', [
            'conditions' => [
                'pickup_day IN' => $value
            ],
            'group' => 'pickup_day',
            'contain' => [
                'Products'
            ]
        ]);
        $query->select(
            [
                'PickupDayCount' => $query->func()->count('OrderDetails.pickup_day'),
                'pickup_day'
            ]
        );

        $result = true;
        if (!empty($query->toArray())) {
            $pickupDaysInfo = [];
            foreach($query->toArray() as $orderDetail) {
                $formattedPickupDay = $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
                $pickupDaysInfo[] = $formattedPickupDay . ' (' . $orderDetail->PickupDayCount . 'x)';
            }
            $result = __('The_following_delivery_day(s)_already_contain_orders:_{0}._To_save_the_delivery_break_either_cancel_them_or_change_the_pickup_day.', [join(', ', $pickupDaysInfo)]);
        }

        return $result;
    }

    public function getNumberRangeValidator(Validator $validator, $field, $min, $max, $additionalErrorMessageSuffix='', $showDefaultErrorMessage=true)
    {
        $message = __('Please_enter_a_number_between_{0}_and_{1}.', [
            Configure::read('app.numberHelper')->formatAsDecimal($min, 0),
            Configure::read('app.numberHelper')->formatAsDecimal($max, 0)
        ]);
        if ($additionalErrorMessageSuffix != '') {
            if (!$showDefaultErrorMessage) {
                $message = '';
            }
            $message .= ' ' . $additionalErrorMessageSuffix;
        }
        $validator->lessThanOrEqual($field, $max, $message);
        $validator->greaterThanOrEqual($field, $min, $message);
        $validator->notEmptyString($field, $message);
        return $validator;
    }

    public function sortByVirtualField($object, $name)
    {
        $sortedObject = (object) Hash::sort($object->toArray(), '{n}.' . $name, 'ASC');
        return $sortedObject;
    }

    public function getAllValidationErrors($entity)
    {
        $preparedErrors = [];
        foreach($entity->getErrors() as $field => $message) {
            $errors = array_keys($message);
            foreach($errors as $error) {
                $preparedErrors[] = $message[$error];
            }
        }
        return $preparedErrors;
    }

    /**
     * {@inheritDoc}
     * @see \Cake\ORM\Table::marshaller()
     */
    public function marshaller(): Marshaller
    {
        return new AppMarshaller($this);
    }

    public function ruleMultipleEmails($check)
    {
        $emails = explode(',', $check);
        if (!is_array($emails)) {
            $emails = [$emails];
        }
        foreach ($emails as $email) {
            $validates = Validation::email($email, true);
            if (!$validates) {
                return false;
            }
        }
        return true;
    }

    public function getProductIdentifierField()
    {
        return 'SUBSTRING(SHA1(CONCAT(Products.id_product, "' .  Security::getSalt() . '", "product")), 1, 4)';
    }

    /**
     * @return string
     */
    protected function getFieldsForProductListQuery()
    {
        $fields = "Products.id_product,
                Products.name, Products.description_short, Products.description, Products.unity, Products.price, Products.created, Products.is_stock_product,
                Products.delivery_rhythm_type, Products.delivery_rhythm_count, Products.delivery_rhythm_first_delivery_day, Products.delivery_rhythm_order_possible_until,
                Products.delivery_rhythm_send_order_list_weekday, Products.delivery_rhythm_send_order_list_day,
                Deposits.deposit,
                Images.id_image,
                Manufacturers.id_manufacturer, Manufacturers.name as ManufacturersName,
                Manufacturers.timebased_currency_enabled, Manufacturers.no_delivery_days, Manufacturers.stock_management_enabled,
                Units.price_per_unit_enabled, Units.price_incl_per_unit, Units.name as unit_name, Units.amount as unit_amount, Units.quantity_in_units,
                StockAvailables.quantity, StockAvailables.quantity_limit, StockAvailables.always_available";

        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
            $fields .= ", Manufacturers.timebased_currency_max_percentage, Manufacturers.timebased_currency_max_credit_balance";
        }

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $fields .= ", " . $this->getProductIdentifierField() . " as ProductIdentifier";
        }

        $fields .= " ";
        return $fields;
    }

    /**
     * @return string
     */
    protected function getJoinsForProductListQuery()
    {
        return "LEFT JOIN ".$this->tablePrefix."stock_available StockAvailables ON Products.id_product = StockAvailables.id_product
                LEFT JOIN ".$this->tablePrefix."images Images ON Images.id_product = Products.id_product
                LEFT JOIN ".$this->tablePrefix."deposits Deposits ON Products.id_product = Deposits.id_product
                LEFT JOIN ".$this->tablePrefix."units Units ON Products.id_product = Units.id_product
                LEFT JOIN ".$this->tablePrefix."manufacturer Manufacturers ON Manufacturers.id_manufacturer = Products.id_manufacturer ";
    }

    /**
     * @return string
     */
    protected function getConditionsForProductListQuery($appAuth)
    {
        $conditions = "WHERE 1
                    AND StockAvailables.id_product_attribute = 0
                    AND (Units.id_product_attribute = 0 OR Units.id_product_attribute IS NULL)
                    AND Products.active = :active
                    AND Manufacturers.active = :active ";

        if (empty($appAuth->user())) {
            $conditions .= 'AND Manufacturers.is_private = :isPrivate ';
        }

        if (Configure::read('appDb.FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS')) {
            if ($appAuth->isInstantOrderMode()) {
                $conditions .= " AND (Manufacturers.stock_management_enabled = 1 AND Products.is_stock_product = 1) ";
            }
        }

        return $conditions;
    }

    /**
     * @return string
     */
    protected function getOrdersForProductListQuery()
    {
        return " ORDER BY Products.name ASC, Images.id_image DESC;";
    }

    protected function hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($appAuth, $products)
    {

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY') || $appAuth->isInstantOrderMode() || $appAuth->isSelfServiceModeByUrl()) {
            return $products;
        }
        $this->Product = FactoryLocator::get('Table')->get('Products');
        $i = -1;
        foreach($products as $product) {
            $i++;
            $deliveryDate = $this->Product->calculatePickupDayRespectingDeliveryRhythm(
                $this->Product->newEntity(
                    [
                        'delivery_rhythm_first_delivery_day' => $product['delivery_rhythm_first_delivery_day'] == '' ? null : new FrozenDate($product['delivery_rhythm_first_delivery_day']),
                        'delivery_rhythm_type' => $product['delivery_rhythm_type'],
                        'delivery_rhythm_count' => $product['delivery_rhythm_count'],
                        'delivery_rhythm_send_order_list_weekday' => $product['delivery_rhythm_send_order_list_weekday'],
                        'delivery_rhythm_send_order_list_day' => $product['delivery_rhythm_send_order_list_day'],
                        'is_stock_product' => $product['is_stock_product']
                    ]
                )
            );

            // deactivates the product if manufacturer based delivery break is enabled
            if ($this->Product->deliveryBreakEnabled($product['no_delivery_days'], $deliveryDate)) {
                $products[$i]['delivery_break_enabled'] = true;
            }

            // deactivates the product if global delivery break is enabled
            if ($this->Product->deliveryBreakEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $deliveryDate)) {
                $products[$i]['delivery_break_enabled'] = true;
            }

            if ($product['delivery_rhythm_type'] == 'individual') {
                // hides products when order_possible_until is reached
                if ($product['delivery_rhythm_order_possible_until'] < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    unset($products[$i]);
                }
            }
        }
        return $products;
    }
}

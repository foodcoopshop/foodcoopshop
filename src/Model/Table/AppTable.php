<?php

namespace App\Model\Table;

use App\Network\AppSession;
use App\ORM\AppMarshaller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppTable extends Table
{

    public $tablePrefix = 'fcs_'; // legacy from CakePHP2

    public function initialize(array $config)
    {
        $this->setTable($this->tablePrefix . $this->getTable());
        // HttpClient needs special header HTTP_X_UNIT_TEST_MODE => set in AppCakeTestCase::initHttpClient()
        if (isset($_SERVER['HTTP_X_UNIT_TEST_MODE'])
            || (php_sapi_name() == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]))) {
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
        $validator->notEmpty($field, $message);
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
    public function marshaller()
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
            $validates = Validation::email($email);
            if (!$validates) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return boolean | array
     */
    protected function getLoggedUser()
    {
        $session = new AppSession();
        if ($session->read('Auth.User.id_customer') !== null) {
            return $session->read('Auth.User');
        }
        return false;
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
                StockAvailables.quantity, StockAvailables.quantity_limit";

        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
            $fields .= ", Manufacturers.timebased_currency_max_percentage, Manufacturers.timebased_currency_max_credit_balance";
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
    protected function getConditionsForProductListQuery()
    {
        $conditions = "WHERE 1
                    AND StockAvailables.id_product_attribute = 0
                    AND (Units.id_product_attribute = 0 OR Units.id_product_attribute IS NULL)
                    AND Products.active = :active
                    AND Manufacturers.active = :active ";

        if (! $this->getLoggedUser()) {
            $conditions .= 'AND Manufacturers.is_private = :isPrivate ';
        }
        
        if (Configure::read('appDb.FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS')) {
            $session = new AppSession();
            if ($session->check('Auth.instantOrderCustomer')) {
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
    
    protected function hideProductsWithActivatedDeliveryRhythmOrDeliveryBreak($products)
    {
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $i = -1;
        foreach($products as $product) {
            $i++;
            if ($product['is_stock_product']) {
                continue;
            }
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
            
            // hides the product if manufacturer has enabled delivery break
            if ($this->Product->deliveryBreakEnabled($product['no_delivery_days'], $deliveryDate)) {
                unset($products[$i]);
            }
            
            if ($product['delivery_rhythm_type'] == 'individual') {
                // hides products when order_possible_until is reached
                if ($product['delivery_rhythm_order_possible_until'] < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    unset($products[$i]);
                }
            }
            /*
             if ($product['delivery_rhythm_type'] == 'week' && $product['delivery_rhythm_first_delivery_day'] > $deliveryDate) {
             unset($products[$i]);
             }
             if ($product['delivery_rhythm_type'] == 'month' && $product['delivery_rhythm_first_delivery_day'] > $deliveryDate) {
             unset($products[$i]);
             }
             */
        }
        return $products;
    }
}

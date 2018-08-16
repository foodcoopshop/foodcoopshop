<?php

namespace App\Model\Table;

use App\Network\AppSession;
use App\ORM\AppMarshaller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
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
class AppTable extends Table
{

    public $tablePrefix = 'fcs_'; // legacy from CakePHP2

    public function initialize(array $config)
    {
        $this->setTable($this->tablePrefix . $this->getTable());
        // simple browser needs special header HTTP_X_UNIT_TEST_MODE => set in AppCakeTestCase::initSimpleBrowser()
        if (isset($_SERVER['HTTP_X_UNIT_TEST_MODE'])
            || (php_sapi_name() == 'cli' && $_SERVER['argv'][0] && preg_match('/phpunit/', $_SERVER['argv'][0]))) {
            $this->setConnection(ConnectionManager::get('test'));
        }
        parent::initialize($config);
    }

    public function getNumberRangeValidator(Validator $validator, $field, $min, $max, $additionalErrorMessageSuffix='')
    {
        $message = __('Please_enter_a_number_between_{0}_and_{1}.', [
            Configure::read('app.numberHelper')->formatAsDecimal($min, 0),
            Configure::read('app.numberHelper')->formatAsDecimal($max, 0)
        ]);
        if ($additionalErrorMessageSuffix != '') {
            $message .= ' ' . $additionalErrorMessageSuffix;
        }
        $validator->lessThanOrEqual($field, $max, $message);
        $validator->greaterThanOrEqual($field, $min, $message);
        $validator->notEmpty($field, $message);
        return $validator;
    }

    public function sortByVirtualField($object, $name)
    {
        return (object) Hash::sort($object->toArray(), '{n}.' . $name, 'ASC');
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
                Products.name, Products.description_short, Products.description, Products.unity, Products.price, Products.created,
                Deposits.deposit,
                Images.id_image,
                Manufacturers.id_manufacturer, Manufacturers.name as ManufacturersName,
                Manufacturers.timebased_currency_enabled,
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
                    AND ".$this->getManufacturerHolidayConditions()."
                    AND Manufacturers.active = :active ";

        if (! $this->getLoggedUser()) {
            $conditions .= 'AND Manufacturers.is_private = :isPrivate ';
        }
        return $conditions;
    }

    /**
     * @return string
     */
    public function getManufacturerHolidayConditions()
    {
        $condition  = ' IF ( ';
        $condition .=       '`Manufacturers`.`holiday_from` IS NULL && `Manufacturers`.`holiday_to` IS NULL, 1,'; // from and to date are not set
        $condition .=       'IF (';
        $condition .=              '(`Manufacturers`.`holiday_from` IS NOT NULL AND `Manufacturers`.`holiday_to`   IS NULL AND `Manufacturers`.`holiday_from` > DATE_FORMAT(NOW(), "%Y-%m-%d"))'; // from and to date are set
        $condition .=           'OR (`Manufacturers`.`holiday_to`   IS NOT NULL AND `Manufacturers`.`holiday_from` IS NULL AND `Manufacturers`.`holiday_to`   < DATE_FORMAT(NOW(), "%Y-%m-%d"))'; // from and to date are set
        $condition .=           'OR (`Manufacturers`.`holiday_from` IS NOT NULL AND `Manufacturers`.`holiday_from` > DATE_FORMAT(NOW(), "%Y-%m-%d")) ';  // only from date is set
        $condition .=           'OR (`Manufacturers`.`holiday_to`   IS NOT NULL AND `Manufacturers`.`holiday_to`   < DATE_FORMAT(NOW(), "%Y-%m-%d")), '; // to date is over
        $condition .=       '1, 0)';
        $condition .=   ')';
        return $condition;
    }

    /**
     * @return string
     */
    protected function getOrdersForProductListQuery()
    {
        return " ORDER BY Products.name ASC, Images.id_image DESC;";
    }
}

<?php

namespace App\Model\Table;

use App\Network\AppSession;
use App\ORM\AppMarshaller;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validation;

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

    public function sortByVirtualField($object, $name)
    {
        return (object) Hash::sort($object->toArray(), '{n}.' . $name, 'ASC');
    }
    
    /**
     * {@inheritDoc}
     * @see \Cake\ORM\Table::marshaller()
     */
    public function marshaller()
    {
        return new AppMarshaller($this);
    }

    /**
     * uses cake's email validation rule for comma separated email addresses
     * @param boolean $allowEmpty
     * @return ValidationRule
     */
    public function getMultipleEmailValidationRule($allowEmpty = false)
    {
        $validationRules = [
          'rule' => [
              'multipleEmails'
          ],
          'message' => 'Mindestens eine E-Mail-Adresse ist nicht gültig. Mehrere bitte mit , trennen (ohne Leerzeichen).',
          'allowEmpty' => $allowEmpty
        ];
        return $validationRules;
    }

    public function multipleEmails($check)
    {
        $emails = explode(',', reset($check));
        foreach ($emails as $email) {
            $validates = Validation::email($email);
            if (!$validates) {
                return false;
            }
        }
        return true;
    }

    public function getNumberRangeConfigurationRule($min, $max)
    {
        $validationRules = [];
        $message = 'Die Eingabe muss eine Zahl zwischen ' . $min . ' und ' . $max . ' sein.';
        $validationRules[] = [
            'rule' => [
                'comparison',
                '>=',
                $min
            ],
            'message' => $message
        ];
        $validationRules[] = [
            'rule' => [
                'comparison',
                '<=',
                $max
            ],
            'message' => $message
        ];
        return $validationRules;
    }

    /**
     * @return boolean
     */
    protected function user()
    {
        $session = new AppSession();
        if ($session->read('Auth.User.id_customer') !== null) {
            return $session->read('Auth.User.id_customer');
        }
        return false;
    }

    /**
     * @return string
     */
    protected function getFieldsForProductListQuery()
    {
        return "Products.id_product,
                ProductLangs.name, ProductLangs.description_short, ProductLangs.description, ProductLangs.unity,
                ProductShops.price, ProductShops.created,
                Deposits.deposit,
                Images.id_image,
                Manufacturers.id_manufacturer, Manufacturers.name as ManufacturersName,
                StockAvailables.quantity ";
    }

    /**
     * @return string
     */
    protected function getJoinsForProductListQuery()
    {
        return "LEFT JOIN ".$this->tablePrefix."product_shop ProductShops ON Products.id_product = ProductShops.id_product
                LEFT JOIN ".$this->tablePrefix."product_lang ProductLangs ON Products.id_product = ProductLangs.id_product
                LEFT JOIN ".$this->tablePrefix."stock_available StockAvailables ON Products.id_product = StockAvailables.id_product
                LEFT JOIN ".$this->tablePrefix."images Images ON Images.id_product = Products.id_product
                LEFT JOIN ".$this->tablePrefix."deposits Deposits ON Products.id_product = Deposits.id_product
                LEFT JOIN ".$this->tablePrefix."manufacturer Manufacturers ON Manufacturers.id_manufacturer = Products.id_manufacturer ";
    }

    /**
     * @return string
     */
    protected function getConditionsForProductListQuery()
    {
        $conditions = "WHERE 1
                    AND StockAvailables.id_product_attribute = 0
                    AND Products.active = :active
                    AND ".$this->getManufacturerHolidayConditions()."
                    AND Manufacturers.active = :active ";

        if (! $this->user()) {
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
        return " ORDER BY ProductLangs.name ASC, Images.id_image DESC;";
    }

    /**
     * http://stackoverflow.com/questions/210564/getting-raw-sql-query-string-from-pdo-prepared-statements
     * for getting the replaced statement for prepared statements
     * Replaces any parameter placeholders in a query with the value of that
     * parameter.
     * Useful for debugging. Assumes anonymous parameters from
     * $params are are in the same order as specified in $query
     *
     * @param string $query
     *            The sql query with parameter placeholders
     * @param array $params
     *            The array of substitution parameters
     * @return string The interpolated query
     */
    public static function interpolateQuery($query, $params)
    {
        $keys = [];

        // build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }
        }

        $query = preg_replace($keys, $params, $query, 1, $count);

        return $query;
    }
}

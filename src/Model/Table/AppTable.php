<?php

namespace App\Model\Table;

use Cake\ORM\Table;

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

    /**
     * for unit testing, database source needs to be changed to 'test'
     * @param string $id
     * @param string $table
     * @param string $ds
     */
    public function __construct($id = false, $table = null, $ds = null)
    {

        // simple browser needs special header HTTP_X_UNIT_TEST_MODE => set in AppCakeTestCase::initSimpleBrowser()
        if (isset($_SERVER['HTTP_X_UNIT_TEST_MODE'])
            || (php_sapi_name() == 'cli' && $_SERVER['argv'][3] && $_SERVER['argv'][3] == 'test')) {
            $this->setDataSource('test');
        }
        parent::__construct($id, $table, $ds);
    }
    
    public function initialize(array $config)
    {
        $this->setTable('fcs_' . $this->getTable());
        parent::initialize($config);
    }

    /**
     * logs validation errors
     * @see Model::validates()
     */
    public function validates($options = [])
    {
        $hasErrors = parent::validates($options);
        if (! empty($this->validationErrors)) {
            $message = 'Validation-Error: Model: ' . $this->name;
            foreach ($this->validationErrors as $field => $errors) {
                $message .= ' Field: ' . $field;
                foreach ($errors as $error) {
                    $message .= ' Error: ' . $error;
                }
            }
            $this->log($message);
        }
        return $hasErrors;
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
        App::import('Validation', 'Cake/Utility');
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
        return (boolean) CakeSession::read('Auth.User.id_customer');
    }

    /**
     * @return string
     */
    protected function getFieldsForProductListQuery()
    {
        return "Product.id_product,
                ProductLang.name, ProductLang.description_short, ProductLang.description, ProductLang.unity,
                ProductShop.price, ProductShop.date_add,
                Deposit.deposit,
                Image.id_image,
                Manufacturer.id_manufacturer, Manufacturer.name,
                StockAvailable.quantity ";
    }

    /**
     * @return string
     */
    protected function getJoinsForProductListQuery()
    {
        return "LEFT JOIN ".$this->tablePrefix."product_shop ProductShop ON Product.id_product = ProductShop.id_product
                LEFT JOIN ".$this->tablePrefix."product_lang ProductLang ON Product.id_product = ProductLang.id_product
                LEFT JOIN ".$this->tablePrefix."stock_available StockAvailable ON Product.id_product = StockAvailable.id_product
                LEFT JOIN ".$this->tablePrefix."images Image ON Image.id_product = Product.id_product
                LEFT JOIN ".$this->tablePrefix."deposits Deposit ON Product.id_product = Deposit.id_product
                LEFT JOIN ".$this->tablePrefix."manufacturer Manufacturer ON Manufacturer.id_manufacturer = Product.id_manufacturer ";
    }

    /**
     * @return string
     */
    protected function getConditionsForProductListQuery()
    {
        $conditions = "WHERE 1
                    AND StockAvailable.id_product_attribute = 0
                    AND ProductLang.id_lang = :langId
                    AND Product.active = :active
                    AND ".$this->getManufacturerHolidayConditions()."
                    AND Manufacturer.active = :active ";

        if (! $this->user()) {
            $conditions .= 'AND Manufacturer.is_private = :isPrivate ';
        }
        return $conditions;
    }

    /**
     * @return string
     */
    public function getManufacturerHolidayConditions()
    {
        $condition  = ' IF ( ';
        $condition .=       '`Manufacturer`.`holiday_from` IS NULL && `Manufacturer`.`holiday_to` IS NULL, 1,'; // from and to date are not set
        $condition .=       'IF (';
        $condition .=              '(`Manufacturer`.`holiday_from` IS NOT NULL AND `Manufacturer`.`holiday_to`   IS NULL AND `Manufacturer`.`holiday_from` > DATE_FORMAT(NOW(), "%Y-%m-%d"))'; // from and to date are set
        $condition .=           'OR (`Manufacturer`.`holiday_to`   IS NOT NULL AND `Manufacturer`.`holiday_from` IS NULL AND `Manufacturer`.`holiday_to`   < DATE_FORMAT(NOW(), "%Y-%m-%d"))'; // from and to date are set
        $condition .=           'OR (`Manufacturer`.`holiday_from` IS NOT NULL AND `Manufacturer`.`holiday_from` > DATE_FORMAT(NOW(), "%Y-%m-%d")) ';  // only from date is set
        $condition .=           'OR (`Manufacturer`.`holiday_to`   IS NOT NULL AND `Manufacturer`.`holiday_to`   < DATE_FORMAT(NOW(), "%Y-%m-%d")), '; // to date is over
        $condition .=       '1, 0)';
        $condition .=   ')';
        return $condition;
    }

    /**
     * @return string
     */
    protected function getOrdersForProductListQuery()
    {
        return " ORDER BY ProductLang.name ASC, Image.id_image DESC;";
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

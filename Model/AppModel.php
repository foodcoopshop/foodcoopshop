<?php

App::uses('Model', 'Model');

/**
 * AppModel
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
class AppModel extends Model
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
               // unit tests called via web browser
               || $_SERVER['PHP_SELF'] == '/test.php'
               // unit tests called via console
               || (php_sapi_name() == 'cli' && $_SERVER['argv'][3] && $_SERVER['argv'][3] == 'test')) {
            $this->setDataSource('test');
        }
        parent::__construct($id, $table, $ds);
    }

    /**
     * logs validation errors
     * @see Model::validates()
     */
    public function validates($options = array())
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
        $validationRules = array(
          'rule' => array(
              'multipleEmails'
          ),
          'message' => 'Mindestens eine E-Mail-Adresse ist nicht gültig. Mehrere bitte mit , trennen (ohne Leerzeichen).',
          'allowEmpty' => $allowEmpty
        );
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
        $validationRules = array();
        $message = 'Die Eingabe muss eine Zahl zwischen ' . $min . ' und ' . $max . ' sein.';
        $validationRules[] = array(
            'rule' => array(
                'comparison',
                '>=',
                $min
            ),
            'message' => $message
        );
        $validationRules[] = array(
            'rule' => array(
                'comparison',
                '<=',
                $max
            ),
            'message' => $message
        );
        return $validationRules;
    }

    /**
     * @return boolean
     */
    protected function loggedIn()
    {
        return (boolean) CakeSession::read('Auth.User.id_customer');
    }

    /**
     * @return string
     */
    protected function getFieldsForProductListQuery()
    {
        return "Product.id_product,
                ProductLang.name, ProductLang.description_short, ProductLang.description,
                ProductShop.price, ProductShop.unity, ProductShop.date_add,
                CakeDeposit.deposit,
                ImageLang.id_image, ImageLang.legend,
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
                LEFT JOIN ".$this->tablePrefix."image Image ON Image.id_product = Product.id_product AND (Image.cover IS NULL OR Image.cover = 1)
                LEFT JOIN ".$this->tablePrefix."image_lang ImageLang ON ImageLang.id_image = Image.id_image
                LEFT JOIN ".$this->tablePrefix."image_shop ImageShop ON ImageShop.id_image = Image.id_image
                LEFT JOIN ".$this->tablePrefix."cake_deposits CakeDeposit ON Product.id_product = CakeDeposit.id_product
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
                    AND (ImageLang.id_lang = :langId OR ImageLang.id_lang IS NULL)
                    AND Product.active = :active
                    AND ".$this->getManufacturerHolidayConditions()."
                    AND Manufacturer.active = :active
                    AND ProductShop.id_shop = :shopId
                    AND (ImageShop.id_shop = :shopId OR ImageShop.id_shop IS NULL) ";

        if (! $this->loggedIn()) {
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
        $condition .=       '`Manufacturer`.`holiday_from` = "0000-00-00" && `Manufacturer`.`holiday_to` = "0000-00-00", 1,'; // from and to date are not set
        $condition .=       'IF (';
        $condition .=              '(`Manufacturer`.`holiday_from` <> "0000-00-00" AND `Manufacturer`.`holiday_to`   = "0000-00-00" AND `Manufacturer`.`holiday_from` > DATE_FORMAT(NOW(), "%Y-%m-%d"))'; // from and to date are set
        $condition .=           'OR (`Manufacturer`.`holiday_to`   <> "0000-00-00" AND `Manufacturer`.`holiday_from` = "0000-00-00" AND `Manufacturer`.`holiday_to`   < DATE_FORMAT(NOW(), "%Y-%m-%d"))'; // from and to date are set
        $condition .=           'OR (`Manufacturer`.`holiday_from` <> "0000-00-00" AND `Manufacturer`.`holiday_from` > DATE_FORMAT(NOW(), "%Y-%m-%d")) ';  // only from date is set
        $condition .=           'OR (`Manufacturer`.`holiday_to`   <> "0000-00-00" AND `Manufacturer`.`holiday_to`   < DATE_FORMAT(NOW(), "%Y-%m-%d")), '; // to date is over
        $condition .=       '1, 0)';
        $condition .=   ')';
        return $condition;
    }

    /**
     * @return string
     */
    protected function getOrdersForProductListQuery()
    {
        return " ORDER BY ProductLang.name ASC, ImageShop.id_image DESC;";
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
        $keys = array();

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

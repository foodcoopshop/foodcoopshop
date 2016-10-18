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

    protected function loggedIn()
    {
        return (boolean) SessionComponent::read('Auth.User.id_customer');
    }

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

    protected function getConditionsForProductListQuery()
    {
        $conditions = "WHERE 1
                    AND StockAvailable.id_product_attribute = 0
                    AND ProductLang.id_lang = :langId
                    AND (ImageLang.id_lang = :langId OR ImageLang.id_lang IS NULL)
                    AND Product.active = :active
                    AND Manufacturer.holiday != :active
                    AND Manufacturer.active = :active
                    AND ProductShop.id_shop = :shopId
                    AND (ImageShop.id_shop = :shopId OR ImageShop.id_shop IS NULL) ";
        
        if (! $this->loggedIn()) {
            $conditions .= 'AND Manufacturer.is_private = :isPrivate ';
        }
        
        return $conditions;
    }

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

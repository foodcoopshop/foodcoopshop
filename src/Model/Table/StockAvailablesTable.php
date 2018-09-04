<?php

namespace App\Model\Table;

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class StockAvailablesTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('stock_available');
        parent::initialize($config);
        $this->setPrimaryKey('id_product');
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->numeric('quantity', __('The_quantity_needs_to_be_a_number.'));
        $validator = $this->getNumberRangeValidator($validator, 'quantity', -5000, 5000, __('Field:_Stock'));
        
        $validator->numeric('quantity_limit', __('The_quantity_limit_needs_to_be_a_number.'));
        $validator = $this->getNumberRangeValidator($validator, 'quantity_limit', -5000, 0, __('Field:_Order_possible_until'));
        
        $validator->numeric('sold_out_quantity', __('The_sold_out_quantity_needs_to_be_a_number.'));
        $validator = $this->getNumberRangeValidator($validator, 'sold_out_quantity', -5000, 5000, __('Field:_Notification_if_quantity_limit_reached'));
        $validator->allowEmpty('sold_out_quantity');
        
        return $validator;
    }

    public function updateQuantityForMainProduct($productId)
    {
        $productId = (int) $productId;
        if ($productId < 0) {
            return;
        }

        $query = 'UPDATE '.$this->getTable().' AS sa1, (
                     SELECT SUM(quantity) as quantitySum
                     FROM '.$this->getTable().'
                     WHERE id_product = :productId
                         AND id_product_attribute > 0
                     GROUP BY id_product 
                     ) sa2
                 SET sa1.quantity = sa2.quantitySum
                 WHERE sa1.id_product = ' . $productId . '
                     AND sa1.id_product_attribute = 0';
        $params = [
            'productId' => $productId
        ];
        $statement = $this->getConnection()->prepare($query);
        $statement->execute($params);
    }
}

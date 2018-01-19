<?php

namespace App\Model\Table;

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
class StockAvailablesTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('stock_available');
        parent::initialize($config);
    }
    
    public $primaryKey = 'id_product';

    public function updateQuantityForMainProduct($productId)
    {
        $productId = (int) $productId;
        if ($productId < 0) {
            return;
        }

        // TODO use prepared statement
        $sql = 'UPDATE '.$this->tablePrefix.'stock_available sa1, (
                     SELECT SUM(quantity) as quantitySum
                     FROM '.$this->tablePrefix.'stock_available
                     WHERE id_product = ' . $productId . '
                         AND id_product_attribute > 0
                     GROUP BY id_product 
                     ) sa2
                 SET sa1.quantity = sa2.quantitySum
                 WHERE sa1.id_product = ' . $productId . '
                     AND sa1.id_product_attribute = 0';

        $this->query($sql);
    }
}

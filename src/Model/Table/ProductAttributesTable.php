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
class ProductAttributesTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('product_attribute');
        parent::initialize($config);
        $this->setPrimaryKey('id_product_attribute');
        $this->belongsTo('Products', [
            'foreignKey' => 'id_product'
        ]);
        $this->hasOne('ProductAttributeCombinations', [
            'foreignKey' => 'id_product_attribute'
        ]);
        $this->hasOne('StockAvailables', [
            'foreignKey' => 'id_product_attribute'
        ]);
        $this->hasOne('DepositProductAttributes', [
            'foreignKey' => 'id_product_attribute'
        ]);
        $this->hasOne('ProductAttributeShops', [
            'className' => 'ProductAttributeShops',
            'foreignKey' => 'id_product_attribute'
        ]);
    }

}

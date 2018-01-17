<?php
/**
 * ProductAttribute
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
class ProductAttribute extends AppModel
{

    public $useTable = 'product_attribute';

    public $primaryKey = 'id_product_attribute';

    public $hasOne = [
        'ProductAttributeCombination' => [
            'foreignKey' => 'id_product_attribute'
        ],
        'StockAvailable' => [
            'foreignKey' => 'id_product_attribute'
        ],
        'DepositProductAttribute' => [
            'className' => 'Deposit',
            'foreignKey' => 'id_product_attribute'
        ],
        'ProductAttributeShop' => [
            'className' => 'ProductAttributeShop',
            'foreignKey' => 'id_product_attribute'
        ]
    ];

    public $belongsTo = [
        'Product' => [
            'foreignKey' => 'id_product'
        ]
    ];
}

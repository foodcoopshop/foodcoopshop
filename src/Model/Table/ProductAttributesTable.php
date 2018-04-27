<?php

namespace App\Model\Table;

use Cake\ORM\TableRegistry;

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

    public function add($productId, $attributeId)
    {
        $defaultQuantity = 999;

        $productAttributesCount = $this->find('all', [
            'conditions' => [
                'ProductAttributes.id_product' => $productId
            ]
        ])->count();

        $newAttribute = $this->save(
            $this->newEntity(
                [
                    'id_product' => $productId,
                    'default_on' => $productAttributesCount == 0 ? 1 : 0
                ]
            )
        );
        $productAttributeId = $newAttribute->id_product_attribute;

        // INSERT in ProductAttributeCombination tricky because of set primary_key
        $this->getConnection()->query('INSERT INTO '.$this->tablePrefix.'product_attribute_combination (id_attribute, id_product_attribute) VALUES(' . $attributeId . ', ' . $productAttributeId . ')');

        $this->ProductAttributeShops->save(
            $this->ProductAttributeShops->newEntity(
                [
                    'id_product_attribute' => $productAttributeId,
                    'default_on' => $productAttributesCount == 0 ? 1 : 0,
                    'id_shop' => 1,
                    'id_product' => $productId
                ]
            )
        );

        // set price of product back to 0 => if not, the price of the attribute is added to the price of the product
        $this->ProductShop = TableRegistry::getTableLocator()->get('ProductShops');
        $this->ProductShop->id = $productId;
        $this->ProductShop->save(
            $this->ProductShop->patchEntity(
                $this->ProductShop->get($productId),
                [
                    'price' => 0
                ]
            )
        );

        // avoid Integrity constraint violation: 1062 Duplicate entry '64-232-1-0' for key 'product_sqlstock'
        // with custom sql
        $this->getConnection()->query('INSERT INTO '.$this->tablePrefix.'stock_available (id_product, id_product_attribute, quantity) VALUES(' . $productId . ', ' . $productAttributeId . ', ' . $defaultQuantity . ')');

        $this->StockAvailable = TableRegistry::getTableLocator()->get('StockAvailables');
        $this->StockAvailable->updateQuantityForMainProduct($productId);
    }
}

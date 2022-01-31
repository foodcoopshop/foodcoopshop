<?php

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterDeleteTrait;
use Cake\Datasource\FactoryLocator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductAttributesTable extends AppTable
{

    use ProductCacheClearAfterDeleteTrait;

    public function initialize(array $config): void
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
        $this->hasOne('PurchasePriceProductAttributes', [
            'foreignKey' => 'product_attribute_id',
        ]);
        $this->hasOne('BarcodeProductAttributes', [
            'foreignKey' => 'product_attribute_id',
        ]);
        $this->hasOne('DepositProductAttributes', [
            'foreignKey' => 'id_product_attribute'
        ]);
        $this->hasOne('UnitProductAttributes', [
            'foreignKey' => 'id_product_attribute'
        ]);
    }

    public function deleteProductAttribute($productId, $productAttributeId)
    {

        $pac = $this->ProductAttributeCombinations->find('all', [
            'conditions' => [
                'ProductAttributeCombinations.id_product_attribute' => $productAttributeId,
            ]
        ])->first();
        $productAttributeId = $pac->id_product_attribute;

        $this->deleteAll([
            'ProductAttributes.id_product_attribute' => $productAttributeId,
        ]);

        $this->ProductAttributeCombinations->deleteAll([
            'ProductAttributeCombinations.id_product_attribute' => $productAttributeId,
        ]);

        $this->UnitProductAttributes->deleteAll([
            'UnitProductAttributes.id_product_attribute' => $productAttributeId,
        ]);

        $this->PurchasePriceProductAttributes->deleteAll([
            'PurchasePriceProductAttributes.product_attribute_id' => $productAttributeId,
        ]);

        $this->BarcodeProductAttributes->deleteAll([
            'BarcodeProductAttributes.product_attribute_id' => $productAttributeId,
        ]);

        // deleteAll can only get primary key as condition
        $originalPrimaryKey = $this->StockAvailables->getPrimaryKey();
        $this->StockAvailables->setPrimaryKey('id_product_attribute');
        $this->StockAvailables->deleteAll([
            'StockAvailables.id_product_attribute' => $productAttributeId,
        ]);
        $this->StockAvailables->setPrimaryKey($originalPrimaryKey);

        $this->StockAvailables->updateQuantityForMainProduct($productId);
    }


    public function add($productId, $attributeId)
    {
        $defaultQuantity = 0;

        $productAttributesCount = $this->find('all', [
            'conditions' => [
                'ProductAttributes.id_product' => $productId,
            ]
        ])->count();

        $newAttribute = $this->save(
            $this->newEntity(
                [
                    'id_product' => $productId,
                    'default_on' => $productAttributesCount == 0 ? 1 : 0,
                ]
            )
        );
        $productAttributeId = $newAttribute->id_product_attribute;

        // INSERT in ProductAttributeCombination tricky because of set primary_key
        $sql = 'INSERT INTO ' . $this->tablePrefix . 'product_attribute_combination (id_attribute, id_product_attribute) VALUES (:attributeId, :productAttributeId)';
        $params = [
            'attributeId' => (int) $attributeId,
            'productAttributeId' => (int) $productAttributeId,
        ];
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);

        // set price of product back to 0 => if not, the price of the attribute is added to the price of the product
        $this->Product = FactoryLocator::get('Table')->get('Products');
        $this->Product->save(
            $this->Product->patchEntity(
                $this->Product->get($productId),
                [
                    'price' => 0,
                ]
            )
        );

        $this->BarcodeProduct = FactoryLocator::get('Table')->get('BarcodeProducts');
        $this->BarcodeProduct->deleteAll([
            'BarcodeProducts.product_id' => $productId,
        ]);

        // avoid Integrity constraint violation: 1062 Duplicate entry '64-232-1-0' for key 'product_sqlstock' with custom sql
        $sql = 'INSERT INTO ' . $this->tablePrefix . 'stock_available (id_product, id_product_attribute, quantity) VALUES (:productId, :productAttributeId, :quantity)';
        $params = [
            'productId' => (int) $productId,
            'productAttributeId' => (int) $productAttributeId,
            'quantity' => (int) $defaultQuantity,
        ];
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);

        $this->StockAvailable = FactoryLocator::get('Table')->get('StockAvailables');
        $this->StockAvailable->updateQuantityForMainProduct($productId);
    }
}

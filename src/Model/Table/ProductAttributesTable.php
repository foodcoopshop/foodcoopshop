<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductAttributesTable extends AppTable
{

    use ProductCacheClearAfterSaveAndDeleteTrait;

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

        $productAttributeCombinationsTable = TableRegistry::getTableLocator()->get('ProductAttributeCombinations');
        $pac = $productAttributeCombinationsTable->find('all',
            conditions: [
                'ProductAttributeCombinations.id_product_attribute' => $productAttributeId,
            ]
        )->first();
        $productAttributeId = $pac->id_product_attribute;

        $this->deleteAll([
            'ProductAttributes.id_product_attribute' => $productAttributeId,
        ]);

        $productAttributeCombinationsTable->deleteAll([
            'ProductAttributeCombinations.id_product_attribute' => $productAttributeId,
        ]);

        $unitProductAttributesTable = TableRegistry::getTableLocator()->get('UnitProductAttributes');
        $unitProductAttributesTable->deleteAll([
            'UnitProductAttributes.id_product_attribute' => $productAttributeId,
        ]);

        $purchasePriceProductAttributesTable = TableRegistry::getTableLocator()->get('PurchasePriceProductAttributes');
        $purchasePriceProductAttributesTable->deleteAll([
            'PurchasePriceProductAttributes.product_attribute_id' => $productAttributeId,
        ]);

        $barcodeProductAttributesTable = TableRegistry::getTableLocator()->get('BarcodeProductAttributes');
        $barcodeProductAttributesTable->deleteAll([
            'BarcodeProductAttributes.product_attribute_id' => $productAttributeId,
        ]);

        // deleteAll can only get primary key as condition
        $stockAvailablesTable = TableRegistry::getTableLocator()->get('StockAvailables');
        $originalPrimaryKey = $stockAvailablesTable->getPrimaryKey();
        $stockAvailablesTable->setPrimaryKey('id_product_attribute');
        $stockAvailablesTable->deleteAll([
            'StockAvailables.id_product_attribute' => $productAttributeId,
        ]);
        $stockAvailablesTable->setPrimaryKey($originalPrimaryKey);

        $stockAvailablesTable->updateQuantityForMainProduct($productId);
    }


    public function add($productId, $attributeId)
    {
        $defaultQuantity = 0;

        $productAttributesCount = $this->find('all', conditions: [
            'ProductAttributes.id_product' => $productId,
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
        $statement = $this->getConnection()->getDriver()->prepare($sql);
        $statement->execute($params);

        // set price of product back to 0 => if not, the price of the attribute is added to the price of the product
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $productsTable->save(
            $productsTable->patchEntity(
                $productsTable->get($productId),
                [
                    'price' => 0,
                ]
            )
        );

        $barcodeProductsTable = TableRegistry::getTableLocator()->get('BarcodeProducts');
        $barcodeProductsTable->deleteAll([
            'BarcodeProducts.product_id' => $productId,
        ]);

        // avoid Integrity constraint violation: 1062 Duplicate entry '64-232-1-0' for key 'product_sqlstock' with custom sql
        $sql = 'INSERT INTO ' . $this->tablePrefix . 'stock_available (id_product, id_product_attribute, quantity) VALUES (:productId, :productAttributeId, :quantity)';
        $params = [
            'productId' => (int) $productId,
            'productAttributeId' => (int) $productAttributeId,
            'quantity' => (int) $defaultQuantity,
        ];
        $statement = $this->getConnection()->getDriver()->prepare($sql);
        $statement->execute($params);

        $stockAvailablesTable = TableRegistry::getTableLocator()->get('StockAvailables');
        $stockAvailablesTable->updateQuantityForMainProduct($productId);
    }
}

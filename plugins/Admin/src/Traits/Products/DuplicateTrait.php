<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Model\Entity\Product;
use App\Model\Entity\PurchasePriceProduct;
use App\Model\Table\AttributesTable;
use App\Model\Table\DepositProductsTable;
use App\Model\Table\ProductAttributeCombinationsTable;
use App\Model\Table\ProductAttributesTable;
use App\Model\Table\PurchasePriceProductsTable;
use App\Model\Table\StockAvailablesTable;
use App\Model\Table\UnitProductsTable;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\Http\Response;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Martin Hatlauf <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait DuplicateTrait
{

    public function duplicate(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productIds = $this->getRequest()->getData('productIds');
        $copyAmount = $this->getRequest()->getData('copyAmount');
        $copies = [];

        $productsTable = $this->getTableLocator()->get('Products');
        $associations = [
            'StockAvailables' =>
                [
                    'primaryKey' => StockAvailablesTable::ORIGINAL_PRIMARY_KEY
                ],
            'UnitProducts' =>
                [
                    'primaryKey' => UnitProductsTable::ORIGINAL_PRIMARY_KEY
                ],
            'DepositProducts' =>
                [
                    'primaryKey' => DepositProductsTable::ORIGINAL_PRIMARY_KEY
                ],
            'CategoryProducts' => [],
        ];

        $srcProducts = [];
        foreach ($productIds as $productId) {
            $srcProduct = $productsTable->find('all',
                conditions: [
                    $productsTable->aliasField('id_product IN') => $productId,
                ],
                contain: array_keys($associations),
            );
            if ($srcProduct->count() > 1) {
                continue;
            }
            $srcProducts[] = $srcProduct;
        }

        foreach ($srcProducts as $srcProduct) {
            $preExistingCopies = $productsTable->find('all',
                conditions: [
                    $productsTable->aliasField('name LIKE') => __d('admin', '{0} - copy {1}', [
                        $srcProduct->name,
                        '%',
                    ]),
                    'NOT active IN' => APP_DEL,
                ],
            );
            $amountOfPreCopies = $preExistingCopies->count() + 1;

            for ($i = 0; $i < $copyAmount; $i++) {
                $copy = $this->deepCopyProduct($srcProduct, $associations, ($amountOfPreCopies + $i));

                $copy = $productsTable->save($copy);
                $copies[] = $copy;

                $this->checkPurchasePrices($srcProduct, $copy);
            }
        }


        $preparedProductForActionLog = [];
        foreach ($copies as $productCopy) {
            $preparedProductForActionLog[] = '<b>' . $productCopy->name . '</b>: ID ' . $productCopy->id_product;
        }

        $this->getRequest()->getSession()->write('highlightedRowId', $copies[0]->id_product);
        $message = __d('admin', 'Product was copied successfully.');

        $this->Flash->success($message);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('product_copied', $this->identity->getId(), 0, 'products', $message . '<br />' . join('<br />', $preparedProductForActionLog));

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        return null;
    }

    public function checkPurchasePrices(mixed $srcProduct, mixed $copy)
    {
        $purchasePriceProductTable = $this->getTableLocator()->get('PurchasePriceProducts');
        $srcPurchasePrice = $purchasePriceProductTable->find('all',
            conditions: [
                $purchasePriceProductTable->getPrimaryKey() => $srcProduct->toArray()['id_product']
            ],
        )->first();

        if (!$srcPurchasePrice instanceof PurchasePriceProduct) {
            return;
        }

        $copyPurchasePriceData = $srcPurchasePrice->toArray();

        unset($copyPurchasePriceData[PurchasePriceProductsTable::ORIGINAL_PRIMARY_KEY]);
        $copyPurchasePriceData[$purchasePriceProductTable->getPrimaryKey()] = $copy->id_product;

        $purchasePriceCopy = new Entity(
            $copyPurchasePriceData,
            [
                'validate' => false,
            ]
        );

        $purchasePriceProductTable->save($purchasePriceCopy);
    }

    function deepCopyProduct(Product $srcProduct, array $associations, int $copyIndex): EntityInterface
    {
        $productsTable = $this->getTableLocator()->get('Products');

        $product = $srcProduct->toArray();
        unset($product[$productsTable->getPrimaryKey()]);

        $product = $this->removeAssociationKeysFromProduct($associations, $product);

        $product = $this->configureCopy($srcProduct, $copyIndex, $product);

        $associationWithValidation = array_fill_keys(
            array_keys($associations),
            ['validate' => false]
        );

        return $productsTable->newEntity(
            $product,
            [
                'associated' => $associationWithValidation,
                'validate' => false,
            ]
        );
    }

    public function isAssociationNamePlural(string $associationName, array $product): bool
    {
        $pluralAssociationName = Inflector::pluralize($associationName);
        return array_key_exists($pluralAssociationName, $product);
    }

    public function removeHasOneAssociationKeys(?array $associatedTable, string $primaryKey): ?array
    {
        if ($associatedTable == null) {
            return $associatedTable;
        }
        unset($associatedTable[$primaryKey]);

        // tests would also pass like that:
        //unset($associatedTable['id_product']);
        unset($associatedTable['id_product'], $associatedTable['product_id']);

        return $associatedTable;
    }

    public function removeHasManyAssociationKeys(?array $associatedTable): ?array
    {
        if ($associatedTable == null) {
            return $associatedTable;
        }

        foreach ($associatedTable as $association) {
            // tests would also pass like that:
            //unset($association['id_product']);
            unset($association['id_product'], $association['product_id']);
        }

        return $associatedTable;
    }

    public function removeAssociationKeysFromProduct(array $associations, array $product): array
    {
        foreach ($associations as $associationName => $options) {

            $primaryKey = $this->getTableLocator()->get($associationName)->getPrimaryKey();
            if (isset($options['primaryKey'])) {
                $primaryKey = $options['primaryKey'];
            }

            $tableAssociationName = Inflector::tableize($associationName);
            $tableAssociationName = Inflector::singularize($tableAssociationName);

            if ($this->isAssociationNamePlural($tableAssociationName, $product)) {
                $tableAssociationName = Inflector::pluralize($tableAssociationName);
                $product[$tableAssociationName] = $this->removeHasManyAssociationKeys($product[$tableAssociationName]);
                continue;
            }

            $product[$tableAssociationName] = $this->removeHasOneAssociationKeys($product[$tableAssociationName], $primaryKey);
        }
        return $product;
    }

    public function configureCopy(Product $srcProduct, int $copyIndex, array $product): array
    {
        $product['name'] = __d('admin', '{0} - copy {1}', [
            $srcProduct->name,
            $copyIndex,
        ]);
        unset($product['modified']);
        unset($product['created']);

        $product['new'] = DateTime::now();
        $product['active'] = APP_OFF;
        return $product;
    }

}
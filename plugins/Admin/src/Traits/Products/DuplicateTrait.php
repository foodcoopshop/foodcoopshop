<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Model\Entity\Product;
use App\Model\Table\DepositProductsTable;
use App\Model\Table\PurchasePriceProductsTable;
use App\Model\Table\StockAvailablesTable;
use App\Model\Table\UnitProductsTable;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\Http\Response;
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

        $productId = $this->getRequest()->getData('productId');
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
            'PurchasePriceProducts' => [
                'primaryKey' => PurchasePriceProductsTable::ORIGINAL_PRIMARY_KEY
            ],
            'CategoryProducts' => [],
        ];

        $srcProduct = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
            ],
            contain: array_keys($associations),
        )->first();

        $preExistingCopies = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('name LIKE') => __d('admin', 'Copy ({0}) of {1}', [
                    '%',
                    $srcProduct->name,
                ])
            ],
        );
        $amountOfPreCopies = $preExistingCopies->count() + 1;

        for ($i = 0; $i < $copyAmount; $i++) {
            $copies[] = $this->deepCopyProduct($srcProduct, $associations, $amountOfPreCopies + $i);
        }

        $result = $productsTable->saveMany($copies);

        $preparedProductForActionLog = [];
        foreach ($copies as $productCopy) {
            $preparedProductForActionLog[] = '<b>' . $productCopy->name . '</b>: ID ' . $productCopy->id_product;
        }

        $message = __d('admin', 'product_was_copied_successfully.');

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

    function deepCopyProduct(Product $srcProduct, array $associations, int $copyIndex): EntityInterface
    {
        $productsTable = $this->getTableLocator()->get('Products');


        $product = $srcProduct->toArray();

        unset($product[$productsTable->getPrimaryKey()]);

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

        $product['name'] =
            __d('admin', 'Copy ({0}) of {1}', [
                    $copyIndex,
                    $srcProduct->name
                ]
            );
        unset($product['modified']);
        unset($product['created']);

        $product['new'] = DateTime::now();
        $product['active'] = APP_OFF;


        $associationWithValidation = array_fill_keys(
            array_keys($associations),
            ['validate' => false]
        );

        return $productsTable->newEntity(
            $product,
            [
                'associated' => $associationWithValidation,
            ]
        );
    }

    public function isAssociationNamePlural(string $associationName, array $product): bool
    {
        if (array_key_exists($associationName, $product)) {
            return false;
        }

        $pluralAssociationName = Inflector::pluralize($associationName);
        if (array_key_exists($pluralAssociationName, $product)) {
            return true;
        }

        return false;
    }


    public function removeHasOneAssociationKeys(mixed $associatedTable, string $primaryKey): mixed
    {
        if ($associatedTable == null) {
            return $associatedTable;
        }

        unset($associatedTable[$primaryKey]);

        unset($associatedTable['id_product'], $associatedTable['product_id']);

        return $associatedTable;
    }

    public function removeHasManyAssociationKeys(mixed $associatedTable): mixed
    {
        if ($associatedTable == null) {
            return $associatedTable;
        }

        foreach ($associatedTable as $association) {
            unset($association['id_product'], $associatedTable['product_id']);
        }

        return $associatedTable;
    }
}
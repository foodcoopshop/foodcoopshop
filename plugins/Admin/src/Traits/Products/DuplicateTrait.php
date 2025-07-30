<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Model\Entity\Product;
use App\Model\Table\DepositProductsTable;
use App\Model\Table\PurchasePriceProductsTable;
use App\Model\Table\StockAvailablesTable;
use App\Model\Table\UnitProductsTable;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\Http\Response;
use Cake\Utility\Inflector;
use function PHPUnit\Framework\assertEquals;

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
            'CategoryProducts' => [],
            'PurchasePriceProducts' => [
                'primaryKey' => PurchasePriceProductsTable::ORIGINAL_PRIMARY_KEY
            ]
        ];

        $srcProduct = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
            ],
            contain: array_keys($associations),
        )->first();

        pr($srcProduct);

        $preExistingCopies = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('name LIKE') => __d('admin', '{0} - copy {1}', [
                    $srcProduct->name,
                    '%',
                ])
            ],
        );
        $amountOfPreCopies = $preExistingCopies->count() + 1;

        for ($i = 0; $i < $copyAmount; $i++) {
            $copies[] = $this->deepCopyProduct($srcProduct, $associations, $amountOfPreCopies + $i);
        }

        pr($copies[0]);
        pr("-------");

        $copies = $productsTable->saveMany($copies);

        pr($copies[0]);


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

    function deepCopyProduct(Product $srcProduct, array $associations, int $copyIndex): EntityInterface
    {
        $productsTable = $this->getTableLocator()->get('Products');

        $product = $srcProduct->toArray();
        unset($product[$productsTable->getPrimaryKey()]);

        foreach ($associations as $associationName => $options) {

            pr("________________________________________________________________________________________");
            pr("________________________________________________________________________________________");
            pr("________________________________________________________________________________________");
            pr("------------");
            pr($associationName);
            pr("--");

            $primaryKey = $this->getTableLocator()->get($associationName)->getPrimaryKey();
            if (isset($options['primaryKey'])) {
                $primaryKey = $options['primaryKey'];
            }

            $tableAssociationName = Inflector::tableize($associationName);
            $tableAssociationName = Inflector::singularize($tableAssociationName);

            pr($tableAssociationName);
            pr("------------");
            pr($product[$tableAssociationName]);
            pr("------------");

            if ($this->isAssociationNamePlural($tableAssociationName, $product)) {
                $tableAssociationName = Inflector::pluralize($tableAssociationName);
                $product[$tableAssociationName] = $this->removeHasManyAssociationKeys($product[$tableAssociationName]);
                continue;
            }

            $product[$tableAssociationName] = $this->removeHasOneAssociationKeys($product[$tableAssociationName], $primaryKey);
        }

        $product['name'] = __d('admin', '{0} - copy {1}', [
            $srcProduct->name,
            $copyIndex,
        ]);
        unset($product['modified']);
        unset($product['created']);

        $product['new'] = DateTime::now();
        $product['active'] = APP_OFF;

        $associationWithValidation = array_fill_keys(
            array_keys($associations),
            ['validate' => false]
        );

        pr("#########################################################################################");
        pr("#########################################################################################");
        pr(
            $productsTable->newEntity(
                $product,
                [
                    'associated' => $associationWithValidation,
                ]
            )
        );
        pr("#########################################################################################");
        pr("#########################################################################################");


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
            unset($association['id_product'], $associatedTable['product_id']);
        }

        return $associatedTable;
    }
}
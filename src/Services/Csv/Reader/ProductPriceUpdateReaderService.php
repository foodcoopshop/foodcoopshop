<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Volker Peters
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Csv\Reader;

use Cake\ORM\TableRegistry;

/**
 * @extends \App\Services\Csv\Reader\ProductReaderService<array<string, scalar|null>>
 */
class ProductPriceUpdateReaderService extends ProductReaderService
{

    /**
     * @return array{updated: int, inserted: int, deactivated: int, errors: list<mixed>}
     */
    public function priceUpdate(int $manufacturerId, float $surcharge): array
    {
        $records = $this->getPreparedRecords();

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $purchasePriceProductsTable = TableRegistry::getTableLocator()->get('PurchasePriceProducts');
        $taxesTable = TableRegistry::getTableLocator()->get('Taxes');

        $existingProducts = $productsTable->find('all',
            conditions: [
                'Products.id_manufacturer' => $manufacturerId,
                'Products.manufacturer_order_number IS NOT' => null,
                'Products.manufacturer_order_number !=' => '',
            ],
            contain: ['StockAvailables'],
        )->toArray();

        $existingByOrderNumber = [];
        foreach ($existingProducts as $product) {
            $existingByOrderNumber[$product->manufacturer_order_number] = $product;
        }

        $csvOrderNumbers = [];
        $errors = [];
        $updatedCount = 0;
        $insertedCount = 0;

        foreach ($records as $index => $record) {
            $orderNumber = trim((string) ($record[__('Manufacturer_order_number')] ?? ''));
            $grossPrice = $record[__('Gross_price')] ?? 0;
            $taxRate = $record[__('Tax_rate')] ?? 0;
            $netPriceAndTaxId = $taxesTable->getNetPriceAndTaxId((float) $grossPrice, (float) $taxRate);

            if ($orderNumber !== '') {
                $csvOrderNumbers[] = $orderNumber;
            }

            if (isset($existingByOrderNumber[$orderNumber])) {
                $product = $existingByOrderNumber[$orderNumber];
                $ppEntity = $purchasePriceProductsTable->getEntityToSaveByProductId($product->id_product);
                $ppEntity->price = $netPriceAndTaxId['netPrice'];
                $ppEntity->tax_id = $netPriceAndTaxId['taxId'];
                if (!$purchasePriceProductsTable->save($ppEntity)) {
                    $errors[] = ['index' => $index, 'errors' => $ppEntity->getErrors()];
                } else {
                    $updatedCount++;
                }
            } else {
                $entity = $productsTable->getValidatedEntity(
                    $manufacturerId,
                    (string) ($record[__('Name')] ?? ''),
                    (string) ($record[__('Description_short')] ?? ''),
                    (string) ($record[__('Description')] ?? ''),
                    (string) ($record[__('Unit')] ?? ''),
                    (float) $grossPrice,
                    (float) $taxRate,
                    (float) ($record[__('Deposit')] ?? 0),
                    (string) ($record[__('Amount')] ?? ''),
                    (string) ($record[__('Status')] ?? ''),
                    (int) ($record[__('Product_declaration')] ?? 0),
                    (string) ($record[__('Storage_location')] ?? ''),
                    $orderNumber,
                );
                if ($entity->hasErrors()) {
                    $errors[] = ['index' => $index, 'errors' => $entity->getErrors()];
                    continue;
                }
                $savedProduct = $productsTable->save($entity);
                if ($savedProduct) {
                    $ppEntity = $purchasePriceProductsTable->getEntityToSaveByProductId($savedProduct->id_product);
                    $ppEntity->price = $netPriceAndTaxId['netPrice'];
                    $ppEntity->tax_id = $netPriceAndTaxId['taxId'];
                    $purchasePriceProductsTable->save($ppEntity);
                    $insertedCount++;
                }
            }
        }

        $deactivatedCount = 0;
        foreach ($existingByOrderNumber as $orderNumber => $product) {
            if (!in_array($orderNumber, $csvOrderNumbers)) {
                if ($product->is_stock_product && !empty($product->stock_available) && $product->stock_available->quantity > 0) {
                    continue;
                }
                $patched = $productsTable->patchEntity($product, ['active' => APP_OFF]);
                $productsTable->save($patched);
                $deactivatedCount++;
            }
        }

        $allProductEntities = $productsTable->find('all',
            conditions: ['Products.id_manufacturer' => $manufacturerId],
            fields: ['id_product'],
        )->toArray();
        $allProductIds = array_column($allProductEntities, 'id_product');
        if (!empty($allProductIds)) {
            $surchargeResult = $purchasePriceProductsTable->getSellingPricesWithSurcharge($allProductIds, $surcharge);
            $productsTable->changePrice($surchargeResult['pricesToChange']);
        }

        return [
            'updated' => $updatedCount,
            'inserted' => $insertedCount,
            'deactivated' => $deactivatedCount,
            'errors' => $errors,
        ];
    }

}

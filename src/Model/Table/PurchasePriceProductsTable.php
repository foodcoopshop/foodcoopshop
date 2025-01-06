<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Traits\ProductAndAttributeEntityTrait;
use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PurchasePriceProductsTable extends AppTable
{

    use ProductAndAttributeEntityTrait;
    use ProductCacheClearAfterSaveAndDeleteTrait;

    public function initialize(array $config): void
    {
        $this->setTable('purchase_prices');
        parent::initialize($config);
        $this->setPrimaryKey('product_id');
        $this->belongsTo('Taxes', [
            'foreignKey' => 'tax_id'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->greaterThanOrEqual('price', 0, __('The_price_needs_to_be_greater_or_equal_than_0.'));
        return $validator;
    }

    public function isPurchasePriceSet($entity): bool
    {
        $result = true;
        if (!empty($entity->unit_product) && $entity->unit_product->price_per_unit_enabled) {
            if (is_null($entity->unit_product->purchase_price_incl_per_unit)) {
                $result = false;
            }
        } else {
            if (empty($entity->purchase_price_product) || is_null($entity->purchase_price_product->price)) {
                $result = false;
            }
        }
        return $result;
    }

    public function calculateSellingPriceGrossBySurcharge($purchasePriceNet, $surcharge, $sellingPriceTaxRate): float
    {
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $purchasePriceNetWithSurcharge = $purchasePriceNet * (100 + $surcharge) / 100;
        $sellingPriceGross = $productsTable->getGrossPrice($purchasePriceNetWithSurcharge, $sellingPriceTaxRate);
        return $sellingPriceGross;
    }

    public function calculateSurchargeBySellingPriceNet($sellingPriceNet, $purchasePriceNet): float
    {
        $surcharge = ($sellingPriceNet / $purchasePriceNet * 100) - 100;
        return $surcharge;
    }

    public function calculateSurchargeBySellingPriceGross($sellingPriceGross, $sellingPriceTaxRate, $purchasePriceGross, $purchasePriceTaxRate): float
    {

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $sellingPriceNet = $productsTable->getNetPrice($sellingPriceGross, $sellingPriceTaxRate);
        $purchasePriceNet = $productsTable->getNetPrice($purchasePriceGross, $purchasePriceTaxRate);

        if ($purchasePriceNet == 0) {
            return 0;
        }

        $surcharge = $this->calculateSurchargeBySellingPriceNet($sellingPriceNet, $purchasePriceNet);
        return $surcharge;

    }

    public function getSellingPricesWithSurcharge($productIds, $surcharge): array
    {

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $products = $productsTable->find('all',
            conditions: [
                'Products.id_product IN' => $productIds,
            ],
            contain: [
                'Taxes',
                'Manufacturers',
                'ProductAttributes.PurchasePriceProductAttributes',
                'ProductAttributes.UnitProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'PurchasePriceProducts.Taxes',
                'UnitProducts',
            ]
        );

        $preparedProductsForActionLog = [];
        $preparedProductData = [];

        foreach($products as $product) {

            $sellingPriceTaxRate = $product->tax->rate ?? 0;

            if (empty($product->purchase_price_product) || is_null($product->purchase_price_product->tax_id)) {
                continue;
            }

            $purchasePriceTaxRate = 0;
            if (!empty($product->purchase_price_product->tax)) {
                $purchasePriceTaxRate = $product->purchase_price_product->tax->rate;
            }

            if (empty($product->product_attributes)) {

                // main product

                $grossPrice = 0;
                if (!empty($product->purchase_price_product)) {
                    $grossPrice = $this->calculateSellingPriceGrossBySurcharge($product->purchase_price_product->price, $surcharge, $sellingPriceTaxRate);
                }

                $grossPricePerUnit = 0;
                if (!empty($product->unit_product) && $product->unit_product->price_per_unit_enabled) {
                    $purchasePriceNet = $productsTable->getNetPrice($product->unit_product->purchase_price_incl_per_unit, $purchasePriceTaxRate);
                    $grossPricePerUnit = $this->calculateSellingPriceGrossBySurcharge($purchasePriceNet, $surcharge, $sellingPriceTaxRate);
                }

                if ($grossPrice == 0 && $grossPricePerUnit == 0) {
                    continue;
                }

                $productId = $product->id_product;
                $preparedProductsForActionLog[] = '<b>' . $product->name . '</b>: ID ' . $product->id_product . ',  ' . $product->manufacturer->name;
                $preparedProductData[] = [
                    'product_id' => $productId,
                    'gross_price' => $grossPrice,
                    'price_incl_per_unit' => $grossPricePerUnit,
                    'price_per_unit_entity' => $product->unit_product,
                ];

            } else {

                foreach($product->product_attributes as $attribute) {

                    // attribute

                    $grossPrice = 0;
                    if (!empty($attribute->purchase_price_product_attribute)) {
                        $grossPrice = $this->calculateSellingPriceGrossBySurcharge($attribute->purchase_price_product_attribute->price, $surcharge, $sellingPriceTaxRate);
                    }

                    $grossPricePerUnit = 0;
                    if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                        $purchasePriceNet = $productsTable->getNetPrice($attribute->unit_product_attribute->purchase_price_incl_per_unit, $purchasePriceTaxRate);
                        $grossPricePerUnit = $this->calculateSellingPriceGrossBySurcharge($purchasePriceNet, $surcharge, $sellingPriceTaxRate);
                    }

                    if ($grossPrice == 0 && $grossPricePerUnit == 0) {
                        continue;
                    }

                    $productId = $product->id_product . '-' . $attribute->id_product_attribute;
                    $preparedProductsForActionLog[] = '<b>' . $product->name . ': ' . $attribute->product_attribute_combination->attribute->name . '</b>: ID ' . $productId . ',  ' . $product->manufacturer->name;
                    $preparedProductData[] = [
                        'product_id' => $productId,
                        'gross_price' => $grossPrice,
                        'price_incl_per_unit' => $grossPricePerUnit,
                        'price_per_unit_entity' => $attribute->unit_product_attribute,
                    ];

                }

            }

        }

        $pricesToChange = [];
        foreach($preparedProductData as $ppd) {
            $pricesToChange[] = [
                $ppd['product_id'] => [
                    'gross_price' => $ppd['gross_price'],
                    'unit_product_price_incl_per_unit' => $ppd['price_incl_per_unit'] > 0 ? $ppd['price_incl_per_unit'] : null,
                    'unit_product_price_per_unit_enabled' => !empty($ppd['price_per_unit_entity']) ? $ppd['price_per_unit_entity']->price_per_unit_enabled : 0,
                    'unit_product_name' => !empty($ppd['price_per_unit_entity']) ? $ppd['price_per_unit_entity']->name : null,
                    'unit_product_amount' => !empty($ppd['price_per_unit_entity']) ? $ppd['price_per_unit_entity']->amount : null,
                    'unit_product_quantity_in_units' => !empty($ppd['price_per_unit_entity']) ? $ppd['price_per_unit_entity']->quantity_in_units : null,
                    'unit_product_use_weight_as_amount' => !empty($ppd['price_per_unit_entity']) ? $ppd['price_per_unit_entity']->use_weight_as_amount : 0,
                ],
            ];
        }

        $result = [
            'pricesToChange' => $pricesToChange,
            'preparedProductsForActionLog' => $preparedProductsForActionLog,
        ];

        return $result;

    }

    public function savePurchasePriceTax($taxId, $productId, $oldProduct): array
    {
        $changedTaxInfoForMessage = [];
        $oldPurchasePriceTaxRate = 0;
        if (!empty($oldProduct->purchase_price_product) && !empty($oldProduct->purchase_price_product->tax)) {
            $oldPurchasePriceTaxRate = $oldProduct->purchase_price_product->tax->rate;
        }

        $taxesTable = TableRegistry::getTableLocator()->get('Taxes');
        $tax = $taxesTable->find('all',
            conditions: [
                'Taxes.id_tax' => $taxId,
            ]
        )->first();

        $taxRate = 0;
        if (! empty($tax)) {
            $taxRate = Configure::read('app.numberHelper')->formatTaxRate($tax->rate);
        }

        $entity2Save = $this->getEntityToSaveByProductId($productId);
        $patchedEntity = $this->patchEntity(
            $entity2Save,
            [
                'tax_id' => $taxId,
            ]
        );

        if ($patchedEntity->isDirty('tax_id')) {

            $productsTable = TableRegistry::getTableLocator()->get('Products');
            if (! empty($oldProduct->product_attributes)) {
                $pppaTable = TableRegistry::getTableLocator()->get('PurchasePriceProductAttributes');
                // update net price of all attributes
                foreach ($oldProduct->product_attributes as $attribute) {
                    if (!empty($attribute->purchase_price_product_attribute)) {
                        $newNetPrice = $productsTable->getNetPriceForNewTaxRate($attribute->purchase_price_product_attribute->price, $oldPurchasePriceTaxRate, $taxRate);
                        $entity2Save = $this->getEntityToSaveByProductAttributeId($attribute->id_product_attribute);
                        $entity2Save->price = $newNetPrice;
                        $pppaTable->save($entity2Save);
                    }
                }
            } else {
                // update net price of main product
                if (!empty($oldProduct->purchase_price_product)) {
                    $newNetPrice = $productsTable->getNetPriceForNewTaxRate($oldProduct->purchase_price_product->price, $oldPurchasePriceTaxRate, $taxRate);
                    $patchedEntity->price = $newNetPrice;
                }
            }

            $changedTaxInfoForMessage[] = [
                'label' => __('Purchase_price') . ': ',
                'oldTaxRate' => $oldPurchasePriceTaxRate,
                'newTaxRate' => $taxRate,
            ];
            $this->save($patchedEntity);
        }

        return $changedTaxInfoForMessage;

    }


}

<?php
declare(strict_types=1);

namespace App\Model\Traits;

use Cake\Core\Configure;
use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
use App\Model\Entity\Product;
use Cake\Utility\Hash;
use App\Services\CatalogService;
use Cake\ORM\Query\SelectQuery;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait GetProductsForBackendTrait
{

    public function getProductsForBackend(
        array|string $productIds,
        int|string $manufacturerId,
        string $active,
        string|array $categoryId = '',
        bool $addProductNameToAttributes = false,
        ?Controller $controller = null,
        ): array
    {

        $query = $this->getProductsForBackendQuery($productIds, $manufacturerId, $active, $categoryId, $controller);

        $i = 0;
        $preparedProducts = [];
        foreach ($query as $product) {
            $product->category = (object) [
                'names' => [],
                'all_products_found' => false
            ];
            foreach ($product->category_products as $category) {
                // assignment to "all products" has to be checked... otherwise show error message
                if ($category->id_category == Configure::read('app.categoryAllProducts')) {
                    $product->category->all_products_found = true;
                } else {
                    // sometimes associated category does not exist any more...
                    if (!empty($category->category)) {
                        $product->category->names[] = $category->category->name;
                    }
                }
            }
            $product->selected_categories = Hash::extract($product->category_products, '{n}.id_category');

            $taxRate = is_null($product->tax) ? 0 : $product->tax->rate;
            $product->gross_price = $this->getGrossPrice($product->price, $taxRate);

            $product->delivery_rhythm_string = Configure::read('app.htmlHelper')->getDeliveryRhythmString(
                $product->is_stock_product && $product->manufacturer->stock_management_enabled,
                $product->delivery_rhythm_type,
                $product->delivery_rhythm_count,
            );
            $product->last_order_weekday = Configure::read('app.timeHelper')->getWeekdayName(
                Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, $product->delivery_rhythm_send_order_list_weekday)
            );

            $rowClass = [];
            if (! $product->active) {
                $rowClass[] = 'deactivated';
            }

            if (!empty($product->deposit_product)) {
                $product->deposit = $product->deposit_product->deposit;
            } else {
                $product->deposit = 0;
            }

            if (!empty($product->image)) {
                $imageSrc = Configure::read('app.htmlHelper')->getProductImageSrc($product->image->id_image, 'home');
                $imageFile = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . $imageSrc);
                $imageFile = Configure::read('app.htmlHelper')->removeTimestampFromFile($imageFile);
                if ($imageFile != '' && !preg_match('/de-default-home/', $imageFile) && file_exists($imageFile)) {
                    $product->image->hash = sha1_file($imageFile);
                    $product->image->src = Configure::read('App.fullBaseUrl') . $imageSrc;
                }
            }

            // show unity only for main products
            $additionalProductNameInfos = [];
            if (empty($product->product_attributes) && $product->unity != '') {
                $additionalProductNameInfos[] = '<span class="unity-for-dialog">' . $product->unity . '</span>';
            }

            $product->price_is_zero = false;
            if (empty($product->product_attributes) && $product->gross_price == 0) {
                $product->price_is_zero = true;
            }
            $product->unit = [];
            if (empty($product->product_attributes) && !empty($product->unit_product)) {

                $product->unit = $product->unit_product;

                $quantityInUnitsString = Configure::read('app.pricePerUnitHelper')->getQuantityInUnitsWithWrapper($product->unit_product->price_per_unit_enabled, $product->unit_product->quantity_in_units, $product->unit_product->name);
                if ($quantityInUnitsString != '') {
                    $additionalProductNameInfos[] = $quantityInUnitsString;
                }

                if ($product->unit_product->price_per_unit_enabled) {
                    $product->price_is_zero = false;
                }

            }

            $product->unchanged_name = $product->name;

            $product->nameSetterMethodEnabled = false;
            $product->name = '<span class="product-name">' . $product->name . '</span>';
            if (!empty($additionalProductNameInfos)) {
                $product->name = $product->name . Product::NAME_SEPARATOR . join(', ', $additionalProductNameInfos);
            }
            $product->nameSetterMethodEnabled = true;

            if (empty($product->tax)) {
                $product->tax = (object) [
                    'rate' => 0,
                ];
            }

            $purchasePriceProductsTable = TableRegistry::getTableLocator()->get('PurchasePriceProducts');

            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {

                $product->purchase_price_is_zero = true;
                $product->purchase_price_is_set = $purchasePriceProductsTable->isPurchasePriceSet($product);

                if (empty($product->purchase_price_product) || $product->purchase_price_product->tax_id === null) {
                    $product->purchase_price_product = (object) [
                        'tax_id' => null,
                        'price' => null,
                        'tax' => [
                            'rate' => null,
                        ],
                    ];
                }
                if (!empty($product->purchase_price_product)) {

                    $purchasePriceTaxRate = $product->purchase_price_product->tax->rate ?? 0;
                    $purchasePrice = $product->purchase_price_product->price ?? null;
                    if ($purchasePrice === null) {
                        $product->purchase_gross_price = $purchasePrice;
                    } else {
                        $product->purchase_gross_price = $this->getGrossPrice($purchasePrice, $purchasePriceTaxRate);
                        if ($product->purchase_gross_price > 0) {
                            $product->purchase_price_is_zero = false;
                        }
                        $product->purchase_net_price = $purchasePrice;
                    }

                    if (!empty($product->unit) && $product->unit->price_per_unit_enabled) {
                        if (!is_null($product->unit->purchase_price_incl_per_unit)) {
                            $product->surcharge_percent = $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross(
                                Configure::read('app.pricePerUnitHelper')->getPricePerUnit($product->unit->price_incl_per_unit, $product->unit_product->quantity_in_units, $product->unit_product->amount),
                                $taxRate,
                                Configure::read('app.pricePerUnitHelper')->getPricePerUnit($product->unit->purchase_price_incl_per_unit, $product->unit_product->quantity_in_units, $product->unit_product->amount),
                                $purchasePriceTaxRate,
                            );
                            $priceInclPerUnitAndAmount = $this->getNetPrice($product->unit->price_incl_per_unit, $taxRate) * $product->unit_product->quantity_in_units / $product->unit_product->amount;
                            $purchasePriceInclPerUnitAndAmount = $this->getNetPrice($product->unit->purchase_price_incl_per_unit, $purchasePriceTaxRate) * $product->unit_product->quantity_in_units / $product->unit_product->amount;
                            $product->surcharge_price = $priceInclPerUnitAndAmount - $purchasePriceInclPerUnitAndAmount;
                            if ($purchasePriceInclPerUnitAndAmount > 0) {
                                $product->purchase_price_is_zero = false;
                            }
                        }
                    } else {
                        $product->surcharge_percent = $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross(
                            $product->gross_price,
                            $taxRate,
                            $product->purchase_gross_price,
                            $purchasePriceTaxRate,
                        );
                        $product->surcharge_price = $product->price - $purchasePrice;
                    }

                }
            }

            $rowClass[] = 'main-product';
            $rowIsOdd = false;
            if ($i % 2 == 0) {
                $rowIsOdd = true;
                $rowClass[] = 'custom-odd';
            }
            $product->row_class = join(' ', $rowClass);

            $preparedProducts[] = $product;
            $i ++;

            if (! empty($product->product_attributes)) {

                foreach ($product->product_attributes as $attribute) {

                    $grossPrice = 0;
                    if (! empty($attribute->price)) {
                        $grossPrice = $this->getGrossPrice($attribute->price, $taxRate);
                    }

                    $rowClass = [
                        'sub-row'
                    ];
                    if (! $product->active) {
                        $rowClass[] = 'deactivated';
                    }

                    if ($rowIsOdd) {
                        $rowClass[] = 'custom-odd';
                    }

                    $priceIsZero = false;
                    if ($grossPrice == 0) {
                        $priceIsZero = true;
                    }
                    if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                        $productName = Configure::read('app.pricePerUnitHelper')->getQuantityInUnitsStringForAttributes(
                            $attribute->product_attribute_combination->attribute->name,
                            $attribute->product_attribute_combination->attribute->can_be_used_as_unit,
                            $attribute->unit_product_attribute->price_per_unit_enabled,
                            $attribute->unit_product_attribute->quantity_in_units,
                            $attribute->unit_product_attribute->name
                        );
                        $priceIsZero = false;
                    } else {
                        $productName = $attribute->product_attribute_combination->attribute->name;
                        if ($addProductNameToAttributes) {
                            $productName = $product->name . ': ' . $productName;
                        }
                    }

                    $preparedProduct = [
                        'id_product' => $product->id_product . '-' . $attribute->id_product_attribute,
                        'gross_price' => $grossPrice,
                        'active' => $product->active,
                        'is_stock_product' => $product->is_stock_product,
                        'price_is_zero' => $priceIsZero,
                        'row_class' => join(' ', $rowClass),
                        'unchanged_name' => $product->unchanged_name,
                        'name' => $productName,
                        'description_short' => '',
                        'description' => '',
                        'unity' => '',
                        'manufacturer' => [
                            'name' => (!empty($product->manufacturer) ? $product->manufacturer->name : ''),
                            'stock_management_enabled' => (!empty($product->manufacturer) ? $product->manufacturer->stock_management_enabled : false),
                        ],
                        'default_on' => $attribute->default_on,
                        'stock_available' => [
                            'quantity' => $attribute->stock_available->quantity,
                            'quantity_limit' => $attribute->stock_available->quantity_limit,
                            'sold_out_limit' => $attribute->stock_available->sold_out_limit,
                            'always_available' => $attribute->stock_available->always_available,
                            'default_quantity_after_sending_order_lists' => $attribute->stock_available->default_quantity_after_sending_order_lists,
                        ],
                        'deposit' => !empty($attribute->deposit_product_attribute) ? $attribute->deposit_product_attribute->deposit : 0,
                        'unit' => !empty($attribute->unit_product_attribute) ? $attribute->unit_product_attribute : [],
                        'category' => [
                            'names' => [],
                            'all_products_found' => true
                        ],
                        'image' => null,
                        'barcode_product' => $attribute->barcode_product_attribute,
                    ];

                    if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
                        $attributeId = $attribute->id_product_attribute ?? 0;
                        $preparedProduct['system_bar_code'] = $product->system_bar_code . Configure::read('app.numberHelper')->addLeadingZerosToNumber((string) $attributeId, 4);
                        $preparedProduct['image'] = $product->image;
                        if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                            $preparedProduct['nameForBarcodePdf'] = $product->name . ': ' . $productName;
                        }
                    }

                    if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                        $purchasePriceProductAttributesTable = TableRegistry::getTableLocator()->get('PurchasePriceProductAttributes');
                        $preparedProduct['purchase_price_is_set'] = $purchasePriceProductAttributesTable->isPurchasePriceSet($attribute);
                        $preparedProduct['purchase_price_is_zero'] = true;

                        $purchasePrice = $attribute->purchase_price_product_attribute->price ?? null;
                        if ($purchasePrice === null) {
                            $preparedProduct['purchase_gross_price'] = $purchasePrice;
                        } else {
                            $preparedProduct['purchase_gross_price'] = $this->getGrossPrice($purchasePrice, $purchasePriceTaxRate);
                            if ($preparedProduct['purchase_gross_price'] > 0) {
                                $preparedProduct['purchase_price_is_zero'] = false;
                            }
                            $preparedProduct['purchase_net_price'] = $purchasePrice;
                        }

                        if (!empty($attribute->unit_product_attribute) && $attribute->unit_product_attribute->price_per_unit_enabled) {
                            if (!is_null($attribute->unit_product_attribute->purchase_price_incl_per_unit)) {
                                $preparedProduct['surcharge_percent'] = $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross(
                                    Configure::read('app.pricePerUnitHelper')->getPricePerUnit($attribute->unit_product_attribute->price_incl_per_unit, $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount),
                                    $taxRate,
                                    Configure::read('app.pricePerUnitHelper')->getPricePerUnit($attribute->unit_product_attribute->purchase_price_incl_per_unit, $attribute->unit_product_attribute->quantity_in_units, $attribute->unit_product_attribute->amount),
                                    $purchasePriceTaxRate,
                                );
                                $priceInclPerUnitAndAmount = $this->getNetPrice($attribute->unit_product_attribute->price_incl_per_unit, $taxRate) * $attribute->unit_product_attribute->quantity_in_units / $attribute->unit_product_attribute->amount;
                                $purchasePriceInclPerUnitAndAmount = $this->getNetPrice($attribute->unit_product_attribute->purchase_price_incl_per_unit, $purchasePriceTaxRate) * $attribute->unit_product_attribute->quantity_in_units / $attribute->unit_product_attribute->amount;
                                $preparedProduct['surcharge_price'] = $priceInclPerUnitAndAmount - $purchasePriceInclPerUnitAndAmount;
                                if ($purchasePriceInclPerUnitAndAmount > 0) {
                                    $preparedProduct['purchase_price_is_zero'] = false;
                                }
                            }
                        } else {
                            $preparedProduct['surcharge_percent'] = $purchasePriceProductsTable->calculateSurchargeBySellingPriceGross(
                                $grossPrice,
                                $taxRate,
                                $preparedProduct['purchase_gross_price'],
                                $purchasePriceTaxRate,
                            );
                            $preparedProduct['surcharge_price'] = $attribute->price - $purchasePrice;
                        }


                    }
                    $preparedProducts[] = $preparedProduct;
                }
            }
        }

        $preparedProducts = json_decode(json_encode($preparedProducts), false); // convert array recursively into object
        return $preparedProducts;
    }

    private function getProductsForBackendQuery(
        array|string $productIds,
        int|string $manufacturerId,
        string $active,
        string|array $categoryId = '',
        ?Controller $controller = null,
    ): SelectQuery {

        $conditions = [];
        if ($manufacturerId != 'all') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Products.id_manufacturer > 0';
        }

        if ($productIds != '') {
            $conditions['Products.id_product IN'] = $productIds;
        }

        if ($active != 'all') {
            $conditions['Products.active'] = $active;
        } else {
            $conditions['Products.active >'] = APP_DEL;
        }

        $contain = [
            'CategoryProducts',
            'CategoryProducts.Categories',
            'DepositProducts',
            'Images',
            'Taxes',
            'UnitProducts',
            'Manufacturers',
            'StockAvailables' => [
                'conditions' => [
                    'StockAvailables.id_product_attribute' => 0
                ]
            ],
            'ProductAttributes',
            'ProductAttributes.StockAvailables' => [
                'conditions' => [
                    'StockAvailables.id_product_attribute > 0'
                ]
            ],
            'ProductAttributes.DepositProductAttributes',
            'ProductAttributes.UnitProductAttributes',
            'ProductAttributes.ProductAttributeCombinations.Attributes'
        ];

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $contain[] = 'PurchasePriceProducts.Taxes';
            $contain[] = 'ProductAttributes.PurchasePriceProductAttributes';
        }

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $contain[] = 'BarcodeProducts';
            $contain[] = 'ProductAttributes.BarcodeProductAttributes';
        }

        $order = [
            'Products.active' => 'DESC',
            'Products.name' => 'ASC'
        ];

        $query = $this->find('all',
        conditions: $conditions,
        contain: $contain,
        order: $controller === null ? $order : null);

        if ($categoryId != '') {
            $query->matching('CategoryProducts', function ($q) use ($categoryId) {
                return $q->where(['id_category IN' => $categoryId]);
            });
        }

        $depositProductsTable = TableRegistry::getTableLocator()->get('DepositProducts');
        $stockAvailablesTable = TableRegistry::getTableLocator()->get('StockAvailables');
        $purchasePriceProductsTable = TableRegistry::getTableLocator()->get('PurchasePriceProducts');
        $barcodeProductsTable = TableRegistry::getTableLocator()->get('BarcodeProducts');
        $taxesTable = TableRegistry::getTableLocator()->get('Taxes');
        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        $unitProductsTable = TableRegistry::getTableLocator()->get('UnitProducts');

        $query
        ->select('Products.id_product')->distinct()
        ->select($this) // Products
        ->select($depositProductsTable)
        ->select('Images.id_image')
        ->select($taxesTable)
        ->select($manufacturersTable)
        ->select($unitProductsTable)
        ->select($stockAvailablesTable);

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $query->select($purchasePriceProductsTable);
        }

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $catalogService = new CatalogService();
            $query->select(['system_bar_code' => $catalogService->getProductIdentifierField()]);
            $query->select($barcodeProductsTable);
        }

        if ($controller) {
            $query = $controller->paginate($query, [
                'sortableFields' => [
                    'Images.id_image', 'Products.name', 'Products.is_declaration_ok', 'Taxes.rate', 'Products.active', 'Manufacturers.name', 'Products.is_stock_product'
                ],
                'order' => $order
            ]);
        }

        return $query;
    }

}
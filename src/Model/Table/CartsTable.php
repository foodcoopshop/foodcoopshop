<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

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
class CartsTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_cart');
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->hasMany('CartProducts', [
            'foreignKey' => 'id_cart'
        ]);
        $this->hasMany('PickupDayEntities', [
            'className' => 'PickupDays', // field has same name and would clash
            'foreignKey' => [
                'id_customer'
            ]
        ]);
        $this->addBehavior('Timestamp');
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->equals('cancellation_terms_accepted', 1, __('Please_accept_the_information_about_right_of_withdrawal.'));
        $validator->equals('general_terms_and_conditions_accepted', 1, __('Please_accept_the_general_terms_and_conditions.'));
        return $validator;
    }
    

    public function getProductNameWithUnity($productName, $unity)
    {
        return $productName . ($unity != '' ? ' : ' . $unity : '');
    }

    public function adaptCartWithTimebasedCurrency($cart, $selectedTimeAdaptionFactor)
    {

        $cartProductSum = 0;
        $cartProductSumExcl = 0;
        $cartProductSecondsSum = 0;
        foreach($cart['CartProducts'] as &$cartProduct) {
            if (isset($cartProduct['timebasedCurrencySeconds'])) {
                $calculatedSeconds = round($cartProduct['timebasedCurrencySeconds'] * $selectedTimeAdaptionFactor, 0);
                $cartProduct['timebasedCurrencySeconds'] = $calculatedSeconds;
                $cartProductSecondsSum += $cartProduct['timebasedCurrencySeconds'];
                $cartProduct['isTimebasedCurrencyUsed'] = true;
            }
            if (isset($cartProduct['timebasedCurrencyMoneyIncl'])) {
                $cartProduct['timebasedCurrencyMoneyIncl'] = round($cartProduct['timebasedCurrencyMoneyIncl'] * $selectedTimeAdaptionFactor, 2);
                $cartProduct['price'] -= $cartProduct['timebasedCurrencyMoneyIncl'];
                $cartProductSum += $cartProduct['price'];
            }
            if (isset($cartProduct['timebasedCurrencyMoneyExcl'])) {
                $cartProduct['timebasedCurrencyMoneyExcl'] = round($cartProduct['timebasedCurrencyMoneyExcl'] *  $selectedTimeAdaptionFactor, 2);
                $cartProduct['priceExcl'] -= $cartProduct['timebasedCurrencyMoneyExcl'];
                $cartProductSumExcl += $cartProduct['priceExcl'];
            }
        }

        $cart['CartTimebasedCurrencyUsed'] = true;
        $cart['CartTimebasedCurrencySecondsSum'] = $cartProductSecondsSum;
        $cart['CartProductSum'] = $cartProductSum;
        $cart['CartProductSumExcl'] = $cartProductSumExcl;

        return $cart;
    }

    /**
     * @param int $customerId
     * @return array
     */
    public function getCart($customerId, $instantOrderMode=false)
    {
		
        $cart = $this->find('all', [
            'conditions' => [
                'Carts.status' => APP_ON,
                'Carts.id_customer' => $customerId
            ]
        ])->first();

        if (empty($cart)) {
            $cart2save = [
                'id_customer' => $customerId
            ];
            $cart = $this->save($this->newEntity($cart2save));
        }

        $cartProductsTable = TableRegistry::getTableLocator()->get('CartProducts');
        $cartProducts = $cartProductsTable->find('all', [
            'conditions' => [
                'CartProducts.id_cart' => $cart['id_cart']
            ],
            'order' => [
                'Products.name' => 'ASC'
            ],
            'contain' => [
                'OrderDetails',
                'Products.Manufacturers',
                'Products.DepositProducts',
                'Products.UnitProducts',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'ProductAttributes.DepositProductAttributes',
                'ProductAttributes.UnitProductAttributes',
                'Products.Images'
            ]
        ])->toArray();
        
        
        if (!empty($cartProducts)) {
            $cart->pickup_day_entities = $this->CartProducts->setPickupDays($cartProducts, $customerId, $instantOrderMode);
        }
        
        $preparedCart = [
            'Cart' => $cart,
            'CartProducts' => []
        ];

        foreach ($cartProducts as &$cartProduct) {

            $imageId = 0;
            if (!empty($cartProduct->product->image)) {
                $imageId = $cartProduct->product->image->id_image;
            }

            if (!empty($cartProduct->product_attribute->product_attribute_combination)) {
                $productData = $this->prepareProductAttribute($cartProduct);
            } else {
                $productData = $this->prepareMainProduct($cartProduct);
            }

            $productImage = Configure::read('app.htmlHelper')->image(Configure::read('app.htmlHelper')->getProductImageSrc($imageId, 'home'));
            $productLink = Configure::read('app.htmlHelper')->link(
                $cartProduct->product->name,
                Configure::read('app.slugHelper')->getProductDetail(
                    $cartProduct->id_product,
                    $cartProduct->product->name
                ),
                ['class' => 'product-name']
            );
            $manufacturerLink = Configure::read('app.htmlHelper')->link($cartProduct->product->manufacturer->name, Configure::read('app.slugHelper')->getManufacturerDetail($cartProduct->product->id_manufacturer, $cartProduct->product->manufacturer->name));
            $productData['image'] = $productImage;
            $productData['productLink'] = $productLink;
            $productData['manufacturerLink'] = $manufacturerLink;

            $preparedCart['CartProducts'][] = $productData;

        }

        // sum up deposits and products
        $preparedCart['ProductsWithUnitCount'] = $this->getProductsWithUnitCount($preparedCart['CartProducts']);
        $preparedCart['CartDepositSum'] = 0;
        $preparedCart['CartProductSum'] = 0;
        $preparedCart['CartProductSumExcl'] = 0;
        $preparedCart['CartTaxSum'] = 0;
        $preparedCart['CartTimebasedCurrencyMoneyExclSum'] = 0;
        $preparedCart['CartTimebasedCurrencyMoneyInclSum'] = 0;
        $preparedCart['CartTimebasedCurrencySecondsSum'] = 0;
        foreach ($preparedCart['CartProducts'] as $p) {
            $preparedCart['CartDepositSum'] += $p['deposit'];
            $preparedCart['CartProductSum'] += $p['price'];
            $preparedCart['CartTaxSum'] += $p['tax'];
            $preparedCart['CartProductSumExcl'] += $p['priceExcl'];
            if (!empty($p['timebasedCurrencyMoneyExcl'])) {
                $preparedCart['CartTimebasedCurrencyMoneyExclSum'] += $p['timebasedCurrencyMoneyExcl'];
            }
            if (!empty($p['timebasedCurrencyMoneyIncl'])) {
                $preparedCart['CartTimebasedCurrencyMoneyInclSum'] += $p['timebasedCurrencyMoneyIncl'];
            }
            if (!empty($p['timebasedCurrencySeconds'])) {
                $preparedCart['CartTimebasedCurrencySecondsSum'] += $p['timebasedCurrencySeconds'];
            }
        }
        return $preparedCart;
    }

    private function addTimebasedCurrencyProductData($productData, $cartProduct, $grossPricePerPiece, $netPricePerPiece)
    {
        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->getLoggedUser()['timebased_currency_enabled']) {
            if ($manufacturersTable->getOptionTimebasedCurrencyEnabled($cartProduct->product->manufacturer->timebased_currency_enabled)) {
                $productData['timebasedCurrencyMoneyIncl'] = round($manufacturersTable->getTimebasedCurrencyMoney($grossPricePerPiece, $cartProduct->product->manufacturer->timebased_currency_max_percentage), 2) * $cartProduct->amount;
                $productData['timebasedCurrencyMoneyExcl'] = round($manufacturersTable->getTimebasedCurrencyMoney($netPricePerPiece, $cartProduct->product->manufacturer->timebased_currency_max_percentage), 2) * $cartProduct->amount;
                $productData['timebasedCurrencySeconds'] = $manufacturersTable->getCartTimebasedCurrencySeconds($grossPricePerPiece, $cartProduct->product->manufacturer->timebased_currency_max_percentage) * $cartProduct->amount;
            }
        }
        return $productData;

    }

    /**
     * @param array $productData
     * @return number
     */
    private function getProductsWithUnitCount($productData)
    {
        $count = 0;
        foreach($productData as $product) {
            if (isset($product['usesQuantityInUnits']) && $product['usesQuantityInUnits']) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @param CartProductsTable $cartProduct
     * @return array
     */
    private function prepareMainProduct($cartProduct)
    {

        $productsTable = TableRegistry::getTableLocator()->get('Products');

        $netPricePerPiece = $cartProduct->product->price;
        $grossPricePerPiece = $productsTable->getGrossPrice($cartProduct->id_product, $netPricePerPiece);
        $grossPrice = $grossPricePerPiece * $cartProduct->amount;
        $tax = $productsTable->getUnitTax($grossPrice, $netPricePerPiece, $cartProduct->amount) * $cartProduct->amount;
        $productData = [
            'cartProductId' => $cartProduct->id_cart_product,
            'productId' => $cartProduct->id_product,
            'productName' => $cartProduct->product->name,
            'amount' => $cartProduct->amount,
            'manufacturerId' => $cartProduct->product->id_manufacturer,
            'manufacturerName' => $cartProduct->product->manufacturer->name,
            'price' => $grossPrice,
            'priceExcl' => $cartProduct->product->price * $cartProduct->amount,
            'tax' => $tax,
            'pickupDay' => $cartProduct->pickup_day
        ];
        
        $deposit = 0;
        if (!empty($cartProduct->product->deposit_product->deposit)) {
            $deposit = $cartProduct->product->deposit_product->deposit * $cartProduct->amount;
        }
        $productData['deposit'] = $deposit;

        $unitName = '';
        $unitAmount = 0;
        $priceInclPerUnit = 0;
        $quantityInUnits = 0;
        $productQuantityInUnits = 0;
        $unity = $cartProduct->product->unity;
        $productData['unity'] = $unity;
        if (!empty($cartProduct->product->unit_product) && $cartProduct->product->unit_product->price_per_unit_enabled) {
            $unitName = $cartProduct->product->unit_product->name;
            $unitAmount = $cartProduct->product->unit_product->amount;
            $priceInclPerUnit = $cartProduct->product->unit_product->price_incl_per_unit;
            $quantityInUnits = $cartProduct->product->unit_product->quantity_in_units;
            $newPriceIncl = round($priceInclPerUnit * $quantityInUnits / $unitAmount, 2);
            $netPricePerPiece = round($productsTable->getNetPrice($cartProduct->id_product, $newPriceIncl), 2);
            $productData['price'] =  $newPriceIncl * $cartProduct->amount;
            $productData['priceExcl'] = $netPricePerPiece * $cartProduct->amount;
            $productData['tax'] = ($newPriceIncl - $netPricePerPiece) * $cartProduct->amount;
            if ($unity != '') {
                $unity .= ', ';
            }
            $unity .=  Configure::read('app.pricePerUnitHelper')->getQuantityInUnits($cartProduct->product->unit_product->price_per_unit_enabled, $quantityInUnits, $unitName, $cartProduct->amount);
            $productData['usesQuantityInUnits'] = true;
        }
        $productData['unity_with_unit'] = $unity;
        $productData['unitName'] = $unitName;
        $productData['unitAmount'] = $unitAmount;
        $productData['priceInclPerUnit'] = $priceInclPerUnit;
        $productData['productQuantityInUnits'] = $quantityInUnits * $cartProduct->amount;
        $productData['quantityInUnits'] = $quantityInUnits;

        $productData = $this->addTimebasedCurrencyProductData($productData, $cartProduct, $grossPricePerPiece, $netPricePerPiece);
        
        return $productData;

    }

    /**
     * @param CartProductsTable $cartProduct
     * @return array
     */
    private function prepareProductAttribute($cartProduct)
    {

        $productsTable = TableRegistry::getTableLocator()->get('Products');

        $netPricePerPiece = $cartProduct->product_attribute->price;
        $grossPricePerPiece = $productsTable->getGrossPrice($cartProduct->id_product, $netPricePerPiece);
        $grossPrice = $grossPricePerPiece * $cartProduct->amount;
        $tax = $productsTable->getUnitTax($grossPrice, $netPricePerPiece, $cartProduct->amount) * $cartProduct->amount;

        $productData = [
            'cartProductId' => $cartProduct->id_cart_product,
            'productId' => $cartProduct->id_product . '-' . $cartProduct->id_product_attribute,
            'productName' => $cartProduct->product->name,
            'amount' => $cartProduct->amount,
            'manufacturerId' => $cartProduct->product->id_manufacturer,
            'manufacturerName' => $cartProduct->product->manufacturer->name,
            'price' => $grossPrice,
            'priceExcl' => $cartProduct->product_attribute->price * $cartProduct->amount,
            'tax' => $tax,
            'pickupDay' => $cartProduct->pickup_day
        ];

        $deposit = 0;
        if (!empty($cartProduct->product_attribute->deposit_product_attribute->deposit)) {
            $deposit = $cartProduct->product_attribute->deposit_product_attribute->deposit * $cartProduct->amount;
        }
        $productData['deposit'] = $deposit;

        $unitName = '';
        $unityName = '';
        $unitAmount = 0;
        $priceInclPerUnit = 0;
        $quantityInUnits = 0;
        $productQuantityInUnits = 0;

        if (!empty($cartProduct->product_attribute->unit_product_attribute) && $cartProduct->product_attribute->unit_product_attribute->price_per_unit_enabled) {
            $unitName = $cartProduct->product_attribute->unit_product_attribute->name;
            if (!$cartProduct->product_attribute->product_attribute_combination->attribute->can_be_used_as_unit) {
                $unityName = $cartProduct->product_attribute->product_attribute_combination->attribute->name;
            }
            $unitAmount = $cartProduct->product_attribute->unit_product_attribute->amount;
            $priceInclPerUnit = $cartProduct->product_attribute->unit_product_attribute->price_incl_per_unit;
            $quantityInUnits = $cartProduct->product_attribute->unit_product_attribute->quantity_in_units;
            $newPriceIncl = round($priceInclPerUnit * $quantityInUnits / $unitAmount, 2);
            $netPricePerPiece = round($productsTable->getNetPrice($cartProduct->id_product, $newPriceIncl), 2);
            $productData['price'] =  $newPriceIncl * $cartProduct->amount;
            $productData['priceExcl'] =  $netPricePerPiece * $cartProduct->amount;
            $productData['tax'] = ($newPriceIncl - $netPricePerPiece) * $cartProduct->amount;
            $unity = Configure::read('app.pricePerUnitHelper')->getQuantityInUnitsStringForAttributes(
                $cartProduct->product_attribute->product_attribute_combination->attribute->name,
                $cartProduct->product_attribute->product_attribute_combination->attribute->can_be_used_as_unit,
                $cartProduct->product_attribute->unit_product_attribute->price_per_unit_enabled,
                $quantityInUnits,
                $unitName,
                $cartProduct->amount
            );
            $productData['usesQuantityInUnits'] = true;
        } else {
            $unity = $cartProduct->product_attribute->product_attribute_combination->attribute->name;
            $unityName = $unity;
        }
        $productData['unity'] = $unityName;
        $productData['unity_with_unit'] = $unity;
        $productData['unitName'] = $unitName;
        $productData['unitAmount'] = $unitAmount;
        $productData['priceInclPerUnit'] = $priceInclPerUnit;
        $productData['productQuantityInUnits'] = $quantityInUnits * $cartProduct->amount;
        $productData['quantityInUnits'] = $quantityInUnits;

        $productData = $this->addTimebasedCurrencyProductData($productData, $cartProduct, $grossPricePerPiece, $netPricePerPiece);

        return $productData;

    }

}

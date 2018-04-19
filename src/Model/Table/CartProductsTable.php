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
class CartProductsTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_cart_product');
        $this->belongsTo('Products', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('ProductLangs', [
            'foreignKey' => ['id_product']
        ]);
        $this->belongsTo('ProductAttributes', [
            'foreignKey' => 'id_product_attribute'
        ]);
        $this->addBehavior('Timestamp');
    }
    
    /**
     * @param int $productId
     * @param int $attributeId
     * @param int $amount
     * @return array || boolean
     */
    public function add($appAuth, $productId, $attributeId, $amount)
    {
        
        $initialProductId = $this->Products->getCompositeProductIdAndAttributeId($productId, $attributeId);
        
        // allow -1 and 1 - 99
        if ($amount == 0 || $amount < - 1 || $amount > 99) {
            $message = 'Die gewünschte Anzahl "' . $amount . '" ist nicht gültig.';
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }
        
        // get product data from database
        $product = $this->Products->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs',
                'StockAvailables',
                'ProductAttributes',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        ])->first();
        
        $existingCartProduct = $appAuth->Cart->getProduct($initialProductId);
        $combinedAmount = $amount;
        if ($existingCartProduct) {
            $combinedAmount = $existingCartProduct['amount'] + $amount;
        }
        // check if passed product exists
        if (empty($product)) {
            $message = 'Das Produkt mit der ID ' . $productId . ' ist nicht vorhanden.';
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }
        
        // stock available check for product
        if ($attributeId == 0 && $product->stock_available->quantity < $combinedAmount && $amount > 0) {
            $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') des Produktes "' . $product->product_lang->name . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $product->stock_available->quantity;
            return [
                'status' => 0,
                'msg' => $message,
                'productId' => $initialProductId
            ];
        }
        
        // check if passed optional product/attribute relation exists
        if ($attributeId > 0) {
            $attributeIdFound = false;
            foreach ($product->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $attributeId) {
                    $attributeIdFound = true;
                    // stock available check for attribute
                    if ($attribute->stock_available->quantity < $combinedAmount && $amount > 0) {
                        $message = 'Die gewünschte Anzahl (' . $combinedAmount . ') der Variante "' . $attribute->product_attribute_combination->attribute->name . '" des Produktes "' . $product->product_lang->name . '" ist leider nicht mehr verfügbar. Verfügbare Menge: ' . $attribute->stock_available->quantity;
                        return [
                            'status' => 0,
                            'msg' => $message,
                            'productId' => $initialProductId
                        ];
                    }
                    break;
                }
            }
            if (! $attributeIdFound) {
                $message = 'Die Variante existiert nicht: ' . $initialProductId;
                return [
                    'status' => 0,
                    'msg' => $message,
                    'productId' => $initialProductId
                ];
            }
        }
        
        // update amount if cart product already exists
        $cart = $appAuth->getCart();
        $appAuth->setCart($cart);
        $cartProductTable = TableRegistry::get('CartProducts');
        
        $cartProduct2save = [
            'id_product' => $productId,
            'amount' => $combinedAmount,
            'id_product_attribute' => $attributeId,
            'id_cart' => $cart['Cart']['id_cart']
        ];
        if ($existingCartProduct) {
            $oldEntity = $cartProductTable->get($existingCartProduct['cartProductId']);
            $entity = $cartProductTable->patchEntity($oldEntity, $cartProduct2save);
        } else {
            $entity = $cartProductTable->newEntity($cartProduct2save);
        }
        $cartProductTable->save($entity);
        
        return true;
        
    }

    public function remove($productId, $attributeId, $cartId)
    {
        $cartProduct2remove = [
            'CartProducts.id_product' => $productId,
            'CartProducts.id_product_attribute' => $attributeId,
            'CartProducts.id_cart' => $cartId
        ];
        return $this->deleteAll($cartProduct2remove);
    }
}

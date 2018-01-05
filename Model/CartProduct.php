<?php
/**
 * CartProduct
 *
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
class CartProduct extends AppModel
{

    public $primaryKey = 'id_cart_product';

    public $actsAs = array(
        'Content'
    );

    public $belongsTo = array(
        'Product' => array(
            'foreignKey' => 'id_product'
        ),
        'ProductLang' => array(
            'foreignKey' => 'id_product'
        ),
        'ProductAttribute' => array(
            'foreignKey' => 'id_product_attribute'
        )
    );

    public function remove($productId, $attributeId, $cartId)
    {
        $cartProduct2remove = array(
            'CartProduct.id_product' => $productId,
            'CartProduct.id_product_attribute' => $attributeId,
            'CartProduct.id_cart' => $cartId
        );
        return $this->deleteAll($cartProduct2remove, false);
    }
}

<?php
/**
 * CartComponent
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
class CartComponent extends Component
{

    public $cakeCart = null;

    public function getProducts()
    {
        if ($this->cakeCart !== null) {
            return $this->cakeCart['CakeCartProducts'];
        }
        return null;
    }

    public function getProductAndDepositSum()
    {
        return $this->getProductSum() + $this->getDepositSum();
    }
    
    public function getTaxSum()
    {
        if ($this->cakeCart !== null) {
            return $this->cakeCart['CakeCartTaxSum'];
        }
        return 0;
    }
    
    public function getDepositSum()
    {
        if ($this->cakeCart !== null) {
            return $this->cakeCart['CakeCartDepositSum'];
        }
        return 0;
    }

    public function getProductSum()
    {
        if ($this->cakeCart !== null) {
            return $this->cakeCart['CakeCartProductSum'];
        }
        return 0;
    }

    public function getProductSumExcl()
    {
        if ($this->cakeCart !== null) {
            return $this->cakeCart['CakeCartProductSumExcl'];
        }
        return 0;
    }

    public function getCartId()
    {
        return $this->cakeCart['CakeCart']['id_cart'];
    }

    public function markAsSaved()
    {
        if ($this->cakeCart !== null) {
            $cc = ClassRegistry::init('CakeCart');
            $cc->id = $this->getCartId();
            $cc->save(array(
                'status' => APP_OFF
            ));
        }
    }

    /**
     *
     * @param string $productId
     *            - possible value: 34-423 (productId, attributeId)
     */
    public function getProduct($productId)
    {
        foreach ($this->getProducts() as $product) {
            if ($product['productId'] == $productId) {
                return $product;
                break;
            }
        }
        return false;
    }
}

?>
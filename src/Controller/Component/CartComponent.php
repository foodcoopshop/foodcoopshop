<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class CartComponent extends Component
{

    public $cart = null;

    public function getProducts()
    {
        if ($this->cart !== null) {
            return $this->cart['CartProducts'];
        }
        return null;
    }

    public function getProductsWithUnitCount()
    {
        if ($this->cart !== null) {
            return $this->cart['ProductsWithUnitCount'];
        }
        return 0;
    }

    public function getProductAndDepositSum()
    {
        return $this->getProductSum() + $this->getDepositSum();
    }

    public function getTimebasedCurrencyMoneyInclSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartTimebasedCurrencyMoneyInclSum'];
        }
        return 0;
    }

    public function isTimebasedCurrencyUsed()
    {
        return isset($this->cart['CartTimebasedCurrencyUsed']) && $this->cart['CartTimebasedCurrencyUsed'];
    }

    public function getTimebasedCurrencyMoneyExclSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartTimebasedCurrencyMoneyExclSum'];
        }
        return 0;
    }

    /**
     * avoids rounding errors
     * @return number
     */
    public function getTimebasedCurrencySecondsSumRoundedUp()
    {
        return round($this->getTimebasedCurrencySecondsSum() * 1.05, 0);
    }

    public function getTimebasedCurrencySecondsSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartTimebasedCurrencySecondsSum'];
        }
        return 0;
    }

    public function getTaxSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartTaxSum'];
        }
        return 0;
    }

    public function getDepositSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartDepositSum'];
        }
        return 0;
    }

    public function getProductSum()
    {
        if ($this->cart !== null) {
            return $this->cart['CartProductSum'];
        }
        return 0;
    }

    public function getProductSumExcl()
    {
        if ($this->cart !== null) {
            return $this->cart['CartProductSumExcl'];
        }
        return 0;
    }

    public function getCartId()
    {
        return $this->cart['Cart']->id_cart;
    }

    public function markAsSaved()
    {
        if ($this->cart === null) {
            return false;
        }
        $cc = TableRegistry::getTableLocator()->get('Carts');
        $patchedEntity = $cc->patchEntity(
            $cc->get($this->getCartId()), [
                'status' => APP_OFF
            ]
        );
        $cc->save($patchedEntity);
        return $patchedEntity;
    }
    
    public function getUniqueManufacturers()
    {
        $manufactures = [];
        foreach ($this->getProducts() as $product) {
            $manufactures[$product['manufacturerId']] = [
                'name' => $product['manufacturerName']
            ];
        }
        return $manufactures;
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

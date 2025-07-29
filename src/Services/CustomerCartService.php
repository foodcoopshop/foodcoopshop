<?php
declare(strict_types=1);

namespace App\Services;

use App\Model\Entity\Customer;
use App\Model\Entity\Cart;
use App\Services\OrderCustomerService;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\ORM\Query\SelectQuery;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CustomerCartService
{
    private Customer $customer;
    
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param array<string, mixed> $cart
     */
    public function setCart(array $cart): void
    {
        $this->customer->cart = $cart;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCart(): array
    {
        $cartType = $this->getCartType();
        $cartsTable = TableRegistry::getTableLocator()->get('Carts');
        return $cartsTable->getCart($this->customer, $cartType);
    }

    public function getCartType(): int
    {
        $cartType = Cart::TYPE_WEEKLY_RHYTHM;
        if (OrderCustomerService::isOrderForDifferentCustomerMode()) {
            $cartType = Cart::TYPE_INSTANT_ORDER;
        }
        if (OrderCustomerService::isSelfServiceModeByUrl() || OrderCustomerService::isSelfServiceModeByReferer()) {
            $cartType = Cart::TYPE_SELF_SERVICE;
        }
        return $cartType;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProducts(): array
    {
        if ($this->customer->cart !== null && isset($this->customer->cart['CartProducts']) && is_array($this->customer->cart['CartProducts'])) {
            return $this->customer->cart['CartProducts'];
        }
        return [];
    }

    public function getProductsWithUnitCount(): int
    {
        if ($this->customer->cart !== null && isset($this->customer->cart['ProductsWithUnitCount'])) {
            return (int) $this->customer->cart['ProductsWithUnitCount'];
        }
        return 0;
    }

    public function getProductAndDepositSum(): float
    {
        return $this->getProductSum() + $this->getDepositSum();
    }

    public function getTaxSum(): float
    {
        if ($this->customer->cart !== null && isset($this->customer->cart['CartTaxSum'])) {
            return (float) $this->customer->cart['CartTaxSum'];
        }
        return 0.0;
    }

    public function getDepositSum(): float
    {
        if ($this->customer->cart !== null && isset($this->customer->cart['CartDepositSum'])) {
            return (float) $this->customer->cart['CartDepositSum'];
        }
        return 0.0;
    }

    public function getProductSum(): float
    {
        if ($this->customer->cart !== null && isset($this->customer->cart['CartProductSum'])) {
            return (float) $this->customer->cart['CartProductSum'];
        }
        return 0.0;
    }

    public function getProductSumExcl(): float
    {
        if ($this->customer->cart !== null && isset($this->customer->cart['CartProductSumExcl'])) {
            return (float) $this->customer->cart['CartProductSumExcl'];
        }
        return 0.0;
    }

    public function getCartId(): int
    {
        if ($this->customer->cart === null || !isset($this->customer->cart['Cart'])) {
            throw new \RuntimeException('Cart is not loaded');
        }
        return $this->customer->cart['Cart']->id_cart;
    }

    public function markCartAsSaved(): Cart|false
    {
        if ($this->customer->cart === null) {
            return false;
        }
        $cc = TableRegistry::getTableLocator()->get('Carts');
        $patchedEntity = $cc->patchEntity(
            $cc->get($this->getCartId()), [
                'status' => APP_OFF,
            ],
            ['validate' => false],
        );
        $savedCart = $cc->save($patchedEntity);
        return $savedCart;
    }

    /**
     * @return array<int|string, array<string, string>>
     */
    public function getUniqueManufacturers(): array
    {
        $manufactures = [];
        foreach ($this->getProducts() as $product) {
            if (isset($product['manufacturerId'], $product['manufacturerName'])) {
                $manufactures[$product['manufacturerId']] = [
                    'name' => $product['manufacturerName']
                ];
            }
        }
        return $manufactures;
    }

    /**
     * @return array<string, mixed>|false
     */
    public function getProduct(int|string $productId): array|false
    {
        foreach ($this->getProducts() as $product) {
            if (isset($product['productId']) && $product['productId'] == $productId) {
                return $product;
            }
        }
        return false;
    }

    public function isCartEmpty(): bool
    {
        return empty($this->getProducts());
    }

    public function getCreditBalanceMinusCurrentCartSum(): float
    {
        $cart = $this->getCart();
        $productSum = isset($cart['CartProductSum']) ? (float) $cart['CartProductSum'] : 0.0;
        $depositSum = isset($cart['CartDepositSum']) ? (float) $cart['CartDepositSum'] : 0.0;
        return $this->customer->getCreditBalance() - $productSum - $depositSum;
    }

    public function hasEnoughCreditForProduct(float $grossPrice): bool
    {
        return $this->getCreditBalanceMinusCurrentCartSum() - (float) Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE') >= $grossPrice;
    }
}
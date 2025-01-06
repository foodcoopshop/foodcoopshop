<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\IdentityInterface;
use Cake\Core\Configure;
use App\Services\OrderCustomerService;
use App\Model\Entity\Cart;
use ArrayAccess;
use Cake\ORM\TableRegistry;
use Cake\ORM\Query\SelectQuery;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Customer extends AppEntity implements IdentityInterface
{

    public ?array $cart;
    protected array $_virtual = ['name', 'manufacturer'];
    protected array $_hidden = ['passwd'];

    const GROUP_SELF_SERVICE_CUSTOMER = 2;
    const GROUP_MEMBER = 3;
    const GROUP_ADMIN = 4;
    const GROUP_SUPERADMIN = 5;

    const SELLING_PRICE = 'SP';
    const PURCHASE_PRICE = 'PP';
    const ZERO_PRICE = 'ZP';

    private Manufacturer|string|null $_manufacturer = 'not-yet-loaded';

    public function getIdentifier(): array|string|int|null
    {
        return $this->id_customer;
    }

    public function getOriginalData(): static
    {
        return $this;
    }

    protected function _getManufacturer(): ?Manufacturer
    {
        if ($this->isNew()) {
            return null;
        }

        if ($this->_manufacturer === 'not-yet-loaded') {
            $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
            $this->_manufacturer = $manufacturersTable->find('all',
                conditions: [
                    'AddressManufacturers.email' => $this->email,
                    'AddressManufacturers.id_manufacturer > ' . APP_OFF,
                ],
                contain: [
                    'AddressManufacturers',
                    'Customers.AddressCustomers',
                ]
            )->first();
        }
        return $this->_manufacturer;
    }

    protected function _getName(): string
    {
        $name = $this->firstname . ' ' . $this->lastname;
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            $name = $this->lastname . ' ' . $this->firstname;
        }

        if ($this->is_company) {
            $name = $this->firstname;
        }

        if ($this->isManufacturer()) {
            $name = $this->manufacturer->name;
        }

        return $name;
    }

    protected function _getDecodedName(): string
    {
        return html_entity_decode($this->name);
    }

    public function termsOfUseAccepted(): bool
    {
        $formattedAcceptedDate = $this->terms_of_use_accepted_date->i18nFormat(Configure::read('DateFormat.Database'));
        return $formattedAcceptedDate >= Configure::read('app.termsOfUseLastUpdate');
    }

    public function isSuperadmin(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->id_default_group == self::GROUP_SUPERADMIN) {
            return true;
        }
        return false;
    }

    public function isManufacturer(): bool
    {
        return isset($this->manufacturer);
    }

    public function getManufacturerId(): int
    {
        if (!$this->isManufacturer()) {
            throw new \Exception('user is no manufacturer');
        }
        return $this->manufacturer->id_manufacturer;
    }

    public function getManufacturerName(): string
    {
        if (!$this->isManufacturer()) {
            throw new \Exception('user is no manufacturer');
        }
        return $this->manufacturer->name;
    }

    public function getManufacturerAnonymizeCustomers(): bool
    {
        if (!$this->isManufacturer()) {
            throw new \Exception('user is no manufacturer');
        }
        return (bool) $this->manufacturer->anonymize_customers;
    }

    public function getManufacturerVariableMemberFee(): ?int
    {
        if (!$this->isManufacturer()) {
            throw new \Exception('user is no manufacturer');
        }
        return $this->manufacturer->variable_member_fee;
    }

    public function getManufacturerEnabledSyncDomains(): ?string
    {
        if (!$this->isManufacturer()) {
            throw new \Exception('user is no manufacturer');
        }
        return $this->manufacturer->enabled_sync_domains;
    }

    public function getManufacturerCustomer(): ?Customer
    {
        if (!$this->isManufacturer()) {
            throw new \Exception('user is no manufacturer');
        }
        return $this->manufacturer->customer;
    }

    public function getId(): int
    {
        return $this->id_customer;
    }

    public function getAbbreviatedUserName(): string
    {
        $result = $this->firstname . ' ' . substr($this->lastname, 0, 1) . '.';
        if ($this->is_company) {
            $result = $this->firstname;
        }
        return $result;
    }

    public function getGroupId(): int
    {
        return $this->id_default_group;
    }

    public function getLastOrderDetailsForDropdown(): array
    {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $dropdownData = $orderDetailsTable->getLastOrderDetailsForDropdown($this->getId());
        return $dropdownData;
    }

    public function getFutureOrderDetails(): SelectQuery
    {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $futureOrderDetails = $orderDetailsTable->getFutureOrdersByCustomerId($this->getId());
        return $futureOrderDetails;
    }

    public function getCreditBalanceMinusCurrentCartSum(): float
    {
        return $this->getCreditBalance() - $this->getCart()['CartProductSum'] - $this->getCart()['CartDepositSum'];
    }

    public function hasEnoughCreditForProduct($grossPrice): bool
    {
        return $this->getCreditBalanceMinusCurrentCartSum() - Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE') >= $grossPrice;
    }

    public function isAdmin(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->getGroupId() == self::GROUP_ADMIN) {
            return true;
        }
        return false;
    }

    public function isCustomer(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if (in_array($this->getGroupId(), [
            self::GROUP_MEMBER,
            self::GROUP_SELF_SERVICE_CUSTOMER,
            ])
        ) {
            return true;
        }
        return false;
    }

    public function isSelfServiceCustomer(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->getGroupId() == self::GROUP_SELF_SERVICE_CUSTOMER) {
            return true;
        }
        return false;
    }

    public function getCreditBalance(): float
    {
        $customersTable = TableRegistry::getTableLocator()->get('Customers');
        return $customersTable->getCreditBalance($this->getId());
    }

    public function getCartType(): int
    {
        $cartType = Cart::TYPE_WEEKLY_RHYTHM;
        $orderCustomerService = new OrderCustomerService();
        if ($orderCustomerService->isOrderForDifferentCustomerMode()) {
            $cartType = Cart::TYPE_INSTANT_ORDER;
        }
        if ($orderCustomerService->isSelfServiceModeByUrl() || $orderCustomerService->isSelfServiceModeByReferer()) {
            $cartType = Cart::TYPE_SELF_SERVICE;
        }
        return $cartType;
    }

    public function setCart(array $cart): void
    {
        $this->cart = $cart;
    }

    public function getCart(): array
    {
        $cartType = $this->getCartType();
        $cartsTable = TableRegistry::getTableLocator()->get('Carts');
        return $cartsTable->getCart($this, $cartType);
    }

    public function getProducts(): array
    {
        if ($this->cart !== null) {
            return $this->cart['CartProducts'];
        }
        return [];
    }

    public function getProductsWithUnitCount(): int
    {
        if ($this->cart !== null) {
            return $this->cart['ProductsWithUnitCount'];
        }
        return 0;
    }

    public function getProductAndDepositSum(): float
    {
        return $this->getProductSum() + $this->getDepositSum();
    }

    public function getTaxSum(): float
    {
        if ($this->cart !== null) {
            return $this->cart['CartTaxSum'];
        }
        return 0;
    }

    public function getDepositSum(): float
    {
        if ($this->cart !== null) {
            return $this->cart['CartDepositSum'];
        }
        return 0;
    }

    public function getProductSum(): float
    {
        if ($this->cart !== null) {
            return $this->cart['CartProductSum'];
        }
        return 0;
    }

    public function getProductSumExcl(): float
    {
        if ($this->cart !== null) {
            return $this->cart['CartProductSumExcl'];
        }
        return 0;
    }

    public function getCartId(): int
    {
        return $this->cart['Cart']->id_cart;
    }

    public function markCartAsSaved(): Cart|false
    {
        if ($this->cart === null) {
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

    public function getUniqueManufacturers(): array
    {
        $manufactures = [];
        foreach ($this->getProducts() as $product) {
            $manufactures[$product['manufacturerId']] = [
                'name' => $product['manufacturerName']
            ];
        }
        return $manufactures;
    }

    public function getProduct($productId): array|false
    {
        foreach ($this->getProducts() as $product) {
            if ($product['productId'] == $productId) {
                return $product;
            }
        }
        return false;
    }

    public function isCartEmpty(): bool
    {
        return empty($this->getProducts());
    }

}

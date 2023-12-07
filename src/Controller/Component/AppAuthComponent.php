<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component\AuthComponent;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use App\Services\CartService;

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
class AppAuthComponent extends AuthComponent
{

    public $CartService;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->CartService = new CartService();
        $this->CartService->setAppAuth($this);
    }

    public function getUserId()
    {
        return $this->user('id_customer');
    }

    public function getUserFirstname()
    {
        return $this->user('firstname');
    }

    public function getUsername()
    {
        return $this->user('name');
    }

    public function getEmail()
    {
        return $this->user('email');
    }

    public function getAbbreviatedUserName()
    {
        $result = $this->user('firstname') . ' ' . substr($this->user('lastname'), 0, 1) . '.';
        if ($this->user('is_company')) {
            $result = $this->user('firstname');
        }
        return $result;
    }

    public function getGroupId()
    {
        return $this->user('id_default_group');
    }

    public function getLastOrderDetailsForDropdown()
    {
        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');
        $dropdownData = $orderDetailsTable->getLastOrderDetailsForDropdown($this->getUserId());
        return $dropdownData;
    }

    public function getFutureOrderDetails()
    {
        if (empty($this->user())) {
            return [];
        }
        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');
        $futureOrderDetails = $orderDetailsTable->getFutureOrdersByCustomerId($this->getUserId());
        return $futureOrderDetails;
    }

    public function getCreditBalanceMinusCurrentCartSum()
    {
        return $this->getCreditBalance() - $this->getCart()['CartProductSum'] - $this->getCart()['CartDepositSum'];
    }

    public function hasEnoughCreditForProduct($grossPrice)
    {
        $hasEnoughCreditForProduct =
            $this->getCreditBalanceMinusCurrentCartSum() -
            Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE')
            >= $grossPrice;
        return $hasEnoughCreditForProduct;
    }

    public function getManufacturerId()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return $this->getController()->getRequest()->getSession()->read('Auth.Manufacturer.id_manufacturer');
    }

    public function getManufacturerName()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return $this->getController()->getRequest()->getSession()->read('Auth.Manufacturer.name');
    }

    public function getManufacturerAnonymizeCustomers()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return $this->getController()->getRequest()->getSession()->read('Auth.Manufacturer.anonymize_customers');
    }

    public function getManufacturerVariableMemberFee()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return $this->getController()->getRequest()->getSession()->read('Auth.Manufacturer.variable_member_fee');
    }

    public function getManufacturerEnabledSyncDomains()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return $this->getController()->getRequest()->getSession()->read('Auth.Manufacturer.enabled_sync_domains');
    }

    public function getManufacturerCustomer()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return $this->getController()->getRequest()->getSession()->read('Auth.Manufacturer.customer');
    }

    public function isAdmin(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->user('id_default_group') == CUSTOMER_GROUP_ADMIN) {
            return true;
        }
        return false;
    }

    public function isCustomer(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if (in_array($this->user('id_default_group'), [
            CUSTOMER_GROUP_MEMBER,
            CUSTOMER_GROUP_SELF_SERVICE_CUSTOMER,
            ],
            )
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
        if ($this->user('id_default_group') == CUSTOMER_GROUP_SELF_SERVICE_CUSTOMER) {
            return true;
        }
        return false;
    }

    public function getCreditBalance()
    {
        $customersTable = FactoryLocator::get('Table')->get('Customers');
        return $customersTable->getCreditBalance($this->getUserId());
    }

    public function isSelfServiceModeByReferer()
    {
        $result = false;
        $serverParams = $this->getController()->getRequest()->getServerParams();
        $requestUriAllowed = [
            '/' . __('route_cart') . '/ajaxAdd/',
            '/' . __('route_cart') . '/ajaxRemove/'
        ];
        if (isset($serverParams['HTTP_REFERER'])) {
            $result = preg_match('`' . preg_quote(Configure::read('App.fullBaseUrl')) . '/' . __('route_self_service') . '`', $serverParams['HTTP_REFERER']);
        }
        if (!in_array($serverParams['REQUEST_URI'], $requestUriAllowed)) {
            $result = false;
        }
        return $result;
    }

    public function getCartType()
    {
        $cartsTable = FactoryLocator::get('Table')->get('Carts');
        $cartType = $cartsTable::CART_TYPE_WEEKLY_RHYTHM;
        if ($this->isOrderForDifferentCustomerMode()) {
            $cartType = $cartsTable::CART_TYPE_INSTANT_ORDER;
        }
        if ($this->isSelfServiceModeByUrl() || $this->isSelfServiceModeByReferer()) {
            $cartType = $cartsTable::CART_TYPE_SELF_SERVICE;
        }
        return $cartType;
    }

    public function setCart($cart)
    {
        $this->CartService->cart = $cart;
    }

    public function getCart()
    {
        if (! $this->user()) {
            return null;
        }

        $cartType = $this->getCartType();

        $cartsTable = FactoryLocator::get('Table')->get('Carts');
        return $cartsTable->getCart($this, $cartType);
    }

}

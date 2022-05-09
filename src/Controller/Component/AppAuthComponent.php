<?php

namespace App\Controller\Component;

use Cake\Controller\Component\AuthComponent;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppAuthComponent extends AuthComponent
{

    public $components = [
        'Flash',
        'RequestHandler',
        'Cart'
    ];

    public function flash($message): void
    {
        $this->Flash->error($message);
    }

    /**
     * @return boolean
     */
    public function termsOfUseAccepted()
    {
        $formattedAcceptedDate = $this->user('terms_of_use_accepted_date')->i18nFormat(Configure::read('DateFormat.Database'));
        return $formattedAcceptedDate >= Configure::read('app.termsOfUseLastUpdate');
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
        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $dropdownData = $this->OrderDetail->getLastOrderDetailsForDropdown($this->getUserId());
        return $dropdownData;
    }

    public function getFutureOrderDetails()
    {
        if (empty($this->user())) {
            return [];
        }
        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $futureOrderDetails = $this->OrderDetail->getFutureOrdersByCustomerId($this->getUserId());
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

    private function setManufacturer()
    {
        if (!empty($this->user()) &&
            !is_null($this->getController()->getRequest()->getSession()->read('Auth')) &&
            array_key_exists('Manufacturer', $this->getController()->getRequest()->getSession()->read('Auth'))) {
            return;
        }

        if (!empty($this->user())) {
            $mm = FactoryLocator::get('Table')->get('Manufacturers');
            $manufacturer = $mm->find('all', [
                'conditions' => [
                    'AddressManufacturers.email' => $this->user('email'),
                    'AddressManufacturers.id_manufacturer > ' . APP_OFF
                ],
                'contain' => [
                    'AddressManufacturers',
                    'Customers.AddressCustomers',
                ]
            ])->first();
            if (!is_null($manufacturer)) {
                $manufacturer = $manufacturer->toArray();
            }
            $this->getController()->getRequest()->getSession()->write('Auth.Manufacturer', $manufacturer);
        }
    }

    public function isSuperadmin(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->user('id_default_group') == CUSTOMER_GROUP_SUPERADMIN) {
            return true;
        }
        return false;
    }

    public function isManufacturer(): bool
    {
        $this->setManufacturer();
        return !empty($this->getController()->getRequest()->getSession()->read('Auth.Manufacturer'));
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
        $c = FactoryLocator::get('Table')->get('Customers');
        return $c->getCreditBalance($this->getUserId());
    }

    public function isOrderForDifferentCustomerMode()
    {
        return $this->getController()->getRequest()->getSession()->read('Auth.orderCustomer');
    }

    public function isSelfServiceModeByUrl()
    {
        $result = $this->getController()->getRequest()->getPath() == '/' . __('route_self_service');
        if (!empty($this->getController()->getRequest()->getQuery('redirect'))) {
            $result |= preg_match('`' . '/' . __('route_self_service') . '`', $this->getController()->getRequest()->getQuery('redirect'));
        }
        return $result;
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
            $result = preg_match('`' . preg_quote(Configure::read('app.cakeServerName')) . '/' . __('route_self_service') . '`', $serverParams['HTTP_REFERER']);
        }
        if (!in_array($serverParams['REQUEST_URI'], $requestUriAllowed)) {
            $result = false;
        }
        return $result;
    }

    public function getCartType()
    {
        $cart = FactoryLocator::get('Table')->get('Carts');
        $cartType = $cart::CART_TYPE_WEEKLY_RHYTHM;
        if ($this->isOrderForDifferentCustomerMode()) {
            $cartType = $cart::CART_TYPE_INSTANT_ORDER;
        }
        if ($this->isSelfServiceModeByUrl() || $this->isSelfServiceModeByReferer()) {
            $cartType = $cart::CART_TYPE_SELF_SERVICE;
        }
        return $cartType;
    }

    public function setCart($cart)
    {
        $this->Cart->cart = $cart;
    }

    public function getCart()
    {
        if (! $this->user()) {
            return null;
        }

        $cartType = $this->getCartType();

        $cart = FactoryLocator::get('Table')->get('Carts');
        return $cart->getCart($this, $cartType);
    }

    public function isTimebasedCurrencyEnabledForManufacturer(): bool
    {
        return Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->isManufacturer() && $this->getController()->getRequest()->getSession()->read('Auth.Manufacturer.timebased_currency_enabled');
    }

    public function isTimebasedCurrencyEnabledForCustomer(): bool
    {
        return Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->user('timebased_currency_enabled');
    }

}

<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\IdentityInterface;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\Datasource\FactoryLocator;
use Cake\Routing\Router;

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
class Customer extends Entity implements IdentityInterface
{

    public $CartService;

    protected $_virtual = ['name'];

    public function getIdentifier()
    {
        return $this->id_customer;
    }

    public function getOriginalData()
    {
        return $this;
    }

    protected function _getName()
    {
        $name = $this->firstname . ' ' . $this->lastname;
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            $name = $this->lastname . ' ' . $this->firstname;
        }

        if ($this->is_company) {
            $name = $this->firstname;
        }

        if ($this->isManufacturer()) {
            $name = $this->getManufacturerName();
        }

        return $name;
    }

    public function termsOfUseAccepted(): bool
    {
        $formattedAcceptedDate = $this->get('terms_of_use_accepted_date')->i18nFormat(Configure::read('DateFormat.Database'));
        return $formattedAcceptedDate >= Configure::read('app.termsOfUseLastUpdate');
    }

    public function isLoggedIn(): bool
    {
        return !$this->isNew();
    }

    public function isSuperadmin(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->get('id_default_group') == CUSTOMER_GROUP_SUPERADMIN) {
            return true;
        }
        return false;
    }
    
    private function setManufacturer()
    {

        if (!$this->isNew() &&
            !is_null(Router::getRequest()->getSession()->read('Auth')) &&
            !empty(Router::getRequest()->getSession()->read('AuthManufacturer'))) {
            return;
        }

        if (!$this->isNew()) {
            $mm = FactoryLocator::get('Table')->get('Manufacturers');
            $manufacturer = $mm->find('all', [
                'conditions' => [
                    'AddressManufacturers.email' => $this->get('email'),
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
            Router::getRequest()->getSession()->write('AuthManufacturer', $manufacturer);
        }
    }

    public function isManufacturer(): bool
    {
        $this->setManufacturer();
        return !empty(Router::getRequest()->getSession()->read('AuthManufacturer'));
    }

    public function getManufacturerId()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return Router::getRequest()->getSession()->read('AuthManufacturer.id_manufacturer');
    }

    public function getManufacturerName()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return Router::getRequest()->getSession()->read('AuthManufacturer.name');
    }

    public function getManufacturerAnonymizeCustomers()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return Router::getRequest()->getSession()->read('AuthManufacturer.anonymize_customers');
    }

    public function getManufacturerVariableMemberFee()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return Router::getRequest()->getSession()->read('AuthManufacturer.variable_member_fee');
    }

    public function getManufacturerEnabledSyncDomains()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return Router::getRequest()->getSession()->read('AuthManufacturer.enabled_sync_domains');
    }

    public function getManufacturerCustomer()
    {
        if (! $this->isManufacturer()) {
            throw new \Exception('logged user is no manufacturer');
        }
        return Router::getRequest()->getSession()->read('AuthManufacturer.customer');
    }


    public function getUserId()
    {
        return $this->get('id_customer');
    }

    public function getUserFirstname()
    {
        return $this->get('firstname');
    }

    public function getEmail()
    {
        return $this->get('email');
    }

    public function getAbbreviatedUserName()
    {
        $result = $this->get('firstname') . ' ' . substr($this->get('lastname'), 0, 1) . '.';
        if ($this->get('is_company')) {
            $result = $this->get('firstname');
        }
        return $result;
    }

    public function getGroupId()
    {
        return $this->get('id_default_group');
    }

    public function getLastOrderDetailsForDropdown()
    {
        $orderDetailsTable = FactoryLocator::get('Table')->get('OrderDetails');
        $dropdownData = $orderDetailsTable->getLastOrderDetailsForDropdown($this->getUserId());
        return $dropdownData;
    }

    public function getFutureOrderDetails()
    {
        if ($this->isNew()) {
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

    public function isAdmin(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->get('id_default_group') == CUSTOMER_GROUP_ADMIN) {
            return true;
        }
        return false;
    }

    public function isCustomer(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if (in_array($this->get('id_default_group'), [
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
        if ($this->get('id_default_group') == CUSTOMER_GROUP_SELF_SERVICE_CUSTOMER) {
            return true;
        }
        return false;
    }

    public function getCreditBalance()
    {
        $customersTable = FactoryLocator::get('Table')->get('Customers');
        return $customersTable->getCreditBalance($this->getUserId());
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
        if (! $this->get()) {
            return null;
        }

        $cartType = $this->getCartType();

        $cartsTable = FactoryLocator::get('Table')->get('Carts');
        return $cartsTable->getCart($this, $cartType);
    }

}

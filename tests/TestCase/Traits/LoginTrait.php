<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait LoginTrait
{

    public bool $isSelfServiceModeByUrl = false;

    public function login($userId): array
    {

        $customerTable = $this->getTableLocator()->get('Customers');
        $identity = $customerTable->find('all',
            conditions: [
                'Customers.id_customer' => $userId,
            ],
            contain: [
                'AddressCustomers',
            ]
        )->first()->toArray();

        return [
            'Auth' => $identity,
        ];
    }

    public function loginAsSelfServiceCustomer(): void
    {
        $sessionData =  $this->login(Configure::read('test.selfServiceCustomerId'));
        $this->session($sessionData);
    }

    public function loginAsSuperadmin(): void
    {
        $sessionData =  $this->login(Configure::read('test.superadminId'));
        $this->session($sessionData);
    }

    public function loginAsAdmin(): void
    {
        $sessionData = $this->login(Configure::read('test.adminId'));
        $this->session($sessionData);
    }

    public function loginAsCustomer(): void
    {
        $sessionData = $this->login(Configure::read('test.customerId'));
        $this->session($sessionData);
    }

    public function loginAsMeatManufacturer(): void
    {
        $sessionData = $this->login(Configure::read('test.meatManufacturerId'));
        $this->session($sessionData);
    }

    public function loginAsVegetableManufacturer(): void
    {
        $sessionData = $this->login(Configure::read('test.vegetableManufacturerId'));
        $this->session($sessionData);
    }

    public function loginAsMilkManufacturer(): void
    {
        $sessionData = $this->login(Configure::read('test.milkManufacturerId'));
        $this->session($sessionData);
    }

    public function logout(): void
    {
        $this->get($this->Slug->getLogout());
    }

    public function loginAsSuperadminAddOrderCustomerToSession($session): void
    {
        $sessionData =  $this->login(Configure::read('test.superadminId'));
        $sessionData['OrderIdentity'] = $session['OrderIdentity'];
        $this->session($sessionData);
    }

    public function getId(): int|array
    {
        $identity = $this->getUser();
        if (empty($identity)) {
            return [];
        }
        return $identity['id_customer'];
    }

    /**
     * used in CartsControllerTest::checkCartStatus
     */
    public function isOrderForDifferentCustomerMode(): ?bool
    {
        return $this->getSession()->read('OrderIdentity');
    }

    /**
     * used in CartsControllerTest::checkCartStatus
     * mocks AppAuthComponent
     */
    public function isSelfServiceModeByUrl(): bool
    {
        return $this->isSelfServiceModeByUrl;
    }

    public function getUser(): array
    {
        if (empty($this->_session)) {
            return [];
        }
        return $this->_session['Auth'];
    }
}

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
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait LoginTrait
{

    public $isSelfServiceModeByUrl = false;

    public function login($userId)
    {

        $customerTable = $this->getTableLocator()->get('Customers');
        $loggedUser = $customerTable->find('all', [
            'conditions' => [
                'Customers.id_customer' => $userId
            ],
            'contain' => [
                'AddressCustomers',
            ]
        ])->first()->toArray();

        return [
            'Auth' => [
                'User' => $loggedUser
            ]
        ];
    }

    public function loginAsSelfServiceCustomer()
    {
        $sessionData =  $this->login(Configure::read('test.selfServiceCustomerId'));
        $this->session($sessionData);
    }

    public function loginAsSuperadmin()
    {
        $sessionData =  $this->login(Configure::read('test.superadminId'));
        $this->session($sessionData);
    }

    public function loginAsAdmin()
    {
        $sessionData = $this->login(Configure::read('test.adminId'));
        $this->session($sessionData);
    }

    public function loginAsCustomer()
    {
        $sessionData = $this->login(Configure::read('test.customerId'));
        $this->session($sessionData);
    }

    public function loginAsMeatManufacturer()
    {
        $sessionData = $this->login(Configure::read('test.meatManufacturerId'));
        $this->session($sessionData);
    }

    public function loginAsVegetableManufacturer()
    {
        $sessionData = $this->login(Configure::read('test.vegetableManufacturerId'));
        $this->session($sessionData);
    }

    public function loginAsMilkManufacturer()
    {
        $sessionData = $this->login(Configure::read('test.milkManufacturerId'));
        $this->session($sessionData);
    }

    public function logout()
    {
        $this->get($this->Slug->getLogout());
    }

    public function loginAsSuperadminAddOrderCustomerToSession($session)
    {
        $sessionData =  $this->login(Configure::read('test.superadminId'));
        $sessionData['Auth']['orderCustomer'] = $session['Auth']['orderCustomer'];
        $this->session($sessionData);
    }

    public function getUserId()
    {
        $loggedUser = $this->user();
        if (empty($loggedUser)) {
            return [];
        }
        return $loggedUser['id_customer'];
    }

    /**
     * used in CartsControllerTest::checkCartStatus
     * mocks AppAuthComponent
     */
    public function isOrderForDifferentCustomerMode()
    {
        return $this->getSession()->read('Auth.orderCustomer');
    }

    /**
     * used in CartsControllerTest::checkCartStatus
     * mocks AppAuthComponent
     */
    public function isSelfServiceModeByUrl()
    {
        return $this->isSelfServiceModeByUrl;
    }

    public function user()
    {
        if (empty($this->_session)) {
            return [];
        }
        return $this->_session['Auth']['User'];
    }
}

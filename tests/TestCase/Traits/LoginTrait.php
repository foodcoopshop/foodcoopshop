<?php

namespace App\Test\TestCase\Traits;

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait LoginTrait
{

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

        $this->session([
            'Auth' => [
                'User' => $loggedUser
            ]
        ]);
    }

    public function loginAsSuperadmin()
    {
        return $this->login(Configure::read('test.superadminId'));
    }

    public function loginAsAdmin()
    {
        return $this->login(Configure::read('test.adminId'));
    }

    public function loginAsCustomer()
    {
        return $this->login(Configure::read('test.customerId'));
    }

    public function loginAsMeatManufacturer()
    {
        return $this->login(Configure::read('test.meatManufacturerId'));
    }

    public function loginAsVegetableManufacturer()
    {
        return $this->login(Configure::read('test.vegetableManufacturerId'));
    }

    public function logout()
    {
        $this->get($this->Slug->getLogout());
    }

    public function getUserId()
    {
        $loggedUser = $this->user();
        if (empty($loggedUser)) {
            return [];
        }
        return $loggedUser['id_customer'];
    }

    public function user()
    {
        if (empty($this->_session)) {
            return [];
        }
        return $this->_session['Auth']['User'];
    }
}

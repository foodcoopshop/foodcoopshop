<?php

namespace App\Test\TestCase;

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

    protected function login($userId, $email, $default_group)
    {
        $this->session([
            'Auth' => [
                'User' => [
                    'id_customer' => $userId,
                    'email' => $email,
                    'id_default_group' => $default_group,
                ]
            ]
        ]);
    }

    protected function loginAsSuperadmin()
    {
        return $this->login(
            Configure::read('test.superadminId'),
            Configure::read('test.loginEmailSuperadmin'),
            Configure::read('test.superAdminDefaultGroup')
        );
    }

    protected function loginAsAdmin()
    {
        return $this->login(
            Configure::read('test.adminId'),
            Configure::read('test.loginEmailAdmin'),
            Configure::read('test.adminDefaultGroup')
        );
    }

    protected function loginAsCustomer()
    {
        return $this->login(
            Configure::read('test.customerId'),
            Configure::read('test.loginEmailCustomer'),
            Configure::read('test.customerDefaultGroup')
        );
    }

    protected function loginAsMeatManufacturer()
    {
        return $this->login(
            Configure::read('test.meatManufacturerId'),
            Configure::read('test.loginEmailMeatManufacturer'),
            Configure::read('test.manufacturerDefaultGroup')
        );
    }

    protected function loginAsVegetableManufacturer()
    {
        return $this->login(
            Configure::read('test.loginEmailVegetableManufacturer'),
            Configure::read('test.loginEmailVegetableManufacturer'),
            Configure::read('test.manufacturerDefaultGroup')
        );
    }

    protected function logout()
    {
        $this->get($this->Slug->getLogout());
    }
}

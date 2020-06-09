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
 * @since         FoodCoopShop 3.0.3
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait LoginTrait {
    protected function login($userId, $email){
        $this->session([
            'Auth' => [
                'User' => [
                    'id_customer' => $userId,
                    'email' => $email,
                ]
            ]
        ]);
    }

    protected function loginAsSuperadmin()
    {
        return $this->login(Configure::read('test.superadminId'), Configure::read('test.loginEmailSuperadmin'));
    }

    protected function loginAsCustomer() {
        return $this->login(Configure::read('test.customerId'), Configure::read('test.loginEmailCustomer'));
    }

    protected function loginAsMeatManufacturer() {
        return $this->login(Configure::read('test.meatManufacturerId'), Configure::read('test.loginEmailMeatManufacturer'));
    }

    protected function loginAsVegetableManufacturer(){
        return $this->login(Configure::read('test.loginEmailVegetableManufacturer'), Configure::read('test.loginEmailVegetableManufacturer'));

    }

    protected function logout(){
        $this->get($this->Slug->getLogout());
    }
}

<?php
namespace App\Test\TestCase;

use Cake\Core\Configure;

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

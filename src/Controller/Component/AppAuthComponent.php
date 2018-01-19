<?php

namespace App\Controller\Component;

use Cake\Controller\Component\AuthComponent;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * AppAuthComponent
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppAuthComponent extends AuthComponent
{

    public $components = [
        'Session',
        'Flash',
        'RequestHandler',
        'Cart'
    ];

    public $manufacturer;

    public function flash($message)
    {
        $this->Flash->error($message);
    }

    /**
     * @return boolean
     */
    public function termsOfUseAccepted()
    {
        return $this->user('terms_of_use_accepted_date') >= Configure::read('AppConfig.termsOfUseLastUpdate');
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
        return $this->user('firstname') . ' ' . substr($this->user('lastname'), 0, 1) . '.';
    }

    public function getGroupId()
    {
        return $this->user('id_default_group');
    }

    private function setManufacturer()
    {
        if (!empty($this->manufacturer)) {
            return;
        }

        /*
        $mm = TableRegistry::get('Manufacturers');
        $this->manufacturer = $mm->find('all', [
            'conditions' => [
                'Addresses.email' => $this->user('email'),
                'Addresses.id_manufacturer > ' . APP_OFF
            ]
        ])->first();
        */
    }

    public function isSuperadmin()
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->user('id_default_group') == CUSTOMER_GROUP_SUPERADMIN) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isManufacturer()
    {
        $this->setManufacturer();

        if (! empty($this->manufacturer)) {
            return true;
        }

        return false;
    }

    public function getManufacturerId()
    {
        if (! $this->isManufacturer()) {
            throw new Exception('logged user is no manufacturer');
        }

        if (! empty($this->manufacturer)) {
            return $this->manufacturer['Manufacturers']['id_manufacturer'];
        }

        return 0;
    }

    public function getManufacturerName()
    {
        if (! $this->isManufacturer()) {
            throw new Exception('logged user is no manufacturer');
        }

        if (! empty($this->manufacturer)) {
            return $this->manufacturer['Manufacturers']['name'];
        }

        return '';
    }

    /**
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->user('id_default_group') == CUSTOMER_GROUP_ADMIN) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isCustomer()
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->user('id_default_group') == CUSTOMER_GROUP_MEMBER) {
            return true;
        }
        return false;
    }

    public function login($user = null)
    {
        return parent::login($user);
    }

    public function getCreditBalance()
    {
        App::uses('Customers', 'Model');
        $c = new Customer();
        return $c->getCreditBalance($this->getUserId());
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
        $cc = ClassRegistry::init('Cart');
        return $cc->getCart($this->getUserId());
    }
}

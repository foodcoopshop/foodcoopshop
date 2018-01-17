<?php

App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('SlugHelper', 'View/Helper');
App::uses('Customer', 'Model');

/**
 * AppSimpleBrowser
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
class AppSimpleBrowser extends SimpleBrowser
{

    public $baseUrl;

    public $adminPrefix;

    public $Slug;

    public $Customer;

    public function __construct()
    {
        parent::__construct();

        $Controller = new Controller();
        $View = new View($Controller);
        $this->Slug = new SlugHelper($View);
        $this->Customer = new Customer();

        $this->setConnectionTimeout(300); // 5 min should be enough
        $this->baseUrl = Configure::read('AppConfig.cakeServerName');
        $this->adminPrefix = '/admin';
    }

    public function get($url, $parameters = false)
    {
        return parent::get($this->baseUrl . $url, $parameters);
    }

    /**
     * posts as ajax
     * @param string $url
     * @param array $parameters
     */
    public function ajaxPost($url, $parameters)
    {
        $this->addHeader('X-Requested-With:XMLHttpRequest');
        return parent::post(
            $this->baseUrl . $url,
            $parameters,
            'application/x-www-form-urlencoded'
        );
    }

    public function post($url, $parameters = false, $content_type = false)
    {
        return parent::post(
            $this->baseUrl . $url,
            $parameters,
            $content_type
        );
    }

    public function getJsonDecodedContent()
    {
        return json_decode($this->getContent());
    }

    public function doFoodCoopShopLogin()
    {
        $this->post($this->Slug->getLogin(), [
            'Customer' => [
                'email' => $this->loginEmail,
                'passwd' => $this->loginPassword
            ],
            'remember_me' => false
        ]);

        if (preg_match('/Anmelden ist fehlgeschlagen./', $this->getContent())) {
            print_r('Falsche Zugangsdaten für FCS Login');
            print_r('AdminEmail: ' . $this->loginEmail);
            print_r('AdminPassword: ' . $this->loginPassword);
            print_r('Skript wird abgebrochen.');
            exit();
        }
    }

    public function getLoggedUser()
    {
        $this->Customer->recursive = - 1;
        $user = $this->Customer->find('first', [
            'conditions' => [
                'Customer.email' => $this->loginEmail
            ]
        ]);
        return $user;
    }

    public function getLoggedUserId()
    {
        $loggedUser = $this->getLoggedUser();
        if (! empty($loggedUser)) {
            return $loggedUser['Customer']['id_customer'];
        }
        return 0;
    }

    public function doFoodCoopShopLogout()
    {
        $this->get($this->Slug->getLogout());
    }
}

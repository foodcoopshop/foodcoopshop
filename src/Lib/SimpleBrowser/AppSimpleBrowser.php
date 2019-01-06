<?php

namespace App\Lib\SimpleBrowser;

use App\View\Helper\SlugHelper;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\View\View;
use SimpleBrowser;

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppSimpleBrowser extends SimpleBrowser
{

    public $baseUrl;

    public $adminPrefix;

    public $Slug;

    public $Customer;
    
    public $loginEmail;
    
    public $loginPassword;

    public function __construct()
    {
        parent::__construct();

        $View = new View();
        $this->Slug = new SlugHelper($View);
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');

        $this->setConnectionTimeout(300); // 5 min should be enough
        $this->baseUrl = Configure::read('app.cakeServerName');
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
            'email' => $this->loginEmail,
            'passwd' => $this->loginPassword,
            'remember_me' => false
        ]);

        if (preg_match('/'.__('Signing_in_failed_account_inactive_or_password_wrong?').'/', $this->getContent())) {
            print_r('wrong credentials for admin login');
            print_r('AdminEmail: ' . $this->loginEmail);
            print_r('AdminPassword: ' . $this->loginPassword);
            print_r(' - script will stop here');
            exit();
        }
    }

    public function getLoggedUser()
    {
        $user = $this->Customer->find('all', [
            'conditions' => [
                'Customers.email' => $this->loginEmail
            ]
        ])->first();
        return $user;
    }

    public function getLoggedUserId()
    {
        $loggedUser = $this->getLoggedUser();
        if (! empty($loggedUser)) {
            return $loggedUser->id_customer;
        }
        return 0;
    }

    public function doFoodCoopShopLogout()
    {
        $this->get($this->Slug->getLogout());
    }
}

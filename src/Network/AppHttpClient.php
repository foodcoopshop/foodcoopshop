<?php

namespace App\Network;

use App\View\Helper\SlugHelper;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\View\View;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppHttpClient extends Client
{

    public $baseUrl;

    public $adminPrefix;

    public $Slug;

    public $Customer;
    
    public $loginEmail;
    
    public $loginPassword;
    
    private $response;
    
    public $redirect = 0;

    public function __construct($config = [])
    {
        $parsedUrl = parse_url(Configure::read('app.cakeServerName'));
        
        $config = array_merge($config, [
            'host' => $parsedUrl['host'],
            'scheme' => $parsedUrl['scheme'],
            'ssl_verify_peer' => false,
            'timeout' => 300
        ]);
        parent::__construct($config);

        $View = new View();
        $this->Slug = new SlugHelper($View);
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');

        $this->adminPrefix = '/admin';
    }
    
    public function followOneRedirectForNextRequest()
    {
        $this->redirect = 1;
    }
    
    public function getContent()
    {
        return $this->response->getStringBody();
    }
    
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }
    
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }
    
    public function getUrl()
    {
        return $this->response->getHeaderline('Location');
    }
    
    public function get($url, $data = [], array $options = [])
    {
        $options = array_merge($options, [
            'redirect' => $this->redirect
        ]);
        $this->redirect = 0;
        $this->response = parent::get($url, $data, $options);
        return $this->getContent();
    }

    /**
     * posts as ajax
     * @param string $url
     * @param array $parameters
     */
    public function ajaxPost($url, $data = [], array $options = [])
    {
        $options = array_merge($options, [
            'headers' => [
                'X-Requested-With:XMLHttpRequest'
            ],
            'type' => 'json',
        ]);
        $this->response = parent::post(
            $url,
            $data,
            $options
        );
        return $this->getContent();
    }

    public function post($url, $data = [], array $options = [])
    {
        $options = array_merge($options, [
            'redirect' => $this->redirect
        ]);
        $this->redirect = 0;
        $this->response = parent::post(
            $url,
            $data,
            $options
        );
        return $this->getContent();
    }

    public function getJsonDecodedContent()
    {
        return json_decode(json_encode($this->response->getJson()), false); // convert array recursively into object
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

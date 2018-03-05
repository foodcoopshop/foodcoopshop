<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * CartComponent
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
class AppController extends Controller
{

    public function initialize()
    {
        
        parent::initialize();
        
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash', [
            'clear' => true
        ]);
        $this->loadComponent('String');
        $this->loadComponent('Cart');
        
        $this->loadComponent('AppAuth', [
            'logoutRedirect' => '/',
            'loginAction' => Configure::read('app.slugHelper')->getLogin(),
            'authError' => ACCESS_DENIED_MESSAGE,
            'authorize' => [
                'Controller'
            ],
            'authenticate' => [
                'Form' => [
                    'userModel' => 'Customers',
                    'fields' => [
                        'username' => 'email',
                        'password' => 'passwd'
                    ],
                    'passwordHasher' => [
                        'className' => 'App'
                    ],
                    'scope' => [
                        'Customers.active' => true
                    ],
                    'finder' => 'auth' // CustomersTable::findAuth
                ]
            ],
            'storage' => 'Session'
        ]);
        
        $this->paginate = [
            'limit' => 300000,
            'maxLimit' => 300000
        ];
        
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $this->set('appAuth', $this->AppAuth);
        $loggedUser = $this->AppAuth->user();
        $this->set('loggedUser', $loggedUser['firstname'] . ' ' . $loggedUser['lastname']);
    }

    public function beforeFilter(Event $event)
    {

        $isMobile = false;
        if ($this->request->is('mobile') && !preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $isMobile = true;
        }
        $this->set('isMobile', $isMobile);
        
        $rememberMeCookie = $this->request->getCookie('remember_me');
        if (empty($this->AppAuth->user()) && !empty($rememberMeCookie)) {
            $value = json_decode($rememberMeCookie);
            if (isset($value->email) && isset($value->passwd)) {
                $this->Customer = TableRegistry::get('Customers');
                $customer = $this->Customer->find('all', [
                    'conditions' => [
                        'Customers.email' => $value->email,
                        'Customers.passwd' => $value->passwd
                    ],
                    'contain' => [
                        'AddressCustomers'
                    ]
                ])->first();
                if (!empty($customer)) {
                    $this->AppAuth->setUser($customer);
                }
            }
        }

        if ($this->AppAuth->isManufacturer()) {
            $this->Manufacturer = TableRegistry::get('Manufacturers');
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $this->AppAuth->getManufacturerId()
                ]
            ])->first();
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            $this->set('variableMemberFeeForTermsOfUse', $variableMemberFee);
        }
        
        // should be removed in v2.1 - fixes different logged user format of cakephp 2 and 3
        if (!is_object($this->AppAuth->user('terms_of_use_accepted_date'))) {
            $this->renewAuthSession();
        }
        
        parent::beforeFilter($event);
        
    }

    /**
     * keep this method in a controller - does not work with AppAuthComponent::login
     * updates login data (after profile change for customer and manufacturer)
     */
    protected function renewAuthSession()
    {
        $this->Customer = TableRegistry::get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $this->AppAuth->getUserId()
            ],
            'contain' => [
                'AddressCustomers'
            ]
        ])->first();
        if (!empty($customer)) {
            $this->AppAuth->setUser($customer);
        }
    }


    public function setFormReferer()
    {
        $this->set('referer', isset($this->request->data['referer']) ? $this->request->data['referer'] : $this->referer());
    }

    /**
     * can be used for returning exceptions as json
     * try {
     *      $this->foo->bar();
     *  } catch (Exception $e) {
     *      $this->sendAjaxError($e);
     *  }
     * @param $error
     */
    protected function sendAjaxError($error)
    {
        if ($this->request->is('ajax')) {
            $this->response->statusCode(500);
            $response = [
                'status' => APP_OFF,
                'msg' => $error->getMessage()
            ];
            $this->set(compact('response'));
            $this->render('/Error/errorjson');
        }
    }

    /**
     * needs to be implemented if $this->AppAuth->authorize = array('Controller') is used
     */
    public function isAuthorized($user)
    {
        return true;
    }
}

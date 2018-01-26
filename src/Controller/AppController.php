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

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        
        parent::initialize();
        
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash', [
            'clear' => true
        ]);
        $this->loadComponent('String');
        $this->loadComponent('Cookie');
        $this->loadComponent('Cart');
        
        $this->loadComponent('AppAuth', [
            'logoutRedirect' => '/',
            'loginAction' => Configure::read('app.slugHelper')->getLogin(),
            'authError' => 'Zugriff verweigert, bitte melde dich an.',
            'authorize' => [
                'Controller'
            ],
            'unauthorizedRedirect' => false,
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
                    //'finder' => 'auth' // CustomersTable::findAuth
                ]
            ],
            'storage' => 'Session'
        ]);
        
        $this->paginate = [
            'limit' => 100000,
            'maxLimit' => 100000
        ];
        
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $this->set('appAuth', $this->AppAuth);
        $loggedUser = $this->AppAuth->user();
        $this->set('loggedUser', $loggedUser['firstname'] . ' ' . $loggedUser['lastname']);

        if ($this->name == 'CakeError') {
            $this->layout = 'plain';
        }
    }

    public function beforeFilter(Event $event)
    {

        $isMobile = false;
        if ($this->request->is('mobile') && !preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $isMobile = true;
        }
        $this->set('isMobile', $isMobile);
        
        // auto login if cookie is set
        if (! $this->AppAuth->user() && $this->Cookie->read('remember_me_cookie') !== null) {
            $cookie = $this->Cookie->read('remember_me_cookie');
            if (isset($cookie['email']) && isset($cookie['passwd'])) { // not set in cronjobs
                $this->Customer = TableRegistry::get('Customers');
                $customer = $this->Customer->find('all', [
                    'conditions' => [
                        'Customers.email' => $cookie['email'],
                        'Customers.passwd' => $cookie['passwd']
                    ]
                ])->first();
                if ($customer && ! $this->AppAuth->login($customer['Customers'])) {
                    $this->redirect($this->AppAuth->logout());
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
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer['Manufacturers']['variable_member_fee']);
            $this->set('variableMemberFeeForTermsOfUse', $variableMemberFee);
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
            ]
        ])->first();
        if (! empty($customer)) {
            $this->AppAuth->login($customer['Customers']);
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
            $this->render('/Errors/errorjson');
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

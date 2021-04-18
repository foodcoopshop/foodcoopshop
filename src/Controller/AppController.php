<?php

namespace App\Controller;

use App\Lib\OutputFilter\OutputFilter;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppController extends Controller
{

    public function initialize(): void
    {

        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false
        ]);
        $this->loadComponent('Flash', [
            'clear' => true
        ]);
        $this->loadComponent('String');
        $this->loadComponent('Cart');

        $authenticate = [
            'Form' => [
                'userModel' => 'Customers',
                'fields' => [
                    'username' => 'email',
                    'password' => 'passwd'
                ],
                'finder' => 'auth' // CustomersTable::findAuth
            ]
        ];

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $authenticate['BarCode'] = [
                'userModel' => 'Customers',
                'fields' => [
                    'identifier' => 'barCode'
                ],
                'finder' => 'auth' // CustomersTable::findAuth
            ];
        }

        $this->loadComponent('AppAuth', [
            'loginAction' => Configure::read('app.slugHelper')->getLogin(),
            'authError' => ACCESS_DENIED_MESSAGE,
            'authorize' => [
                'Controller'
            ],
            'authenticate' => $authenticate,
            'storage' => 'Session'
        ]);

        $this->paginate = [
            'limit' => 300000,
            'maxLimit' => 300000
        ];
    }

    public function beforeRender(EventInterface $event)
    {
        parent::beforeRender($event);
        $this->set('appAuth', $this->AppAuth);
    }

    /**
     * check valid login on each request
     * logged in user should be logged out if deleted or deactivated by admin
     */
    private function validateAuthentication()
    {
        if ($this->AppAuth->user()) {
            $this->Customer = $this->getTableLocator()->get('Customers');
            $query = $this->Customer->find('all', [
                'conditions' => [
                    'Customers.email' => $this->AppAuth->getEmail()
                ]
            ]);
            $query = $this->Customer->findAuth($query, []);
            if (empty($query->first())) {
                $this->Flash->error(__('You_have_been_signed_out.'));
                $this->AppAuth->logout();
                $this->response = $this->response->withCookie((new Cookie('remember_me')));
                $this->redirect(Configure::read('app.slugHelper')->getHome());
            }
        }
    }

    public function beforeFilter(EventInterface $event)
    {

        $this->validateAuthentication();

        if (!$this->getRequest()->is('json') && !$this->AppAuth->isInstantOrderMode()) {
            $this->loadComponent('FormProtection');
        }

        $isMobile = false;
        if ($this->getRequest()->is('mobile') && !preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $isMobile = true;
        }
        $this->set('isMobile', $isMobile);

        $rememberMeCookie = $this->getRequest()->getCookie('remember_me');
        if (empty($this->AppAuth->user()) && !empty($rememberMeCookie)) {
            $value = json_decode($rememberMeCookie);
            if (isset($value->auto_login_hash)) {
                $this->Customer = $this->getTableLocator()->get('Customers');
                $customer = $this->Customer->find('all', [
                    'conditions' => [
                        'Customers.auto_login_hash' => $value->auto_login_hash
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
            $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $this->AppAuth->getManufacturerId()
                ]
            ])->first();
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            $this->set('variableMemberFeeForTermsOfUse', $variableMemberFee);
        }

        parent::beforeFilter($event);
    }

    public function afterFilter(EventInterface $event)
    {
        parent::afterFilter($event);
        if (Configure::check('app.outputStringReplacements')) {
            $newOutput = OutputFilter::replace($this->response->getBody(), Configure::read('app.outputStringReplacements'));
            $this->response = $this->response->withStringBody($newOutput);
        }
    }

    /**
     * keep this method in a controller - does not work with AppAuthComponent::login
     * updates login data (after profile change for customer and manufacturer)
     */
    protected function renewAuthSession()
    {
        $this->Customer = $this->getTableLocator()->get('Customers');
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

    public function getPreparedReferer()
    {
        return htmlspecialchars_decode($this->getRequest()->getData('referer'));
    }

    public function setFormReferer()
    {
        $this->set('referer', !empty($this->getRequest()->getData('referer')) ? $this->getRequest()->getData('referer') : $this->referer());
    }

    /**
     * can be used for returning exceptions as json
     * try {
     *      $this->foo->bar();
     *  } catch (Exception $e) {
     *      return $this->sendAjaxError($e);
     *  }
     * @param $error
     */
    protected function sendAjaxError($error)
    {
        if ($this->getRequest()->is('json')) {
            $this->setResponse($this->getResponse()->withStatus(500));
            $response = [
                'status' => APP_OFF,
                'msg' => $error->getMessage()
            ];
            $this->set(compact('response'));
            $this->render('/Error/errorjson');
        }
    }

    /**
     * needs to be implemented if $this->AppAuth->authorize = ['Controller'] is used
     */
    public function isAuthorized($user)
    {
        return true;
    }
}

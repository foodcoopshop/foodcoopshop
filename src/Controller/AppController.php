<?php
declare(strict_types=1);

namespace App\Controller;

use App\Lib\OutputFilter\OutputFilter;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use hisorange\BrowserDetect\Parser as Browser;
use App\Services\CartService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppController extends Controller
{

    public $protectEmailAddresses = false;
    protected $AppAuth;
    protected $Customer;
    protected $Manufacturer;

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
            'storage' => 'Session',
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
                $this->Flash->error(__('You_have_been_signed_out_automatically.'));
                $this->AppAuth->logout();
                $this->response = $this->response->withCookie((new Cookie('remember_me')));
                $this->redirect(Configure::read('app.slugHelper')->getHome());
            }
        }
    }

    public function beforeFilter(EventInterface $event)
    {

        $this->validateAuthentication();

        if (!$this->getRequest()->is('json') && !$this->AppAuth->isOrderForDifferentCustomerMode()) {
            $this->loadComponent('FormProtection');
        }

        $isMobile = false;
        if (PHP_SAPI !== 'cli') {
            /** @phpstan-ignore-next-line */
            $isMobile = Browser::isMobile() && !Browser::isTablet();
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

        $this->AppAuth->CartService->setController($this);

        parent::beforeFilter($event);
    }

    public function afterFilter(EventInterface $event)
    {
        parent::afterFilter($event);

        $newOutput = $this->response->getBody()->__toString();
        if ($this->protectEmailAddresses) {
            $newOutput = OutputFilter::protectEmailAdresses($newOutput);
        }
        
        if (Configure::check('app.outputStringReplacements')) {
            $newOutput = OutputFilter::replace($newOutput, Configure::read('app.outputStringReplacements'));
        }
        $this->response = $this->response->withStringBody($newOutput);
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

    public function setCurrentFormAsFormReferer()
    {
        $this->set('referer', $this->getRequest()->getUri()->getPath());
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

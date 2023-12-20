<?php
declare(strict_types=1);

namespace App\Controller;

use App\Services\OutputFilter\OutputFilterService;
use App\Traits\AppRequestAwareTrait;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use hisorange\BrowserDetect\Parser as Browser;

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

    use AppRequestAwareTrait;

    public $protectEmailAddresses = false;
    public $loggedUser = null;
    
    protected $AppAuth;
    protected $Customer;
    protected $Manufacturer;

    public function initialize(): void
    {

        parent::initialize();

        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false
        ]);
        $this->loadComponent('Flash', [
            'clear' => true
        ]);
        $this->loadComponent('String');

        /*
        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $authenticate['BarCode'] = [
                'userModel' => 'Customers',
                'fields' => [
                    'identifier' => 'barCode'
                ],
                'finder' => 'auth' // CustomersTable::findAuth
            ];
        }
        */

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

    public function beforeFilter(EventInterface $event)
    {

        $this->loggedUser = $this->request->getAttribute('identity');
        $this->set('loggedUser', $this->loggedUser);

        $this->setAppRequest($this->request);
        $this->set('isOrderForDifferentCustomerMode', $this->isOrderForDifferentCustomerMode());
        $this->set('isSelfServiceModeByUrl', $this->isSelfServiceModeByUrl());
        $this->set('isSelfServiceModeByReferer', $this->isSelfServiceModeByReferer());

        if (!$this->getRequest()->is('json') && !$this->isOrderForDifferentCustomerMode()) {
            $this->loadComponent('FormProtection');
        }

        $isMobile = false;
        if (PHP_SAPI !== 'cli') {
            /** @phpstan-ignore-next-line */
            $isMobile = Browser::isMobile() && !Browser::isTablet();
        }
        $this->set('isMobile', $isMobile);

        if (0 && $this->AppAuth->isManufacturer()) {
            $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $this->AppAuth->getManufacturerId()
                ]
            ])->first();
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            $this->set('variableMemberFeeForTermsOfUse', $variableMemberFee);
        }

        //$this->AppAuth->CartService->setController($this);

        parent::beforeFilter($event);
    }

    public function afterFilter(EventInterface $event)
    {
        parent::afterFilter($event);

        $newOutput = $this->response->getBody()->__toString();
        if ($this->protectEmailAddresses) {
            $newOutput = OutputFilterService::protectEmailAdresses($newOutput);
        }
        
        if (Configure::check('app.outputStringReplacements')) {
            $newOutput = OutputFilterService::replace($newOutput, Configure::read('app.outputStringReplacements'));
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
            $this->loggedUser = $customer;
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

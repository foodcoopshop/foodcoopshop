<?php

App::uses('Controller', 'Controller');
App::uses('AppEmail', 'Lib');
App::uses('AppPasswordHasher', 'Controller/Component/Auth');

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

    public $components = array(
        'RequestHandler', // to parse xml extensions
        'AppSession', 
        'String',
        'Cookie',  
        'Paginator' => array(
            'maxLimit' => 100000, // eg for retrieving order details for 1 year
            'limit' => 100000
        ),
        'AppAuth' => array(
            'loginAction' => array(
                'plugin' => null,
                'controller' => 'customers',
                'action' => 'login'
            ),
            'unauthorizedRedirect' => false,
            'logoutRedirect' => '/admin/order_details', // important for manufacturer login!
            'authError' => 'Zugriff verweigert, bitte melde dich an.',
            // non acl-authorization: uses function isAuthorized in Controller
            'authorize' => array(
                'Controller'
            ),
            'authenticate' => array(
                'Form' => array(
                    'userModel' => 'Customer',
                    'fields' => array(
                        'username' => 'email',
                        'password' => 'passwd'
                    ),
                    'passwordHasher' => array(
                        'className' => 'App'
                    ),
                    'scope' => array(
                        'Customer.active' => true
                    )
                )
            )
        ),
        'Cart',
        'DbMigration',
    );

    public $helpers = array(
        'Html' => array(
            'className' => 'MyHtml'
        ),
        'Time' => array(
            'className' => 'MyTime'
        ),
        'Session',
        'Form',
        'Menu',
        'Slug',
        'AssetCompress.AssetCompress',
        'Text'
    );

    /**
     * loads configuration from database
     */
    public function loadConfigurations()
    {
        $this->loadModel('Configuration');
        $configurations = $this->Configuration->loadConfigurations();
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->set('appAuth', $this->AppAuth);
        $loggedUser = $this->AppAuth->user();
        $this->set('loggedUser', $loggedUser['firstname'] . ' ' . $loggedUser['lastname']);

        if ($this->name == 'CakeError') {
            $this->layout = 'plain';
        }
    }

    public function beforeFilter()
    {
        $this->loadConfigurations();

        switch($this->DbMigration->doDbMigrations()) {
            case -1:  // always abort what is done and return home.
                $this->redirect('/');
                break;
            case -2:  // abort if not home.
                if ($this->request->here != '/') {  // went away from home?
                    $this->redirect('/');  // return home. DB is unusable, remember?
                }
                break;
            case 1:  // always abort what is done but pick up work
                if ($this->request->is('get')) {
                    $this->redirect($this->request->here);  // redirect to the request URL -> start all over...
                }
                else { // bad luck...input is lost, do not recover from that.
                    $this->redirect('/');
                }
                break;
        }

        // auto login if cookie is set
        if (! $this->AppAuth->loggedIn() && $this->Cookie->read('remember_me_cookie') !== null) {
            $cookie = $this->Cookie->read('remember_me_cookie');
            if (isset($cookie['email']) && isset($cookie['passwd'])) { // not set in cronjobs
                $this->loadModel('Customer');
                $customer = $this->Customer->find('first', array(
                    'conditions' => array(
                        'Customer.email' => $cookie['email'],
                        'Customer.passwd' => $cookie['passwd']
                    )
                ));
                if ($customer && ! $this->AppAuth->login($customer['Customer'])) {
                    $this->redirect($this->AppAuth->logout());
                }
            }
        }

        if ($this->AppAuth->isManufacturer()) {
            $this->loadModel('Manufacturer');
            $manufacturer = $this->Manufacturer->find('first', array(
                'conditions' => array(
                    'Manufacturer.id_manufacturer' => $this->AppAuth->getManufacturerId()
                )
            ));
            $addressOther = $manufacturer['Address']['other'];
            $compensationPercentage = $this->Manufacturer->getCompensationPercentage($addressOther);
            $this->set('compensationPercentageForTermsOfUse', $compensationPercentage);
        }

        $isMobile = false;
        if ($this->request->is('mobile') && !preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $isMobile = true;
        }
        $this->set('isMobile', $isMobile);

        parent::beforeFilter();
    }

    /**
     * keep this method in a controller - does not work with AppAuthComponent::login
     * updates login data (after profile change for customer and manufacturer)
     */
    protected function renewAuthSession()
    {
        $this->loadModel('Customer');
        $customer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $this->AppAuth->getUserId()
            )
        ));
        if (! empty($customer)) {
            $this->AppAuth->login($customer['Customer']);
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

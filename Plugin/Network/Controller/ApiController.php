<?php

App::uses('AppController', 'Controller');

/**
 * ApiController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ApiController extends Controller
{
    public $components = array(
        'RequestHandler',
        'AppAuth' => array(
            'unauthorizedRedirect' => false,
            'authError' => 'Zugriff verweigert, bitte melde dich an.',
            'authorize' => array(
                'Controller'
            ),
            'authenticate' => array(
                'Basic' => array(
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
        'Paginator' => array(
            'maxLimit' => 100000,
            'limit' => 100000
        )
    );

    public function beforeFilter()
    {

        $this->RequestHandler->renderAs($this, 'json');
        AuthComponent::$sessionKey = false; // enables stateless authentification

        $this->request->allowMethod('GET', 'POST', 'OPTIONS');

        $this->response->header('Access-Control-Allow-Origin', '*');
        $this->response->header('Access-Control-Allow-Methods', '*');
        $this->response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        if ($this->request->is('options')) {
            $this->response->send();
            $this->_stop();
        }

        $this->loadModel('Configuration');
        $this->Configuration->loadConfigurations();
    }

    public function isAuthorized($user)
    {
        return $this->AppAuth->isManufacturer();
    }

    private function getProductDetailLinks($productIds)
    {
        $productDetailLinks = array();

        $this->loadModel('Product');
        $this->Product->Behaviors->load('Containable');
        foreach ($productIds as $productId) {
            $product = $this->Product->find('first', array(
                'conditions' => array(
                    'Product.id_product' => $productId,
                ),
                'contain' => array('ProductLang')
            ));
            $productDetailLinks[] = Configure::read('htmlHelper')->link($product['ProductLang']['name'], Configure::read('slugHelper')->getProductDetail($productId, $product['ProductLang']['name']));
        }
        return join(', ', $productDetailLinks);
    }

    public function updateProducts()
    {

        // TODO validation, owner check (AppAuth->getManufacturerId() / $product['Product']['id_manufacturer']
        $products = $this->data['data'];

        $this->loadModel('Product');
        $this->loadModel('CakeActionLog');

        $products2saveForName = array();
        $products2saveForQuantity = array();
        $products2saveForPrice = array();
        $products2saveForStatus = array();

        foreach ($products as $product) {
            if (isset($product['name'])) {
                $products2saveForName[] = array(
                    $product['remoteProductId'] => $product['name']
                );
            }
            if (isset($product['quantity'])) {
                $products2saveForQuantity[] = array(
                    $product['remoteProductId'] => $product['quantity']
                );
            }
            if (isset($product['price'])) {
                $price = $this->Product->getPriceAsFloat($product['price']);
                // increase price if compensation percentage is enabled
//                 $this->loadModel('Manufacturer');
//                 $compensationPercentage = $this->Manufacturer->getOptionCompensationPercentage($this->AppAuth->manufacturer['Manufacturer']['compensation_percentage']);
//                 if ($compensationPercentage > 0) {
//                     $price = $price + round($price * $compensationPercentage / 100, 2);
//                 }
                $products2saveForPrice[] = array(
                    $product['remoteProductId'] => $price
                );
            }
            if (isset($product['active'])) {
                $products2saveForStatus[] = array(
                    $product['remoteProductId'] => (int) $product['active']
                );
            }
        }

        $syncFieldsOk = array();
        $syncFieldsError = array();

        if (empty($products2saveForName) &&
            empty($products2saveForQuantity) &&
            empty($products2saveForPrice) &&
            empty($products2saveForStatus)) {
            $message = 'Es wurden keine Felder zum Synchronisieren angegeben.';
        } else {
            if (!empty($products2saveForName)) {
                $syncFieldsOk[] = 'Name';
                $status = $this->Product->ProductLang->changeName($products2saveForName);
                $productIds = array();
                foreach ($products2saveForName as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForQuantity)) {
                $syncFieldsOk[] = 'Anzahl';
                $status = $this->Product->changeQuantity($products2saveForQuantity);
                $productIds = array();
                foreach ($products2saveForQuantity as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForPrice)) {
                $fieldName = 'Preis';
                try {
                    $status = $this->Product->changePrice($products2saveForPrice);
                    if ($status) {
                        $syncFieldsOk[] = $fieldName;
                        $productIds = array();
                        foreach ($products2saveForPrice as $p) {
                            $productIds[] = key($p);
                        }
                    } else {
                        $syncFieldsError[] = $fieldName;
                    }
                } catch (InvalidParameterException $e) {
                    $syncFieldsError[] = $fieldName;
                }
            }

            if (!empty($products2saveForStatus)) {
                $fieldName = 'Status';
                try {
                    $status = $this->Product->changeStatus($products2saveForStatus);
                    if ($status) {
                        $syncFieldsOk[] = $fieldName;
                        $productIds = array();
                        foreach ($products2saveForStatus as $p) {
                            $productIds[] = key($p);
                        }
                    } else {
                        $syncFieldsError[] = $fieldName;
                    }
                } catch (InvalidParameterException $e) {
                    $syncFieldsError[] = $fieldName;
                }
            }

            $message = '';
            $actionLogMessage = '';
            $errorMessage = '';

            if (count($syncFieldsOk) > 0) {
                $message .= join(', ', $syncFieldsOk) . ' von ' . count($products) . ' '. (count($products) == 1 ? 'Produkt' : 'Produkten') . ' ' . (count($syncFieldsOk) == 1 ? 'wurde' : 'wurden') . ' erfolgreich synchronisiert.';
            }
            if (isset($productIds)) {
                $actionLogMessage = 'Über ' . $this->data['baseDomain'] . ' wurden ' . count($products) . ' ' . (count($products) == 1 ? 'Produkt' : 'Produkte') . ' ('.join(', ', $syncFieldsOk).') geändert: ' . $this->getProductDetailLinks($productIds);
            }
            if (count($syncFieldsError) > 0) {
                $errorMessage .=  '<br /><b>Beim Synchronisieren der Produkte sind Fehler aufgetreten!</b><br />';
                $errorMessage .= '<b>' . join(', ', $syncFieldsError).'</b> ' . (count($syncFieldsError) == 1 ? 'wurde' : 'wurden') . ' <b>nicht</b> aktualisiert.';
                $message .= $errorMessage;
                $actionLogMessage .= $errorMessage;
            }

            if ($actionLogMessage != '') {
                $this->CakeActionLog->customSave('product_remotely_changed', $this->AppAuth->getUserId(), 0, 'products', $actionLogMessage);
            }
        }

        $this->set('data', array(
            'app' => array(
                'name' => $this->getInstallationName(),
                'domain' => Configure::read('app.cakeServerName')
            ),
            'status' => count($syncFieldsError) == 0,
            'msg' => $message
        ));

        $this->set('_serialize', 'data');
    }

    private function getInstallationName()
    {

        return Configure::check('app.db_config_FCS_APP_NAME') ? Configure::read('app.db_config_FCS_APP_NAME') : Configure::read('app.cakeServerName');
    }

    public function getProducts()
    {

        $this->loadModel('Product');

        $pParams = $this->Product->getProductParams($this->AppAuth, '', $this->AppAuth->getManufacturerId(), 'all');
        $preparedProducts = $this->Product->prepareProductsForBackend($this->Paginator, $pParams);

        $this->set('data', array(
                'app' => array(
                    'name' => $this->getInstallationName(),
                    'domain' => Configure::read('app.cakeServerName')
                ),
                'loggedUser' => $this->AppAuth->user(),
                'products' => $preparedProducts
            ));

        $this->set('_serialize', 'data');
    }
}

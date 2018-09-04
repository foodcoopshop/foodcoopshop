<?php

namespace Network\Controller;

use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * ApiController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ApiController extends Controller
{

    public $components = [
        'RequestHandler',
        'AppAuth' => [
            'authError' => ACCESS_DENIED_MESSAGE,
            'authorize' => [
                'Controller'
            ],
            'authenticate' => [
                'Basic' => [
                    'userModel' => 'Customers',
                    'fields' => [
                        'username' => 'email',
                        'password' => 'passwd'
                    ],
                    'passwordHasher' => [
                        'className' => 'App'
                    ],
                    'finder' => 'auth' // CustomersTable::findAuth
                ]
            ],
            // stateless suthentication
            'unauthorizedRedirect' => false,
            'storage' => 'Memory'
        ]
    ];

    public function beforeFilter(Event $event)
    {

        $this->RequestHandler->renderAs($this, 'json');

        $this->getRequest()->allowMethod(['get', 'post', 'delete', 'options']);
        $this->setResponse($this->getResponse()->withHeader('Access-Control-Allow-Origin', '*'));
        $this->setResponse($this->getResponse()->withHeader('Access-Control-Allow-Methods', '*'));
        $this->setResponse($this->getResponse()->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization'));

        if ($this->getRequest()->is('options')) {
            return $this->getResponse();
        }
    }

    public function isAuthorized($user)
    {
        return $this->AppAuth->isManufacturer();
    }

    private function getProductDetailLinks($productsData)
    {
        $productDetailLinks = [];
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        foreach ($productsData as $originalProduct) {
            $productIds = $this->Product->getProductIdAndAttributeId($originalProduct['remoteProductId']);
            $product = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productIds['productId'],
                ],
                'contain' => [
                    'ProductAttributes.ProductAttributeCombinations.Attributes'
                ]
            ])->first();
            if ($productIds['attributeId'] == 0) {
                $linkName = $product->name;
            } else {
                foreach ($product->product_attributes as $attribute) {
                    if ($attribute->id_product_attribute == $productIds['attributeId']) {
                        $linkName = $product->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                    }
                }
            }
            $productDetailLinks[] = Configure::read('app.htmlHelper')->link($linkName, Configure::read('app.slugHelper')->getProductDetail($productIds['productId'], $product->name));
        }
        return join(', ', $productDetailLinks);
    }

    public function updateProducts()
    {

        $productsData = $this->getRequest()->getData('data.data');

        if (empty($productsData)) {
            throw new InvalidParameterException('Keine Produkte vorhanden.');
        }

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');

        $products2saveForName = [];
        $products2saveForQuantity = [];
        $products2saveForPrice = [];
        $products2saveForDeposit = [];
        $products2saveForStatus = [];

        $products = [];
        $attributes = [];

        foreach ($productsData as $product) {

            $productIds = $this->Product->getProductIdAndAttributeId($product['remoteProductId']);

            $manufacturerIsOwner = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productIds['productId'],
                    'Products.id_manufacturer' => $this->AppAuth->getManufacturerId()
                ]
            ])->count();
            if (!$manufacturerIsOwner) {
                throw new InvalidParameterException('Das Produkt ' . $productIds['productId'] . ' ist nicht dem Hersteller ' . $this->AppAuth->getManufacturerName() . ' zugeordnet.');
            }

            if ($productIds['attributeId'] == 0) {
                $products[] = $product;
            } else {
                $attributes[] = $product;
            }

            if (isset($product['name'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForName[] = [
                        $productIds['productId'] => $product['name']
                    ];
                }
            }
            if (isset($product['quantity'])) {
                $products2saveForQuantity[] = [
                    $product['remoteProductId'] => $product['quantity']
                ];
            }
            if (isset($product['price'])) {
                $price = $this->Product->getStringAsFloat($product['price']);

                $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($this->AppAuth->manufacturer->variable_member_fee);
                if ($variableMemberFee > 0) {
                    $price = $this->Manufacturer->increasePriceWithVariableMemberFee($price, $variableMemberFee);
                }

                $products2saveForPrice[] = [
                    $product['remoteProductId'] => $price
                ];
            }
            if (isset($product['deposit'])) {
                $products2saveForDeposit[] = [
                    $product['remoteProductId'] => $this->Product->getStringAsFloat($product['deposit'])
                ];
            }
            if (isset($product['active'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForStatus[] = [
                        $productIds['productId'] => (int) $product['active']
                    ];
                }
            }
        }

        $syncFieldsOk = [];
        $syncFieldsError = [];

        if (empty($products2saveForName) &&
            empty($products2saveForQuantity) &&
            empty($products2saveForPrice) &&
            empty($products2saveForDeposit) &&
            empty($products2saveForStatus)) {
            $message = 'Es wurden keine Felder zum Synchronisieren angegeben.';
        } else {
            if (!empty($products2saveForName)) {
                $syncFieldsOk[] = 'Name';
                $updateStatus = $this->Product->changeName($products2saveForName);
                $productIds = [];
                foreach ($products2saveForName as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForQuantity)) {
                $syncFieldsOk[] = 'Anzahl';
                $updateStatus = $this->Product->changeQuantity($products2saveForQuantity);
                $productIds = [];
                foreach ($products2saveForQuantity as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForPrice)) {
                $fieldName = 'Preis';
                try {
                    $updateStatus = $this->Product->changePrice($products2saveForPrice);
                    if ($updateStatus) {
                        $syncFieldsOk[] = $fieldName;
                        $productIds = [];
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

            if (!empty($products2saveForDeposit)) {
                $syncFieldsOk[] = 'Pfand';
                $updateStatus = $this->Product->changeDeposit($products2saveForDeposit);
                $productIds = [];
                foreach ($products2saveForDeposit as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForStatus)) {
                $fieldName = 'Status';
                try {
                    $updateStatus = $this->Product->changeStatus($products2saveForStatus);
                    if ($updateStatus) {
                        $syncFieldsOk[] = $fieldName;
                        $productIds = [];
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

            $syncronizedProductsString = count($products) . ' '. (count($products) == 1 ? 'Produkt' : 'Produkte');
            $syncronizedAttributesString = count($attributes) . ' '. (count($attributes) == 1 ? 'Variante' : 'Varianten');
            $listOfSyncFieldsOk = ' ('.join(', ', $syncFieldsOk).') ';

            if (count($syncFieldsOk) > 0) {
                $message .= 'Es wurden ' . $syncronizedProductsString . ' und ' . $syncronizedAttributesString  . $listOfSyncFieldsOk . 'erfolgreich synchronisiert.';
            }
            $actionLogMessage = 'Über ' . $this->getRequest()->getData('data.metaData.baseDomain') . ' wurden ' . $syncronizedProductsString . ' und ' . $syncronizedAttributesString . $listOfSyncFieldsOk . 'synchronisiert: ';
            $actionLogMessage .= $this->getProductDetailLinks($productsData);

            if (count($syncFieldsError) > 0) {
                $errorMessage .=  '<br /><b>Beim Synchronisieren der Produkte sind Fehler aufgetreten!</b><br />';
                $errorMessage .= '<b>' . join(', ', $syncFieldsError).'</b> ' . (count($syncFieldsError) == 1 ? 'wurde' : 'wurden') . ' <b>nicht</b> aktualisiert.';
                $message .= $errorMessage;
                $actionLogMessage .= $errorMessage;
            }

            if ($actionLogMessage != '') {
                $this->ActionLog->customSave('product_remotely_changed', $this->AppAuth->getUserId(), 0, 'products', $actionLogMessage);
            }
        }

        $this->set('data', [
            'app' => [
                'name' => $this->getInstallationName(),
                'domain' => Configure::read('app.cakeServerName')
            ],
            'status' => count($syncFieldsError) == 0,
            'msg' => $message
        ]);

        $this->set('_serialize', 'data');
    }

    private function getInstallationName()
    {

        return Configure::check('appDb.FCS_APP_NAME') ? Configure::read('appDb.FCS_APP_NAME') : Configure::read('app.cakeServerName');
    }

    public function getProducts()
    {

        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');

        $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee(
            $this->AppAuth->manufacturer->variable_member_fee
        );
        $preparedProducts = $this->Product->getProductsForBackend($this->AppAuth, '', $this->AppAuth->getManufacturerId(), 'all', '', 0, 0, true);

        $this->set('data', [
            'app' => [
                'name' => $this->getInstallationName(),
                'domain' => Configure::read('app.cakeServerName'),
                'variableMemberFee' => $variableMemberFee
            ],
            'loggedUser' => $this->AppAuth->user(),
            'products' => $preparedProducts
        ]);

        $this->set('_serialize', 'data');
    }
}

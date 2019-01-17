<?php

namespace Network\Controller;

use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
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
                        'className' => 'Fallback',
                        'hashers' => [
                            'Default',
                            'Legacy'
                        ]
                    ],
                    'finder' => 'auth' // CustomersTable::findAuth
                ]
            ],
            // stateless authentication
            'unauthorizedRedirect' => false,
            'storage' => 'Memory'
        ]
    ];

    public function beforeFilter(Event $event)
    {

        // enables basic authentication with php in cgi mode
        if (isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            $ha = base64_decode( substr($_SERVER['HTTP_AUTHORIZATION'],6) );
            if (isset($ha[0]) && isset($ha[1])) {
                list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $ha);
            }
        }
        
        $this->RequestHandler->renderAs($this, 'json');

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
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

        $products2saveForImage = [];
        $products2saveForName = [];
        $products2saveForIsStockProduct = [];
        $products2saveForQuantity = [];
        $products2saveForPrice = [];
        $products2saveForDeposit = [];
        $products2saveForDeliveryRhythm = [];
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
                throw new InvalidParameterException('the product' . $productIds['productId'] . ' is not associated with manufacturer ' . $this->AppAuth->getManufacturerName());
            }

            if ($productIds['attributeId'] == 0) {
                $products[] = $product;
            } else {
                $attributes[] = $product;
            }

            if (isset($product['image'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForImage[] = [
                        $productIds['productId'] => $product['image']
                    ];
                }
            }
            if (isset($product['name'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForName[] = [
                        $productIds['productId'] => $product['name']
                    ];
                }
            }
            
            if (isset($product['is_stock_product'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForIsStockProduct[] = [
                        $productIds['productId'] => $product['is_stock_product']
                    ];
                }
            }
            
            if (isset($product['quantity'])) {
                $product['quantity'] = [
                    'quantity' => $product['quantity']['stock_available_quantity'],
                    'quantity_limit' => $product['quantity']['stock_available_quantity_limit'],
                    'sold_out_limit' => $product['quantity']['stock_available_sold_out_limit']
                ];
                $products2saveForQuantity[] = [
                    $product['remoteProductId'] => $product['quantity']
                ];
            }
            if (isset($product['price'])) {
                
                $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($this->AppAuth->manufacturer->variable_member_fee);
                
                if ($variableMemberFee > 0) {
                    
                    $price = $this->Product->getStringAsFloat($product['price']['gross_price']);
                    $product['price']['gross_price'] = $this->Manufacturer->increasePriceWithVariableMemberFee($price, $variableMemberFee);
                    
                    if (isset($product['price']['unit_product_price_incl_per_unit'])) {
                        $pricePerUnit = $this->Product->getStringAsFloat($product['price']['unit_product_price_incl_per_unit']);
                        $product['price']['unit_product_price_incl_per_unit'] = $this->Manufacturer->increasePriceWithVariableMemberFee($pricePerUnit, $variableMemberFee);
                    }
                    
                }

                if (!isset($product['price']['unit_product_price_per_unit_enabled'])) {
                    $product['price']['unit_product_price_per_unit_enabled'] = 0;
                    $product['price']['unit_product_price_incl_per_unit'] = 0;
                    $product['price']['unit_product_name'] = '';
                    $product['price']['unit_product_amount'] = 0;
                    $product['price']['unit_product_quantity_in_units'] = 0;
                }
                
                $products2saveForPrice[] = [
                    $product['remoteProductId'] => $product['price']
                ];
            }
            
            if (isset($product['deposit'])) {
                $products2saveForDeposit[] = [
                    $product['remoteProductId'] => $this->Product->getStringAsFloat($product['deposit'])
                ];
            }
            
            if (isset($product['delivery_rhythm'])) {
                $products2saveForDeliveryRhythm[] = [
                    $product['remoteProductId'] => $product['delivery_rhythm']
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

        if (empty($products2saveForImage) &&
            empty($products2saveForName) &&
            empty($products2saveForIsStockProduct) &&
            empty($products2saveForQuantity) &&
            empty($products2saveForPrice) &&
            empty($products2saveForDeposit) &&
            empty($products2saveForDeliveryRhythm) &&
            empty($products2saveForStatus)) {
            $message = __d('network', 'No_fields_were_selected_for_synchronizing.');
        } else {
            
            if (!empty($products2saveForImage)) {
                $syncFieldsOk[] = 'Bild';
                $updateStatus = $this->Product->changeImage($products2saveForImage);
                $productIds = [];
                foreach ($products2saveForImage as $p) {
                    $productIds[] = key($p);
                }
            }
            
            if (!empty($products2saveForName)) {
                $syncFieldsOk[] = 'Name';
                $updateStatus = $this->Product->changeName($products2saveForName);
                $productIds = [];
                foreach ($products2saveForName as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForIsStockProduct)) {
                $fieldName = 'Lagerprodukt';
                try {
                    $updateIsStockProduct = $this->Product->changeIsStockProduct($products2saveForIsStockProduct);
                    if ($updateIsStockProduct) {
                        $syncFieldsOk[] = $fieldName;
                        $productIds = [];
                        foreach ($products2saveForIsStockProduct as $p) {
                            $productIds[] = key($p);
                        }
                    } else {
                        $syncFieldsError[] = $fieldName;
                    }
                } catch (InvalidParameterException $e) {
                    $syncFieldsError[] = $fieldName;
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

            if (!empty($products2saveForDeliveryRhythm)) {
                $syncFieldsOk[] = 'Lieferrhythmus';
                $updateStatus = $this->Product->changeDeliveryRhythm($products2saveForDeliveryRhythm);
                $productIds = [];
                foreach ($products2saveForDeliveryRhythm as $p) {
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

            $syncronizedProductsString = count($products) . ' '. (count($products) == 1 ? __d('network', 'product') : __d('network', 'products'));
            $syncronizedAttributesString = count($attributes) . ' '. (count($attributes) == 1 ? __d('network', 'attribute') : __d('network', 'attributes'));
            $listOfSyncFieldsOk = join(', ', $syncFieldsOk);

            if (count($syncFieldsOk) > 0) {
                $message = __d('network', '{0}_and_{1}_({2})_have_been_successfully_synchronized.', [$syncronizedProductsString, $syncronizedAttributesString, $listOfSyncFieldsOk]);
            }
            $actionLogMessage = __d('network', 'Via_{0}_there_have_been_{1}_and_{2}_({3})_successfully_synchronized.', [$this->getRequest()->getData('data.metaData.baseDomain'), $syncronizedProductsString, $syncronizedAttributesString, $listOfSyncFieldsOk]);
            $actionLogMessage .= ' ' . $this->getProductDetailLinks($productsData);

            if (count($syncFieldsError) > 0) {
                $errorMessage .=  '<br /><b>'.__d('network', 'Errors_occurred_while_synchronizing!').'</b><br />';
                $errorMessage .= '<b>';
                if (count($syncFieldsError) == 1) {
                    $errorMessage .=  __d('network', '{0}_has_not_been_updated.', [join(', ', $syncFieldsError)]);
                } else {
                    $errorMessage .=  __d('network', '{0}_have_not_been_updated.', [join(', ', $syncFieldsError)]);
                }
                $errorMessage .= '</b><br />';
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

<?php

namespace Admin\Controller;

use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Filesystem\Folder;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Intervention\Image\ImageManagerStatic as Image;
use Cake\I18n\FrozenTime;

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
class ProductsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'generateProductCards':
                return Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin());
                break;
            case 'index':
            case 'add':
            case 'ajaxGetProductsForDropdown':
                return $this->AppAuth->user();
                break;
            default:
                if (!empty($this->getRequest()->getData('productId'))) {
                    $ids = $this->Product->getProductIdAndAttributeId($this->getRequest()->getData('productId'));
                    $productId = $ids['productId'];
                    $product = $this->Product->find('all', [
                        'conditions' => [
                            'Products.id_product' => $productId
                        ]
                    ])->first();
                    if (empty($product)) {
                        $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                        return false;
                    }
                }

                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                /*
                 * START manufacturer OWNER check
                 */
                if ($this->AppAuth->isManufacturer()) {
                    // param productIds is passed via ajaxCall
                    if (!empty($this->getRequest()->getData('productIds'))) {
                        $productIds = $this->getRequest()->getData('productIds');
                    }
                    // param productId is passed via ajaxCall
                    if (!empty($this->getRequest()->getData('productId'))) {
                        $ids = $this->Product->getProductIdAndAttributeId($this->getRequest()->getData('productId'));
                        $productIds = [$ids['productId']];
                    }
                    // param objectId is passed via ajaxCall
                    if (!empty($this->getRequest()->getData('objectId'))) {
                        $ids = $this->Product->getProductIdAndAttributeId($this->getRequest()->getData('objectId'));
                        $productIds = [$ids['productId']];
                    }
                    // param productId is passed as first argument of url
                    if (!empty($this->getRequest()->getParam('pass')[0])) {
                        $productIds = [$this->getRequest()->getParam('pass')[0]];
                    }
                    if (!isset($productIds)) {
                        return false;
                    }
                    $result = true;
                    foreach($productIds as $productId) {
                        $product = $this->Product->find('all', [
                            'conditions' => [
                                'Products.id_product' => $productId
                            ]
                        ])->first();
                        if (empty($product) || $product->id_manufacturer != $this->AppAuth->getManufacturerId()) {
                            $result = false;
                            break;
                        }
                    }
                    if ($result) {
                        return true;
                    }
                }
                $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                return false;
                break;
        }
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
    }
    
    public function delete()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $productIds = $this->getRequest()->getData('productIds');
        $products = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product IN' => $productIds
            ],
            'contain' => [
                'Manufacturers'
            ]
        ]);
        $preparedProductsForActionLog = [];
        foreach($products as $product) {
            $preparedProductsForActionLog[] = '<b>' . $product->name . '</b>: ID ' . $product->id_product . ',  ' . $product->manufacturer->name;
        }
        
        try {
            // check if open order exist
            $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
            $query = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.product_id IN' => $productIds,
                    'OrderDetails.order_state IN' => [ORDER_STATE_ORDER_PLACED, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER]
                ],
                'contain' => [
                    'Products'
                ]
            ]);
            $query->select(
                [
                    'orderDetailsCount' => $query->func()->count('OrderDetails.product_id'),
                    'productName' => 'Products.name'
                ]
            );
            $query->group('OrderDetails.product_id');
            
            $errors = [];
            if ($query->count() > 0) {
                foreach($query as $orderDetail) {
                    $errors[] = __d('admin', 'The_product_{0}_has_{1,plural,=1{1_open_order} other{#_open_orders}}.',
                        [
                            $orderDetail->productName,
                            $orderDetail->orderDetailsCount
                        ]
                    );
                }
            }
            if (!empty($errors)) {
                $errorString = '<ul><li>' . join('</li><li>', $errors) . '</li></ul>';
                $errorString .= __d('admin', 'Please_try_again_as_soon_as_the_next_invoice_has_been_generated.');
                throw new Exception($errorString);
            }
        } catch (Exception $e) {
            $this->sendAjaxError($e);
        }
    
        // 1) set field active to -1
        $this->Product->updateAll([
            'active' => APP_DEL,
            'modified' => FrozenTime::now() // timestamp behavior does not work here...
        ], [
            'id_product IN' => $productIds
        ]);
        
        // 2) delete image
        foreach($productIds as $productId) {
            $this->Product->changeImage(
                [
                    [$productId => 'no-image']
                ]
            );
        }
        
        $message = __d('admin', '{0,plural,=1{1_product_was} other{#_products_were}}_deleted_successfully.', [
            count($productIds)
        ]);
        $this->Flash->success($message);
        $this->ActionLog->customSave('product_deleted', $this->AppAuth->getUserId(), 0, 'products', $message . '<br />' . join('<br />', $preparedProductsForActionLog));
        
        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        
    }
    
    public function generateProductCards()
    {
        $productIds = h($this->getRequest()->getQuery('productIds'));
        $productIds = explode(',', $productIds);
        
        if (empty($productIds)) {
            throw new InvalidParameterException('no product id passed');
        }
        
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $products = $this->Product->getProductsForBackend($this->AppAuth, $productIds, 'all', 'all', '', 0, 0, true);
        
        $preparedProducts = [];
        foreach($products as &$product) {
            if (!empty($product->product_attributes)) {
                // avoid rendering main product if product has attributes
                continue;
            }
            $price = Configure::read('app.numberHelper')->formatAsCurrency($product->gross_price);
            if (!empty($product->unit) && $product->unit->price_per_unit_enabled) {
                $price = Configure::read('app.pricePerUnitHelper')->getPricePerUnitBaseInfo($product->unit->price_incl_per_unit, $product->unit->name, $product->unit->amount);
                if (!preg_match('/main-product/', $product->row_class)) {
                    $product->name = $product->nameForBarcodePdf;
               }
            }
            if (preg_match('/main-product/', $product->row_class)) {
                $product->bar_code .= '0000'; 
            }
            $product->prepared_price = $price;
            $preparedProducts[] = $product;
        }
        
        $this->set('products', $preparedProducts);
    }
    

    public function ajaxGetProductsForDropdown($manufacturerId = 0)
    {
        $this->RequestHandler->renderAs($this, 'json');

        $products = $this->Product->getForDropdown($this->AppAuth, $manufacturerId);
        $productsForDropdown = [];
        foreach ($products as $key => $ps) {
            $productsForDropdown[] = '<optgroup label="' . $key . '">';
            foreach ($ps as $pId => $p) {
                $productsForDropdown[] = '<option value="' . $pId . '">' . $p . '</option>';
            }
            $productsForDropdown[] = '</optgroup>';
        }

        $this->set([
            'status' => 1,
            'dropdownData' => join('', $productsForDropdown),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'dropdownData']);
    }

    /**
     * deletes both db entries and physical files (thumbs)
     *
     * @param int $productId
     */
    public function deleteImage($productId)
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $productId = (int) $productId;

        if ($productId == 0 || $productId == '') {
            $message = 'Product ID not correct: ' . $productId;
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Images',
                'Manufacturers'
            ]
        ])->first();
        
        $this->Product->changeImage(
            [
                [$productId => 'no-image']
            ]
        );

        $actionLogMessage = __d('admin', 'Image_ID_{0}_from_manufacturer_{1}_was_deleted_successfully_Product_{1}_Manufacturer_{2}.', [
            $product->image->id_image,
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>'
        ]);

        $this->Flash->success($actionLogMessage);
        $this->ActionLog->customSave('product_image_deleted', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

    public function saveUploadedImageProduct()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $productId = $this->getRequest()->getData('objectId');
        $filename = $this->getRequest()->getData('filename');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Images',
                'Manufacturers'
            ]
        ])->first();

        if (empty($product->image)) {
            // product does not yet have image => create the necessary record
            $image = $this->Product->Images->save(
                $this->Product->Images->newEntity(
                    ['id_product' => $productId]
                )
            );
        } else {
            $image = $product->image;
        }

        // not (yet) implemented for attributes, only for productIds!
        $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($image->id_image);
        $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);

        // recursively create path
        $dir = new Folder();
        $dir->create($thumbsPath);
        $dir->chmod($thumbsPath, 0755);

        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $physicalImage = Image::make(WWW_ROOT . $filename);
            // make portrait images smaller
            if ($physicalImage->getHeight() > $physicalImage->getWidth()) {
                $thumbSize = round($thumbSize * ($physicalImage->getWidth() / $physicalImage->getHeight()), 0);
            }
            $physicalImage->widen($thumbSize);
            $thumbsFileName = $thumbsPath . DS . $image->id_image . $options['suffix'] . '.' . $extension;
            $physicalImage->save($thumbsFileName);
        }

        $actionLogMessage = __d('admin', 'A_new_image_was_uploaded_to_product_{0}_from_manufacturer_{1}.', [
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>'
        ]);
        $this->Flash->success($actionLogMessage);
        $this->ActionLog->customSave('product_image_added', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'success',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function deleteProductAttribute($productId, $productAttributeId)
    {

        // get new data
        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        ])->first();

        $attributeName = '';
        foreach ($oldProduct->product_attributes as $attribute) {
            if ($attribute->product_attribute_combination->id_product_attribute == $productAttributeId) {
                $attributeName = $attribute->product_attribute_combination->attribute->name;
                break;
            }
        }

        $this->Product->deleteProductAttribute($productId, $productAttributeId);

        $actionLogMessage = __d('admin', 'The_attribute_{0}_of_the_product_{1}_from_manufacturer_{2}_was_successfully_deleted.', [
            '<b>' . $attributeName . '</b>',
            '<b>' . $oldProduct->name . '</b>',
            '<b>' . $oldProduct->manufacturer->name . '</b>'
        ]);
        $this->Flash->success($actionLogMessage);
        $this->ActionLog->customSave('product_attribute_deleted', $this->AppAuth->getUserId(), $oldProduct->id_product, 'products', $actionLogMessage);

        $this->redirect($this->referer());
    }

    public function addProductAttribute($productId, $productAttributeId)
    {
        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        $this->Product->ProductAttributes->add($productId, $productAttributeId);

        // get new data
        $newProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        ])->first();
        foreach ($newProduct->product_attributes as $attribute) {
            if ($attribute->product_attribute_combination->id_attribute == $productAttributeId) {
                $productAttributeIdForHighlighting = $attribute->product_attribute_combination->id_product_attribute;
            }
        }
        $this->getRequest()->getSession()->write('highlightedRowId', $productId . '-' . $productAttributeIdForHighlighting);

        $actionLogMessage = __d('admin', 'The_attribute_{0}_for_the_product_{1}_from_manufacturer_{2}_was_successfully_created.', [
            '<b>' . $attribute->product_attribute_combination->attribute->name . '</b>',
            '<b>' . $oldProduct->name . '</b>',
            '<b>' . $oldProduct->manufacturer->name . '</b>'
        ]);
        $this->Flash->success($actionLogMessage);
        $this->ActionLog->customSave('product_attribute_added', $this->AppAuth->getUserId(), $oldProduct->id_product, 'products', $actionLogMessage);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

    public function add($manufacturerId)
    {

        // if logged user is manufacturer, then get param manufacturer id is NOT used
        // but logged user id for security reasons
        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();

        if (empty($manufacturer)) {
            throw new RecordNotFoundException('manufacturer not existing');
        }

        $newProduct = $this->Product->add($manufacturer);

        $messageString = __d('admin', 'A_new_product_was_created_for_{0}.', ['<b>' . $manufacturer->name . '</b>']);
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_added', $this->AppAuth->getUserId(), $newProduct->id_product, 'products', $messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $newProduct->id_product);
        $this->redirect($this->referer());
    }
    
    public function editDeliveryRhythm() 
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));
        
        $productIds = $this->getRequest()->getData('productIds');
        $deliveryRhythmTypeCombined = $this->getRequest()->getData('deliveryRhythmType');
        $deliveryRhythmFirstDeliveryDay = $this->getRequest()->getData('deliveryRhythmFirstDeliveryDay');
        $deliveryRhythmOrderPossibleUntil = $this->getRequest()->getData('deliveryRhythmOrderPossibleUntil');
        $deliveryRhythmSendOrderListWeekday = $this->getRequest()->getData('deliveryRhythmSendOrderListWeekday');
        $deliveryRhythmSendOrderListDay = $this->getRequest()->getData('deliveryRhythmSendOrderListDay');
        
        $splittedDeliveryRhythmType = explode('-', $deliveryRhythmTypeCombined);
        
        $singleEditMode = false;
        if (count($productIds) == 1) {
            $singleEditMode = true;
            $productId = $productIds[0];
        }
        
        if ($singleEditMode) {
            $oldProduct = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productId
                ],
                'contain' => [
                    'Manufacturers'
                ]
            ])->first();
        }
        
        $deliveryRhythmCount = $splittedDeliveryRhythmType[0];
        $deliveryRhythmType = $splittedDeliveryRhythmType[1];
        
        $product2update = [
            'delivery_rhythm_count' => $deliveryRhythmCount,
            'delivery_rhythm_type' => $deliveryRhythmType
        ];
        
        $isFirstDeliveryDayMandatory = in_array($deliveryRhythmTypeCombined, ['0-individual', '2-week', '4-week']);
        if ($deliveryRhythmFirstDeliveryDay != '' || $isFirstDeliveryDayMandatory) {
            $product2update['delivery_rhythm_first_delivery_day'] = Configure::read('app.timeHelper')->formatToDbFormatDate($deliveryRhythmFirstDeliveryDay);
        }
        if ($deliveryRhythmFirstDeliveryDay == '' && !$isFirstDeliveryDayMandatory) {
            $product2update['delivery_rhythm_first_delivery_day'] = '';
        }
        
        $product2update['delivery_rhythm_order_possible_until'] = '';
        $product2update['delivery_rhythm_send_order_list_day'] = '';
        if ($deliveryRhythmSendOrderListWeekday == '') {
            $deliveryRhythmSendOrderListWeekday = Configure::read('app.timeHelper')->getNthWeekdayBeforeWeekday(1, Configure::read('app.timeHelper')->getSendOrderListsWeekday());
        }
        $product2update['delivery_rhythm_send_order_list_weekday'] = Configure::read('app.timeHelper')->getNthWeekdayAfterWeekday(1, $deliveryRhythmSendOrderListWeekday);
        
        if (in_array($deliveryRhythmTypeCombined, ['0-individual'])) {
            $product2update['delivery_rhythm_order_possible_until'] = Configure::read('app.timeHelper')->formatToDbFormatDate($deliveryRhythmOrderPossibleUntil);
            if ($deliveryRhythmSendOrderListDay != '') {
                $product2update['delivery_rhythm_send_order_list_day'] = Configure::read('app.timeHelper')->formatToDbFormatDate($deliveryRhythmSendOrderListDay);
            }
        }
        
        try {
            
            $products2update = [];
            foreach($productIds as $productId) {
                $products2update[] = [
                    $productId => $product2update
                ];
            }
            
            $this->Product->changeDeliveryRhythm($products2update);
            
            $additionalMessages = [];
            if ($deliveryRhythmFirstDeliveryDay != '') {
                if ($product2update['delivery_rhythm_order_possible_until'] != '') {
                    $additionalMessages[] = __d('admin', 'Order_possible_until') . ': <b>'. Configure::read('app.timeHelper')->formatToDateShort($deliveryRhythmOrderPossibleUntil) . '</b>';
                }
            }
            
            if ($deliveryRhythmType == 'individual') {
                if ($product2update['delivery_rhythm_send_order_list_day'] != '') {
                    $additionalMessages[] = __d('admin', 'Send_order_lists_day') . ': <b>'. Configure::read('app.timeHelper')->formatToDateShort($deliveryRhythmSendOrderListDay) . '</b>';
                } else {
                    $additionalMessages[] = __d('admin', 'Order_list_is_not_sent');
                }
            } else {
                if ($product2update['delivery_rhythm_send_order_list_weekday'] != Configure::read('app.timeHelper')->getSendOrderListsWeekday()) {
                    $additionalMessages[] =  __d('admin', 'Last_order_weekday') . ': <b>' . Configure::read('app.timeHelper')->getWeekdayName(
                        $deliveryRhythmSendOrderListWeekday) . ' ' . __d('admin', 'midnight')
                        . '</b>';
                }
            }
            
            if ($deliveryRhythmFirstDeliveryDay != '') {
                $deliveryDayMessage = '';
                if ($deliveryRhythmType == 'individual') {
                    $deliveryDayMessage .= __d('admin', 'Delivery_day');
                } else {
                    $deliveryDayMessage .= __d('admin', 'First_delivery_day');
                }
                $deliveryDayMessage .= ': <b>'. Configure::read('app.timeHelper')->formatToDateShort($deliveryRhythmFirstDeliveryDay) . '</b>';
                $additionalMessages[] = $deliveryDayMessage;
            }
            
            if ($singleEditMode) {
                $messageString = __d('admin', 'The_delivery_rhythm_of_the_product_{0}_from_manufacturer_{1}_was_changed_successfully_to_{2}.', [
                    '<b>' . $oldProduct->name . '</b>',
                    '<b>' . $oldProduct->manufacturer->name . '</b>',
                    '<b>' . Configure::read('app.htmlHelper')->getDeliveryRhythmString(
                        $oldProduct->is_stock_product && $oldProduct->manufacturer->stock_management_enabled,
                        $deliveryRhythmType,
                        $deliveryRhythmCount
                    ) . '</b>'
                ]);
                if (!empty($additionalMessages)) {
                    $messageString .= ' ' . join(', ', $additionalMessages);
                }
                $this->ActionLog->customSave('product_delivery_rhythm_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);
                $this->getRequest()->getSession()->write('highlightedRowId', $productId);
            } else {
                $messageString = __d('admin', 'Delivery_rhythm_of_{0}_products_has_been_changed_successfully_to_{1}.', [
                    count($productIds),
                    '<b>' . Configure::read('app.htmlHelper')->getDeliveryRhythmString(false, $deliveryRhythmType, $deliveryRhythmCount) . '</b>'
                ]);
                if (!empty($additionalMessages)) {
                    $messageString .= ' ' . join(', ', $additionalMessages);
                }
                $this->ActionLog->customSave('product_delivery_rhythm_changed', $this->AppAuth->getUserId(), 0, 'products', $messageString . ' Ids: ' . join(', ', $productIds));
            }
            
            $this->Flash->success($messageString);
            
            $this->set([
                'status' => 1,
                'msg' => __d('admin', 'Saving_successful.'),
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }
        
    }

    public function editTax()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $productId = (int) $this->getRequest()->getData('productId');
        $taxId = (int) $this->getRequest()->getData('taxId');

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Taxes',
                'ProductAttributes',
                'Manufacturers'
            ]
        ])->first();

        if (empty($oldProduct->tax)) {
            $oldProduct->tax = (object) [
                'rate' => 0
            ];
        }

        if ($taxId != $oldProduct->id_tax) {
            $product2update = [
                'id_tax' => $taxId
            ];

            $this->Product->save(
                $this->Product->patchEntity($oldProduct, $product2update)
            );

            if (! empty($oldProduct->product_attributes)) {
                // update net price of all attributes
                foreach ($oldProduct->product_attributes as $attribute) {
                    // netPrice needs to be calculated new - product tax has been saved above...
                    $newNetPrice = $this->Product->getNetPriceAfterTaxUpdate($productId, $attribute->price, $oldProduct->tax->rate);
                    $this->Product->ProductAttributes->updateAll([
                        'price' => $newNetPrice
                    ], [
                        'id_product_attribute' => $attribute->id_product_attribute
                    ]);
                }
            } else {
                // update price of product without attributes
                $newNetPrice = $this->Product->getNetPriceAfterTaxUpdate($productId, $oldProduct->price, $oldProduct->tax->rate);
                $product2update = [
                    'price' => $newNetPrice
                ];
                $this->Product->save(
                    $this->Product->patchEntity($oldProduct, $product2update)
                );
            }

            $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
            $tax = $this->Tax->find('all', [
                'conditions' => [
                    'Taxes.id_tax' => $taxId
                ]
            ])->first();

            if (! empty($tax)) {
                $taxRate = Configure::read('app.numberHelper')->formatTaxRate($tax->rate);
            } else {
                $taxRate = 0; // 0 % does not have record in tax
            }

            if (! empty($oldProduct->tax)) {
                $oldTaxRate = Configure::read('app.numberHelper')->formatTaxRate($oldProduct->tax->rate);
            } else {
                $oldTaxRate = 0; // 0 % does not have record in tax
            }

            $messageString = __d('admin', 'The_tax_rate_of_product_{0}_from_manufacturer_{1}_was_changed_from_{2}_to_{3}.', ['<b>' . $oldProduct->name . '</b>', '<b>' . $oldProduct->manufacturer->name . '</b>', $oldTaxRate . '%', $taxRate . '%']);
            $this->ActionLog->customSave('product_tax_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);
        } else {
            $messageString = __d('admin', 'Nothing_changed.');
        }

        $this->Flash->success($messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => __d('admin', 'Saving_successful.'),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function editCategories()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $productId = (int) $this->getRequest()->getData('productId');
        $selectedCategories = [];
        if (!empty($this->getRequest()->getData('selectedCategories'))) {
            $selectedCategories = $this->getRequest()->getData('selectedCategories');
        }

        $selectedCategories[] = Configure::read('app.categoryAllProducts'); // always add 'all-products'
        $selectedCategories = array_unique($selectedCategories);

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        $this->CategoryProduct = TableRegistry::getTableLocator()->get('CategoryProducts');
        $this->CategoryProduct->deleteAll([
            'id_product' => $productId
        ]);

        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $selectedCategoryNames = [];
        foreach ($selectedCategories as $selectedCategory) {
            // only add if entry of passed id exists in category table
            $oldCategory = $this->Category->find('all', [
                'conditions' => [
                    'Categories.id_category' => $selectedCategory
                ]
            ])->first();
            if (! empty($oldCategory)) {
                // do not track "all-products"
                if ($selectedCategory != Configure::read('app.categoryAllProducts')) {
                    $selectedCategoryNames[] = $oldCategory->name;
                }
                $sql = 'INSERT INTO ' . $this->CategoryProduct->getTable() . ' (`id_product`, `id_category`) VALUES(' . $productId . ', ' . $selectedCategory . ');';
                $this->CategoryProduct->getConnection()->query($sql);
            }
        }

        $messageString = __d('admin', 'The_categories_of_the_product_{0}_from_manufacturer_{1}_have_been_changed:_{2}', ['<b>' . $oldProduct->name . '</b>', '<b>' . $oldProduct->manufacturer->name . '</b>', join(', ', $selectedCategoryNames)]);
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_categories_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => __d('admin', 'Saving_successful.'),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }
    
    public function editIsStockProduct()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $originalProductId = $this->getRequest()->getData('productId');
        
        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];
        
        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'StockAvailables',
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        ])->first();
        
        try {
            $this->Product->changeIsStockProduct(
                [
                    [
                        $originalProductId => $this->getRequest()->getData('isStockProduct')
                    ]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }
        
        $this->Flash->success(__d('admin', 'The_product_{0}_was_changed_successfully_to_a_stock_product.', ['<b>' . $oldProduct->name . '</b>']));
        
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);
        
        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function editQuantity()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $originalProductId = $this->getRequest()->getData('productId');

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'StockAvailables',
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        ])->first();

        if ($ids['attributeId'] > 0) {
            // override values for messages
            foreach ($oldProduct->product_attributes as $attribute) {
                if ($attribute->id_product_attribute != $ids['attributeId']) {
                    continue;
                }
                $oldProduct->name = $oldProduct->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                $oldProduct->stock_available = $attribute->stock_available;
            }
        }

        try {
            $object2save = [
                'quantity' => $this->getRequest()->getData('quantity'),
                'always_available' => $this->getRequest()->getData('alwaysAvailable'),
                'default_quantity_after_sending_order_lists' => $this->getRequest()->getData('defaultQuantityAfterSendingOrderLists'),
            ];
            if (in_array('quantityLimit', array_keys($this->getRequest()->getData()))) {
                $object2save['quantity_limit'] = $this->getRequest()->getData('quantityLimit');
            }
            if (in_array('soldOutLimit', array_keys($this->getRequest()->getData()))) {
                $object2save['sold_out_limit'] = $this->getRequest()->getData('soldOutLimit');
            }
            $this->Product->changeQuantity(
                [
                    [
                        $originalProductId => $object2save
                    ]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $this->Flash->success(__d('admin', 'The_amount_of_the_product_{0}_was_changed_successfully.', ['<b>' . $oldProduct->name . '</b>']));

        $entity = $this->Product->StockAvailables->patchEntity($oldProduct->stock_available, $object2save);
        if ($entity->isDirty()) {
            $this->ActionLog->customSave(
                'product_quantity_changed',
                $this->AppAuth->getUserId(),
                $productId,
                'products',
                __d('admin', 'The_amount_of_the_product_{0}_from_manufacturer_{1}_was_changed.', ['<b>' . $oldProduct->name . '</b>', '<b>' . $oldProduct->manufacturer->name . '</b>'])
            );
        }
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function editPrice()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $originalProductId = $this->getRequest()->getData('productId');

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'ProductAttributes.UnitProductAttributes',
                'UnitProducts'
            ]
        ])->first();

        if ($ids['attributeId'] > 0) {
            // override values for messages
            foreach ($oldProduct->product_attributes as $attribute) {
                if ($attribute->id_product_attribute != $ids['attributeId']) {
                    continue;
                }
                $oldProduct->name = $oldProduct->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                $oldProduct->price = $attribute->price;
                $oldProduct->unit_product = $attribute->unit_product_attribute;
            }
        }

        try {
            $this->Product->changePrice(
                [
                    [
                        $originalProductId => [
                            'gross_price' => $this->getRequest()->getData('price'),
                            'unit_product_price_incl_per_unit' => $this->getRequest()->getData('priceInclPerUnit'),
                            'unit_product_name' => $this->getRequest()->getData('priceUnitName'),
                            'unit_product_amount' => $this->getRequest()->getData('priceUnitAmount'),
                            'unit_product_quantity_in_units' => $this->getRequest()->getData('priceQuantityInUnits'),
                            'unit_product_price_per_unit_enabled' => $this->getRequest()->getData('pricePerUnitEnabled')
                        ]
                    ]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $price = Configure::read('app.numberHelper')->getStringAsFloat($this->getRequest()->getData('price'));
        
        $this->Flash->success(__d('admin', 'The_price_of_the_product_{0}_was_changed_successfully.', ['<b>' . $oldProduct->name . '</b>']));
        if (!empty($oldProduct->unit_product) && $oldProduct->unit_product->price_per_unit_enabled) {
            $oldPrice = Configure::read('app.pricePerUnitHelper')->getPricePerUnitBaseInfo($oldProduct->unit_product->price_incl_per_unit, $oldProduct->unit_product->name, $oldProduct->unit_product->amount);
        } else {
            $oldPrice = Configure::read('app.numberHelper')->formatAsCurrency($this->Product->getGrossPrice($productId, $oldProduct->price));
        }

        if ($this->getRequest()->getData('pricePerUnitEnabled')) {
            $newPrice = Configure::read('app.pricePerUnitHelper')->getPricePerUnitBaseInfo(Configure::read('app.numberHelper')->getStringAsFloat($this->getRequest()->getData('priceInclPerUnit')), $this->getRequest()->getData('priceUnitName'), $this->getRequest()->getData('priceUnitAmount'));
        } else {
            $newPrice = Configure::read('app.numberHelper')->formatAsCurrency($price);
        }

        $actionLogMessage = __d('admin', 'The_price_of_the_product_{0}_from_manufacturer_{1}_was_changed_from_{2}_to_{3}.', [
            '<b>' . $oldProduct->name . '</b>',
            '<b>' . $oldProduct->manufacturer->name . '</b>',
            $oldPrice,
            $newPrice
        ]);

        $this->ActionLog->customSave('product_price_changed', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        
    }

    public function editDeposit()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $originalProductId = $this->getRequest()->getData('productId');

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'DepositProducts',
                'ProductAttributes.DepositProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        ])->first();

        try {
            $this->Product->changeDeposit(
                [
                    [$originalProductId => $this->getRequest()->getData('deposit')]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $depositEntity = $oldProduct->deposit_product;
        $productName = $oldProduct->name;

        if ($ids['attributeId'] > 0) {
            $attributeName = '';
            foreach ($oldProduct->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $ids['attributeId']) {
                    $attributeName = $attribute->product_attribute_combination->attribute->name;
                    $depositEntity = $attribute->deposit_product_attribute;
                    break;
                }
            }
            $productName .= ' ('.__d('admin', 'Attribute').': '.$attributeName.')';
        }

        $oldDeposit = 0;
        if (!empty($depositEntity->deposit)) {
            $oldDeposit = $depositEntity->deposit;
        }
        $deposit = Configure::read('app.numberHelper')->getStringAsFloat($this->getRequest()->getData('deposit'));

        $actionLogMessage = __d('admin', 'The_deposit_of_the_product_{0}_was_changed_from_{1}_to_{2}.', [
            '<b>' . $productName . '</b>',
            Configure::read('app.numberHelper')->formatAsCurrency($oldDeposit),
            Configure::read('app.numberHelper')->formatAsCurrency($deposit)
        ]);

        $this->ActionLog->customSave('product_deposit_changed', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);

        $this->Flash->success($actionLogMessage);
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function editName()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $productId = $this->getRequest()->getData('productId');

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        try {
            $this->Product->changeName(
                [
                    [$productId => [
                        'name' => $this->getRequest()->getData('name'),
                        'description' => $this->getRequest()->getData('description'),
                        'description_short' => $this->getRequest()->getData('descriptionShort'),
                        'unity' => $this->getRequest()->getData('unity'),
                        'is_declaration_ok' => $this->getRequest()->getData('isDeclarationOk')
                    ]]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $this->Flash->success(__d('admin', 'The_product_was_changed_successfully.'));

        if ($this->getRequest()->getData('name') != $oldProduct->name) {
            $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_was_renamed_to_{2}.', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                '<i>"' . $this->getRequest()->getData('name') . '"</i>'
            ]);
            $this->ActionLog->customSave('product_name_changed', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);
        }
        if ($this->getRequest()->getData('unity') != $oldProduct->unity) {
            $actionLogMessage = __d('admin', 'The_unity_of_the_product_{0}_from_manufacturer_{1}_was_changed_to_{2}.', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                '<i>"' . $this->getRequest()->getData('unity') . '"</i>'
            ]);
            $this->ActionLog->customSave('product_unity_changed', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);
        }
        if ($this->getRequest()->getData('description') != $oldProduct->description) {
            $actionLogMessage = __d('admin', 'The_description_of_the_product_{0}_from_manufacturer_{1}_was_changed:_{2}', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                '<div class="changed">' . $this->getRequest()->getData('description') . ' </div>'
            ]);
            $this->ActionLog->customSave('product_description_changed', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);
        }
        if ($this->getRequest()->getData('descriptionShort') != $oldProduct->description_short) {
            $actionLogMessage = __d('admin', 'The_short_description_of_the_product_{0}_from_manufacturer_{1}_was_changed:_{2}', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                '<div class="changed">' . $this->getRequest()->getData('descriptionShort') . ' </div>'
            ]);
            $this->ActionLog->customSave('product_description_short_changed', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);
        }

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

    public function index()
    {
        $productId = '';
        if (! empty($this->getRequest()->getQuery('productId'))) {
            $productId = h($this->getRequest()->getQuery('productId'));
        }
        $this->set('productId', $productId);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        }

        // always filter by manufacturer id so that no other products than the own are shown
        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }
        $this->set('manufacturerId', $manufacturerId);

        $active = 'all'; // default value
        if (in_array('active', array_keys($this->getRequest()->getQueryParams()))) {
            $active = h($this->getRequest()->getQuery('active'));
        }
        $this->set('active', $active);

        $categoryId = ''; // default value
        if (!empty($this->getRequest()->getQuery('categoryId'))) {
            $categoryId = h($this->getRequest()->getQuery('categoryId'));
        }
        $this->set('categoryId', $categoryId);

        $isQuantityMinFilterSet = 0; // default value
        if (!empty($this->getRequest()->getQuery('isQuantityMinFilterSet'))) {
            $isQuantityMinFilterSet = h($this->getRequest()->getQuery('isQuantityMinFilterSet'));
        }
        $this->set('isQuantityMinFilterSet', $isQuantityMinFilterSet);

        $isPriceZero = 0; // default value
        if (!empty($this->getRequest()->getQuery('isPriceZero'))) {
            $isPriceZero = h($this->getRequest()->getQuery('isPriceZero'));
        }
        $this->set('isPriceZero', $isPriceZero);

        if ($manufacturerId != '') {
            $preparedProducts = $this->Product->getProductsForBackend($this->AppAuth, $productId, $manufacturerId, $active, $categoryId, $isQuantityMinFilterSet, $isPriceZero, false, $this);
        } else {
            $preparedProducts = [];
        }
        $this->set('products', $preparedProducts);

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $this->Attribute = TableRegistry::getTableLocator()->get('Attributes');
        $this->set('attributesForDropdown', $this->Attribute->getForDropdown());
        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $this->set('categoriesForSelect', $this->Category->getForSelect());
        $manufacturersForDropdown = ['all' => __d('admin', 'All_manufacturers')];
        $manufacturersForDropdown = array_merge($manufacturersForDropdown, $this->Product->Manufacturers->getForDropdown());
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());

        if ($manufacturerId > 0) {
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $manufacturerId
                ]
            ])
            ->select($this->Product->Manufacturers)
            ->first();
            $this->set('manufacturer', $manufacturer);
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            $this->set('variableMemberFee', $variableMemberFee);
        }
        
        $advancedStockManagementEnabled = $manufacturerId == 'all' || (!empty($manufacturer) && $manufacturer->stock_management_enabled);
        $this->set('advancedStockManagementEnabled', $advancedStockManagementEnabled);

        $this->set('title_for_layout', __d('admin', 'Products'));

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED') && $this->AppAuth->isManufacturer()) {
            $this->SyncManufacturer = TableRegistry::getTableLocator()->get('Network.SyncManufacturers');
            $this->SyncDomain = TableRegistry::getTableLocator()->get('Network.SyncDomains');
            $this->viewBuilder()->setHelpers(['Network.Network']);
            $isAllowedToUseAsMasterFoodcoop = $this->SyncManufacturer->isAllowedToUseAsMasterFoodcoop($this->AppAuth);
            $syncDomains = $this->SyncDomain->getActiveManufacturerSyncDomains($this->AppAuth->manufacturer->enabled_sync_domains);
            $showSyncProductsButton = $isAllowedToUseAsMasterFoodcoop && count($syncDomains) > 0;
            $this->set('showSyncProductsButton', $showSyncProductsButton);
        }
    }

    public function changeDefaultAttributeId($productId, $productAttributeId)
    {
        $productId = (int) $productId;
        $productAttributeId = (int) $productAttributeId;

        $this->Product->changeDefaultAttributeId($productId, $productAttributeId);

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers',
            ]
        ])->first();

        $productAttribute = $this->Product->ProductAttributes->find('all', [
            'conditions' => [
                'ProductAttributes.id_product_attribute' => $productAttributeId
            ],
            'contain' => [
                'ProductAttributeCombinations.Attributes'
            ]
        ])->first();

        $actionLogMessage = __d('admin', 'The_default_attribute_of_the_product_{0}_from_manufacturer_{1}_was_changed_to_{2}.', [
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>',
            '<b>' . $productAttribute->product_attribute_combination->attribute->name . '</b>'
        ]);
        $this->Flash->success($actionLogMessage);
        $this->ActionLog->customSave('product_default_attribute_changed', $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);

        $this->redirect($this->referer());
    }

    public function changeNewStatus($productId, $status)
    {
        $status = (int) $status;

        if (! in_array($status, [
            APP_OFF,
            APP_ON
        ])) {
            throw new InvalidParameterException('New status needs to be 0 or 1: ' . $status);
        }

        if ($status == 1) {
            $newCreated = 'NOW()';
        } else {
            $newCreated = 'DATE_ADD(NOW(), INTERVAL -8 DAY)';
        }

        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $sql = "UPDATE ".$this->Product->getTable()." p 
                SET p.created  = " . $newCreated . "
                WHERE p.id_product = " . $productId . ";";
        $result = $this->Product->getConnection()->query($sql);

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        $actionLogType = 'product_set_to_old';
        $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_is_not_shown_as_new_any_more.', [
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>'
        ]);
        if ($status) {
            $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_is_shown_as_new_from_now_on_for_the_next_{2}_days.', [
                '<b>' . $product->name . '</b>',
                '<b>' . $product->manufacturer->name . '</b>',
                Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW')
            ]);
            $actionLogType = 'product_set_to_new';
        }

        $this->Flash->success($actionLogMessage);
        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

    public function changeStatus($productId, $status)
    {

        $this->Product->changeStatus(
            [
                [$productId => (int) $status]
            ]
        );

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_was_deactivated.', [
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>'
        ]);
        $actionLogType = 'product_set_inactive';
        if ($status) {
            $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_was_activated.', [
                '<b>' . $product->name . '</b>',
                '<b>' . $product->manufacturer->name . '</b>'
            ]);
            $actionLogType = 'product_set_active';
            $this->getRequest()->getSession()->write('highlightedRowId', $productId);
        }

        $this->Flash->success($actionLogMessage);

        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $productId, 'products', $actionLogMessage);

        $this->redirect($this->referer());
    }
}

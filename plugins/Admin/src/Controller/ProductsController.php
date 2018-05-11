<?php

namespace Admin\Controller;

use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * ProductsController
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
class ProductsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
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
                    // param productId is passed via ajaxCall
                    if (!empty($this->getRequest()->getData('productId'))) {
                        $ids = $this->Product->getProductIdAndAttributeId($this->getRequest()->getData('productId'));
                        $productId = $ids['productId'];
                    }
                    // param objectId is passed via ajaxCall
                    if (!empty($this->getRequest()->getData('objectId'))) {
                        $ids = $this->Product->getProductIdAndAttributeId($this->getRequest()->getData('objectId'));
                        $productId = $ids['productId'];
                    }
                    // param productId is passed as first argument of url
                    if (!empty($this->getRequest()->getParam('pass')[0])) {
                        $productId = $this->getRequest()->getParam('pass')[0];
                    }
                    if (!isset($productId)) {
                        return false;
                    }
                    $product = $this->Product->find('all', [
                        'conditions' => [
                            'Products.id_product' => $productId
                        ]
                    ])->first();
                    if (!empty($product) && $product->id_manufacturer == $this->AppAuth->getManufacturerId()) {
                        return true;
                    }
                }
                $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                return false;
                break;
        }
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
    }

    public function ajaxGetProductsForDropdown($selectedProductId, $manufacturerId = 0)
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $products = $this->Product->getForDropdown($this->AppAuth, $manufacturerId);
        $productsForDropdown = [];
        foreach ($products as $key => $ps) {
            $productsForDropdown[] = '<optgroup label="' . $key . '">';
            foreach ($ps as $pId => $p) {
                $selected = '';
                if ($selectedProductId == $pId) {
                    $selected = ' selected';
                }
                $productsForDropdown[] = '<option' . $selected . ' value="' . $pId . '">' . $p . '</option>';
            }
            $productsForDropdown[] = '</optgroup>';
        }

        die(json_encode([
            'status' => 1,
            'products' => join('', $productsForDropdown)
        ]));
    }

    /**
     * deletes both db entries and physical files (thumbs)
     *
     * @param int $productId
     */
    public function deleteImage($productId)
    {
        $productId = (int) $productId;

        if ($productId == 0 || $productId == '') {
            $message = 'Product Id nicht korrekt: ' . $productId;
            $this->log($message);
            die(json_encode([
                'status' => 0,
                'msg' => $message
            ]));
        }

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Images',
                'ProductLangs',
                'Manufacturers'
            ]
        ])->first();

        // delete db entries
        $this->Product->Images->deleteAll([
            'Images.id_image' => $product->image->id_image
        ]);

        // delete physical files
        $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($product->image->id_image);
        $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);
        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $product->image->id_image . $options['suffix'] . '.jpg';
            unlink($thumbsFileName);
        }

        $messageString = 'Bild (ID: ' . $product->image->id_image . ') wurde erfolgreich gelöscht. Produkt: <b>' . $product->product_lang->name . '</b>, Hersteller: <b>' . $product->manufacturer->name . '</b>';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_image_deleted', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

    public function saveUploadedImageProduct()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = $this->getRequest()->getData('objectId');
        $filename = $this->getRequest()->getData('filename');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Images',
                'ProductLangs',
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

        $messageString = 'Ein neues Bild zum Produkt <b>' . $product->product_lang->name . '</b> vom Hersteller <b>' . $product->manufacturer->name . '</b> wurde hochgeladen.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_image_added', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'success'
        ]));
    }

    public function deleteProductAttribute($productId, $productAttributeId)
    {

        // get new data
        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs',
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

        $messageString = 'Die Variante <b>' . $attributeName . '</b> des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller </b>' . $oldProduct->manufacturer->name . '</b> wurde erfolgreich gelöscht.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_attribute_deleted', $this->AppAuth->getUserId(), $oldProduct->id_product, 'products', $messageString);

        $this->redirect($this->referer());
    }

    public function addProductAttribute($productId, $productAttributeId)
    {
        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs',
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
                $attribute = $attribute->product_attribute_combination->attribute->name;
            }
        }
        $this->getRequest()->getSession()->write('highlightedRowId', $productId . '-' . $productAttributeIdForHighlighting);

        $messageString = 'Die Variante "' . $attribute . '" für das Produkt <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde erfolgreich erstellt.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_attribute_added', $this->AppAuth->getUserId(), $oldProduct->id_product, 'products', $messageString);

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

        $messageString = 'Ein neues Produkt für "' . $manufacturer->name . '" wurde erfolgreich erstellt.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_added', $this->AppAuth->getUserId(), $newProduct->id_product, 'products', $messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $newProduct->id_product);
        $this->redirect($this->referer());
    }

    public function editTax()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = (int) $this->getRequest()->getData('productId');
        $taxId = (int) $this->getRequest()->getData('taxId');

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Taxes',
                'ProductLangs',
                'ProductShops',
                'ProductAttributes',
                'ProductAttributes.ProductAttributeShops',
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
            $this->Product->ProductShops->save(
                $this->Product->ProductShops->patchEntity($oldProduct->product_shop, $product2update)
            );

            if (! empty($oldProduct->product_attributes)) {
                // update net price of all attributes
                foreach ($oldProduct->product_attributes as $attribute) {
                    // netPrice needs to be calculated new - product tax has been saved above...
                    $newNetPrice = $this->Product->getNetPriceAfterTaxUpdate($productId, $attribute->product_attribute_shop->price, $oldProduct->tax->rate);
                    $this->Product->ProductAttributes->ProductAttributeShops->updateAll([
                        'price' => $newNetPrice
                    ], [
                        'id_product_attribute' => $attribute->id_product_attribute
                    ]);
                }
            } else {
                // update price of product without attributes
                $newNetPrice = $this->Product->getNetPriceAfterTaxUpdate($productId, $oldProduct->product_shop->price, $oldProduct->tax->rate);
                $product2update = [
                    'price' => $newNetPrice
                ];
                $this->Product->ProductShops->save(
                    $this->Product->ProductShops->patchEntity($oldProduct->product_shop, $product2update)
                );
            }

            $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
            $tax = $this->Tax->find('all', [
                'conditions' => [
                    'Taxes.id_tax' => $taxId
                ]
            ])->first();

            if (! empty($tax)) {
                $taxRate = Configure::read('app.htmlHelper')->formatTaxRate($tax->rate);
            } else {
                $taxRate = 0; // 0 % does not have record in tax
            }

            if (! empty($oldProduct->tax)) {
                $oldTaxRate = Configure::read('app.htmlHelper')->formatTaxRate($oldProduct->rate);
            } else {
                $oldTaxRate = 0; // 0 % does not have record in tax
            }

            $messageString = 'Der Steuersatz des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde erfolgreich von  ' . $oldTaxRate . '% auf ' . $taxRate . '% geändert.';
            $this->ActionLog->customSave('product_tax_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);
        } else {
            $messageString = 'Es wurden keine Änderungen gespeichert.';
        }

        $this->Flash->success($messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'Speichern erfolgreich.'
        ]));
    }

    public function editCategories()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = (int) $this->getRequest()->getData('productId');
        $selectedCategories = [];
        if (!empty($this->getRequest()->getData('selectedCategories'))) {
            $selectedCategories = $this->getRequest()->getData('selectedCategories');
        }

        $selectedCategories[] = Configure::read('app.categoryAllProducts'); // always add 'alle-produkte'
        $selectedCategories = array_unique($selectedCategories);

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs',
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
            // only add if entry of passed id exists in category lang table
            $oldCategory = $this->Category->find('all', [
                'conditions' => [
                    'Categories.id_category' => $selectedCategory
                ]
            ])->first();
            if (! empty($oldCategory)) {
                // do not track "alle-produkte"
                if ($selectedCategory != Configure::read('app.categoryAllProducts')) {
                    $selectedCategoryNames[] = $oldCategory->name;
                }
                $sql = 'INSERT INTO ' . $this->CategoryProduct->getTable() . ' (`id_product`, `id_category`) VALUES(' . $productId . ', ' . $selectedCategory . ');';
                $this->CategoryProduct->getConnection()->query($sql);
            }
        }

        $messageString = 'Die Kategorien des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurden erfolgreich geändert: ' . join(', ', $selectedCategoryNames);
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_categories_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'Speichern erfolgreich.'
        ]));
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
                'ProductLangs',
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
                $oldProduct->product_lang->name = $oldProduct->product_lang->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                $oldProduct->stock_available->quantity = $attribute->stock_available->quantity;
            }
        }

        try {
            $this->Product->changeQuantity(
                [
                    [$originalProductId => $this->getRequest()->getData('quantity')]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $quantity = $this->Product->getQuantityAsInteger($this->getRequest()->getData('quantity'));
        $this->Flash->success('Die Anzahl des Produktes <b>' . $oldProduct->product_lang->name . '</b> wurde erfolgreich geändert.');
        $this->ActionLog->customSave('product_quantity_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Anzahl des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde von ' . $oldProduct->stock_available->quantity . ' auf ' . $quantity . ' geändert.');
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function editPrice()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData())));

        $originalProductId = $this->getRequest()->getData('productId');

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs',
                'ProductShops',
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.ProductAttributeShops',
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
                $oldProduct->product_lang->name = $oldProduct->product_lang->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                $oldProduct->product_shop->price = $attribute->product_attribute_shop->price;
            }
        }

        try {
            $this->Product->changePrice(
                [
                    [$originalProductId => $this->getRequest()->getData('price')]
                ]
            );
            $this->Unit = TableRegistry::getTableLocator()->get('Units'); 
            $this->Unit->saveUnits(
                $ids['productId'],
                $ids['attributeId'],
                $this->getRequest()->getData('pricePerUnitEnabled'),
                $this->Product->getStringAsFloat($this->getRequest()->getData('priceInclPerUnit')),
                $this->getRequest()->getData('priceUnitName'),
                $this->getRequest()->getData('priceUnitAmount'),
                $this->Product->getStringAsFloat($this->getRequest()->getData('priceQuantityInUnits'))
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $price = $this->Product->getStringAsFloat($this->getRequest()->getData('price'));
        $this->Flash->success('Der Preis des Produktes <b>' . $oldProduct->product_lang->name . '</b> wurde erfolgreich geändert.');
        $this->ActionLog->customSave('product_price_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Der Preis des Produktes <b>' . $oldProduct->product_lang->name. '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde von ' . Configure::read('app.htmlHelper')->formatAsEuro($this->Product->getGrossPrice($productId, $oldProduct->product_shop->price)) . ' auf ' . Configure::read('app.htmlHelper')->formatAsEuro($price) . ' geändert.');
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set('data', [
            'status' => 1,
            'msg' => 'ok'
        ]);

        $this->set('_serialize', 'data');
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
                'ProductLangs',
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
        $productName = $oldProduct->product_lang->name;

        if ($ids['attributeId'] > 0) {
            $attributeName = '';
            foreach ($oldProduct->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $ids['attributeId']) {
                    $attributeName = $attribute->product_attribute_combination->attribute->name;
                    $depositEntity = $attribute->deposit_product_attribute;
                    break;
                }
            }
            $productName .= ' (Variante: '.$attributeName.')';
        }

        $logString = 'Der Pfand des Produktes <b>' . $productName . '</b> wurde von ';
        if (!empty($depositEntity->deposit)) {
            $logString .= Configure::read('app.htmlHelper')->formatAsEuro($depositEntity->deposit);
        } else {
            $logString .= Configure::read('app.htmlHelper')->formatAsEuro(0);
        }

        $deposit = $this->Product->getStringAsFloat($this->getRequest()->getData('deposit'));
        $logString .= ' auf ' . Configure::read('app.htmlHelper')->formatAsEuro($deposit) . ' geändert.';

        $this->ActionLog->customSave('product_deposit_changed', $this->AppAuth->getUserId(), $productId, 'products', $logString);

        $this->Flash->success('Der Pfand des Produktes <b>' . $productName . '</b> wurde erfolgreich geändert.');
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set('data', [
            'status' => 1,
            'msg' => 'ok'
        ]);

        $this->set('_serialize', 'data');
    }

    public function editName()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = $this->getRequest()->getData('productId');

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs',
                'Manufacturers'
            ]
        ])->first();

        try {
            $this->Product->ProductLangs->changeName(
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

        $this->Flash->success('Das Produkt wurde erfolgreich geändert.');

        if ($this->getRequest()->getData('name') != $oldProduct->product_lang->name) {
            $this->ActionLog->customSave('product_name_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Das Produkt <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde umbenannt in <i>"' . $this->getRequest()->getData('name') . '"</i>.');
        }
        if ($this->getRequest()->getData('unity') != $oldProduct->product_lang->unity) {
            $this->ActionLog->customSave('product_unity_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Einheit des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde geändert in <i>"' . $this->getRequest()->getData('unity') . '"</i>.');
        }
        if ($this->getRequest()->getData('description') != $oldProduct->product_lang->description) {
            $this->ActionLog->customSave('product_description_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Beschreibung des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde geändert: <div class="changed">' . $this->getRequest()->getData('description') . ' </div>');
        }
        if ($this->getRequest()->getData('descriptionShort') != $oldProduct->product_lang->description_short) {
            $this->ActionLog->customSave('product_description_short_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Kurzbeschreibung des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde geändert. <div class="changed">' . $this->getRequest()->getData('descriptionShort') . '</div>');
        }

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function index()
    {
        $productId = '';
        if (! empty($this->getRequest()->getQuery('productId'))) {
            $productId = $this->getRequest()->getQuery('productId');
        }
        $this->set('productId', $productId);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        }

        // always filter by manufacturer id so that no other products than the own are shown
        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }
        $this->set('manufacturerId', $manufacturerId);

        $active = 'all'; // default value
        if (in_array('active', array_keys($this->getRequest()->getQueryParams()))) {
            $active = $this->getRequest()->getQuery('active');
        }
        $this->set('active', $active);

        $categoryId = ''; // default value
        if (!empty($this->getRequest()->getQuery('categoryId'))) {
            $categoryId = $this->getRequest()->getQuery('categoryId');
        }
        $this->set('categoryId', $categoryId);

        $isQuantityMinFilterSet = 0; // default value
        if (!empty($this->getRequest()->getQuery('isQuantityMinFilterSet'))) {
            $isQuantityMinFilterSet = $this->getRequest()->getQuery('isQuantityMinFilterSet');
        }
        $this->set('isQuantityMinFilterSet', $isQuantityMinFilterSet);

        $isPriceZero = 0; // default value
        if (!empty($this->getRequest()->getQuery('isPriceZero'))) {
            $isPriceZero = $this->getRequest()->getQuery('isPriceZero');
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
        $manufacturersForDropdown = ['all' => 'Alle Hersteller'];
        $manufacturersForDropdown = array_merge($manufacturersForDropdown, $this->Product->Manufacturers->getForDropdown());
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());

        if ($manufacturerId > 0) {
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $manufacturerId
                ],
                'fields' => [
                    'is_holiday_active' => '!'.$this->Product->Manufacturers->getManufacturerHolidayConditions()
                ]
            ])
            ->select($this->Product->Manufacturers)
            ->first();
            $this->set('manufacturer', $manufacturer);
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            $this->set('variableMemberFee', $variableMemberFee);
        }

        $this->set('title_for_layout', 'Produkte');

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED') && $this->AppAuth->isManufacturer()) {
            $this->SyncManufacturer = TableRegistry::getTableLocator()->get('Network.SyncManufacturers');
            $this->SyncDomain = TableRegistry::getTableLocator()->get('Network.SyncDomains');
            $this->helpers[] = 'Network.Network';
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
                'ProductLangs',
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

        $message = 'Die Standard-Variante des Produktes <b>' . $product->product_lang->name . '</b> vom Hersteller <b>' . $product->manufacturer->name . '</b> wurde auf <b>' . $productAttribute->product_attribute_combination->attribute->name . '</b> geändert.';
        $this->Flash->success($message);
        $this->ActionLog->customSave('product_default_attribute_changed', $this->AppAuth->getUserId(), $productId, 'products', $message);

        $this->redirect($this->referer());
    }

    public function changeNewStatus($productId, $status)
    {
        $status = (int) $status;

        if (! in_array($status, [
            APP_OFF,
            APP_ON
        ])) {
            throw new InvalidParameterException('New-Status muss 0 oder 1 sein!');
        }

        if ($status == 1) {
            $newCreated = 'NOW()';
        } else {
            $newCreated = 'DATE_ADD(NOW(), INTERVAL -8 DAY)';
        }

        $this->ProductShop = TableRegistry::getTableLocator()->get('ProductShops');
        $sql = "UPDATE ".$this->Product->getTable()." p, ".$this->ProductShop->getTable()." ps 
                SET p.created  = " . $newCreated . ",
                    ps.created = " . $newCreated . "
                WHERE p.id_product = ps.id_product
                AND p.id_product = " . $productId . ";";
        $result = $this->Product->getConnection()->query($sql);

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'ProductLangs',
                'Manufacturers'
            ]
        ])->first();

        $statusText = 'ab sofort nicht mehr als "neu" angezeigt';
        $actionLogType = 'product_set_to_old';
        if ($status) {
            $statusText = 'jetzt ' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' Tage lang als "neu" angezeigt';
            $actionLogType = 'product_set_to_new';
        }

        $message = 'Das Produkt <b>' . $product->product_lang->name . '</b> vom Hersteller <b>' . $product->manufacturer->name . '</b> wird ' . $statusText . '.';
        $this->Flash->success($message);

        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $productId, 'products', $message);

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
                'ProductLangs',
                'Manufacturers'
            ]
        ])->first();

        $statusText = 'deaktiviert';
        $actionLogType = 'product_set_inactive';
        if ($status) {
            $statusText = 'aktiviert';
            $actionLogType = 'product_set_active';
            $this->getRequest()->getSession()->write('highlightedRowId', $productId);
        }

        $this->Flash->success('Das Produkt "' . $product->product_lang->name . '" wurde erfolgreich ' . $statusText . '.');

        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $productId, 'products', 'Das Produkt <b>' . $product->product_lang->name . '</b> vom Hersteller "' . $product->manufacturer->name . '" wurde ' . $statusText . '.');

        $this->redirect($this->referer());
    }
}

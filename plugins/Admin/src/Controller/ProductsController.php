<?php

namespace Admin\Controller;

use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
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
        switch ($this->request->action) {
            case 'index':
            case 'add':
            case 'ajaxGetProductsForDropdown':
                return $this->AppAuth->user();
                break;
            default:
                if (!empty($this->request->getData('productId'))) {
                    $ids = $this->Product->getProductIdAndAttributeId($this->request->getData('productId'));
                    $productId = $ids['productId'];
                    $product = $this->Product->find('all', [
                        'conditions' => [
                            'Products.id_product' => $productId
                        ]
                    ])->first();
                    if (empty($product)) {
                        new 
                        $this->sendAjaxError(new ForbiddenExcepton(ACCESS_DENIED_MESSAGE));
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
                    if (!empty($this->request->getData('productId'))) {
                        $ids = $this->Product->getProductIdAndAttributeId($this->request->getData('productId'));
                        $productId = $ids['productId'];
                    }
                    // param objectId is passed via ajaxCall
                    if (!empty($this->request->getData('objectId'))) {
                        $ids = $this->Product->getProductIdAndAttributeId($this->request->getData('objectId'));
                        $productId = $ids['productId'];
                    }
                    // param productId is passed as first argument of url
                    if (!empty($this->request->getParam('pass')[0])) {
                        $productId = $this->request->getParam('pass')[0];
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
                $this->sendAjaxError(new ForbiddenExcepton(ACCESS_DENIED_MESSAGE));
                return false;
                break;
        }
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->Product = TableRegistry::get('Products');
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
            ]
		])->first();

        // delete db entries
        $this->Product->Image->deleteAll([
            'Images.id_image' => $product['Images']['id_image']
        ]);

        // delete physical files
        $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($product['Images']['id_image']);
        $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);
        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $product['Images']['id_image'] . $options['suffix'] . '.jpg';
            unlink($thumbsFileName);
        }

        $messageString = 'Bild (Id: ' . $product['Images']['id_image'] . ') wurde erfolgreich gelöscht. Produkt: "' . $product['ProductLangs']['name'] . '", Hersteller: "' . $product['Manufacturers']['name'] . '"';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_image_deleted', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->request->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

    public function saveUploadedImageProduct()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = $this->params['data']['objectId'];
        $filename = $this->params['data']['filename'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ]
        ])->first();

        if ($product['Images']['id_image'] == '') {
            // product does not yet have image => create the necessary record
            $this->Product->Image->save([
                'id_product' => $productId
            ]);
            $imageId = $this->Product->Image->getLastInsertID();
        } else {
            $imageId = $product['Images']['id_image'];
        }

        // not (yet) implemented for attributes, only for productIds!
        $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($imageId);
        $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);

        // recursively create path
        $dir = new Folder();
        $dir->create($thumbsPath);
        $dir->chmod($thumbsPath, 0755);

        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {
            $image = Image::make(WWW_ROOT . $filename);
            // make portrait images smaller
            if ($image->getHeight() > $image->getWidth()) {
                $thumbSize = round($thumbSize * ($image->getWidth() / $image->getHeight()), 0);
            }
            $image->widen($thumbSize);
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . $extension;
            $image->save($thumbsFileName);
        }

        $messageString = 'Ein neues Bild zum Produkt: "' . $product['ProductLangs']['name'] . '" (Hersteller: "' . $product['Manufacturers']['name'] . '") wurde hochgeladen.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_image_added', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->request->getSession()->write('highlightedRowId', $productId);

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

        $this->Product->addProductAttribute($productId, $productAttributeId);

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
        $this->request->getSession()->write('highlightedRowId', $productId . '-' . $productAttributeIdForHighlighting);

        $messageString = 'Die Variante "' . $attribute . '" für das Produkt <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde erfolgreich erstellt.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_attribute_added', $this->AppAuth->getUserId(), $oldProduct->id_product, 'products', $messageString);

        $this->request->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

    public function add($manufacturerId)
    {

        // if logged user is manufacturer, then get param manufacturer id is NOT used
        // but logged user id for security reasons
        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }

        $this->Manufacturer = TableRegistry::get('Manufacturers');
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

        $this->request->getSession()->write('highlightedRowId', $newProduct->id_product);
        $this->redirect($this->referer());
    }

    public function editTax()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = (int) $this->params['data']['productId'];
        $taxId = (int) $this->params['data']['taxId'];

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ]
        ])->first();

        if ($taxId != $oldProduct['Products']['id_tax']) {
            $product2update = [
                'id_tax' => $taxId
            ];

            // as often data is saved twice (Product, ProductShop)
            $this->Product->id = $productId;
            $this->Product->save($product2update);

            $this->Product->ProductShop->id = $productId;
            $this->Product->ProductShop->save($product2update);

            if (! empty($oldProduct['ProductAttributes'])) {
                // update net price of all attributes
                foreach ($oldProduct['ProductAttributes'] as $attribute) {
                    // netPrice needs to be calculated new - product tax has been saved above...
                    $newNetPrice = $this->Product->getNetPriceAfterTaxUpdate($productId, $attribute['ProductAttributeShops']['price'], $oldProduct['Taxes']['rate']);
                    $this->Product->ProductAttributes->ProductAttributeShop->updateAll([
                        'ProductAttributeShop.price' => $newNetPrice
                    ], [
                        'ProductAttributeShop.id_product_attribute' => $attribute['id_product_attribute']
                    ]);
                }
            } else {
                // update price of product without attributes
                $newNetPrice = $this->Product->getNetPriceAfterTaxUpdate($productId, $oldProduct['ProductShop']['price'], $oldProduct['Taxes']['rate']);
                $product2update = [
                    'price' => $newNetPrice
                ];
                $this->Product->ProductShop->id = $productId;
                $this->Product->ProductShop->save($product2update);
            }

            $this->Tax = TableRegistry::get('Taxes');
            $tax = $this->Tax->find('all', [
                'conditions' => [
                    'Taxes.id_tax' => $taxId
                ]
            ])->first();

            if (! empty($tax)) {
                $taxRate = Configure::read('app.htmlHelper')->formatTaxRate($tax['Taxes']['rate']);
            } else {
                $taxRate = 0; // 0 % does not have record in tax
            }

            if (! empty($oldProduct['Taxes'])) {
                $oldTaxRate = Configure::read('app.htmlHelper')->formatTaxRate($oldProduct['Taxes']['rate']);
            } else {
                $oldTaxRate = 0; // 0 % does not have record in tax
            }

            $messageString = 'Der Steuersatz des Produktes <b>' . $oldProduct['ProductLangs']['name'] . '</b> wurde erfolgreich von  ' . $oldTaxRate . '% auf ' . $taxRate . '% geändert.';
            $this->ActionLog->customSave('product_tax_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);
        } else {
            $messageString = 'Es wurden keine Änderungen gespeichert.';
        }

        $this->Flash->success($messageString);

        $this->request->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'Speichern erfolgreich.'
        ]));
    }

    public function editCategories()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = (int) $this->params['data']['productId'];
        $selectedCategories = [];
        if (isset($this->params['data']['selectedCategories'])) {
            $selectedCategories = $this->params['data']['selectedCategories'];
        }

        $selectedCategories[] = Configure::read('app.categoryAllProducts'); // always add 'alle-produkte'
        $selectedCategories = array_unique($selectedCategories);

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ]
        ]);

        $this->CategoryProduct = TableRegistry::get('CategoryProducts');
        $this->CategoryProduct->deleteAll([
            'id_product' => $productId
        ]);

        $this->Category = TableRegistry::get('Categories');
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
                    $selectedCategoryNames[] = $oldCategory['Categories']['name'];
                }
                $sql = 'INSERT INTO ' . $this->CategoryProduct->getTable() . ' (`id_product`, `id_category`) VALUES(' . $productId . ', ' . $selectedCategory . ');';
                $this->CategoryProduct->getConnection()->query($sql);
            }
        }

        $messageString = 'Die Kategorien des Produktes <b>' . $oldProduct['ProductLangs']['name'] . '</b> wurden erfolgreich geändert: ' . join(', ', $selectedCategoryNames);
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_categories_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->request->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'Speichern erfolgreich.'
        ]));
    }

    public function editQuantity()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $originalProductId = $this->request->getData('productId');

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
                    [$originalProductId => $this->request->getData('quantity')]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $quantity = $this->Product->getQuantityAsInteger($this->request->getData('quantity'));
        $this->Flash->success('Die Anzahl des Produktes <b>' . $oldProduct->product_lang->name . '</b> wurde erfolgreich geändert.');
        $this->ActionLog->customSave('product_quantity_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Anzahl des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde von ' . $oldProduct->stock_available->quantity . ' auf ' . $quantity . ' geändert.');
        $this->request->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function editPrice()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $originalProductId = $this->request->getData('productId');

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
                $oldProduct->product_shop->price = $attribute->product_attribute_shop->price;
            }
        }

        try {
            $this->Product->changePrice(
                [
                    [$originalProductId => $this->request->getData('price')]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $price = $this->Product->getPriceAsFloat($this->request->getData('price'));
        $this->Flash->success('Der Preis des Produktes <b>' . $oldProduct->product_lang->name . '</b> wurde erfolgreich geändert.');
        $this->ActionLog->customSave('product_price_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Der Preis des Produktes <b>' . $oldProduct->product_lang->name. '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde von ' . Configure::read('app.htmlHelper')->formatAsEuro($this->Product->getGrossPrice($productId, $oldProduct->product_shop->price)) . ' auf ' . Configure::read('app.htmlHelper')->formatAsEuro($price) . ' geändert.');
        $this->request->getSession()->write('highlightedRowId', $productId);

        $this->set('data', [
            'status' => 1,
            'msg' => 'ok'
        ]);

        $this->set('_serialize', 'data');
    }

    public function editDeposit()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $originalProductId = $this->request->getData('productId');

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
                    [$originalProductId => $this->request->getData('deposit')]
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

        $deposit = $this->Product->getPriceAsFloat($this->request->getData('deposit'));
        $logString .= ' auf ' . Configure::read('app.htmlHelper')->formatAsEuro($deposit) . ' geändert.';

        $this->ActionLog->customSave('product_deposit_changed', $this->AppAuth->getUserId(), $productId, 'products', $logSting);

        $this->Flash->success('Der Pfand des Produktes "' . $productName . '" wurde erfolgreich geändert.');
        $this->request->getSession()->write('highlightedRowId', $productId);

        $this->set('data', [
            'status' => 1,
            'msg' => 'ok'
        ]);

        $this->set('_serialize', 'data');
    }

    public function editName()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = $this->request->getData('productId');

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
                        'name' => $this->request->getData('name'),
                        'description' => $this->request->getData('description'),
                        'description_short' => $this->request->getData('descriptionShort'),
                        'unity' => $this->request->getData('unity'),
                        'is_declaration_ok' => $this->request->getData('isDeclarationOk')
                    ]]
                ]
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $this->Flash->success('Das Produkt wurde erfolgreich geändert.');
        
        if ($this->request->getData('name') != $oldProduct->product_lang->name) {
            $this->ActionLog->customSave('product_name_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Das Produkt <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde umbenannt in <i>"' . $this->request->getData('name') . '"</i>.');
        }
        if ($this->request->getData('unity') != $oldProduct->product_lang->unity) {
            $this->ActionLog->customSave('product_unity_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Einheit des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde geändert in <i>"' . $this->request->getData('unity') . '"</i>.');
        }
        if ($this->request->getData('description') != $oldProduct->product_lang->description) {
            $this->ActionLog->customSave('product_description_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Beschreibung des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde geändert: <div class="changed">' . $this->request->getData('description') . ' </div>');
        }
        if ($this->request->getData('descriptionShort') != $oldProduct->product_lang->description_short) {
            $this->ActionLog->customSave('product_description_short_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Kurzbeschreibung des Produktes <b>' . $oldProduct->product_lang->name . '</b> vom Hersteller <b>' . $oldProduct->manufacturer->name . '</b> wurde geändert. <div class="changed">' . $this->request->getData('descriptionShort') . '</div>');
        }

        $this->request->getSession()->write('highlightedRowId', $productId);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function index()
    {
        $productId = '';
        if (! empty($this->request->getQuery('productId'))) {
            $productId = $this->request->getQuery('productId');
        }
        $this->set('productId', $productId);

        $manufacturerId = '';
        if (! empty($this->request->getQuery('manufacturerId'))) {
            $manufacturerId = $this->request->getQuery('manufacturerId');
        }

        // always filter by manufacturer id so that no other products than the own are shown
        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }
        $this->set('manufacturerId', $manufacturerId);

        $active = 'all'; // default value
        if (in_array('active', array_keys($this->request->getQueryParams()))) {
            $active = $this->request->getQuery('active');
        }
        $this->set('active', $active);

        $categoryId = ''; // default value
        if (!empty($this->request->getQuery('categoryId'))) {
            $categoryId = $this->request->getQuery('categoryId');
        }
        $this->set('categoryId', $categoryId);

        $isQuantityZero = 0; // default value
        if (!empty($this->request->getQuery('isQuantityZero'))) {
            $isQuantityZero = $this->request->getQuery('isQuantityZero');
        }
        $this->set('isQuantityZero', $isQuantityZero);

        $isPriceZero = 0; // default value
        if (!empty($this->request->getQuery('isPriceZero'))) {
            $isPriceZero = $this->request->getQuery('isPriceZero');
        }
        $this->set('isPriceZero', $isPriceZero);

        if ($manufacturerId != '') {
            $preparedProducts = $this->Product->getProductsForBackend($this->AppAuth, $productId, $manufacturerId, $active, $categoryId, $isQuantityZero, $isPriceZero);
        } else {
            $preparedProducts = [];
        }
        $this->set('products', $preparedProducts);

        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $this->Attribute = TableRegistry::get('Attributes');
        $this->set('attributesForDropdown', $this->Attribute->getForDropdown());
        $this->Category = TableRegistry::get('Categories');
        $this->set('categoriesForSelect', $this->Category->getForSelect());
        $manufacturersForDropdown = ['all' => 'Alle Hersteller'];
        $manufacturersForDropdown = array_merge($manufacturersForDropdown, $this->Product->Manufacturers->getForDropdown());
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->Tax = TableRegistry::get('Taxes');
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
            $this->SyncManufacturer = TableRegistry::get('Network.SyncManufacturers');
            $this->SyncDomain = TableRegistry::get('Network.SyncDomains');
            $this->helpers[] = 'Network.Network';
            $isAllowedToUseAsMasterFoodcoop = $this->SyncManufacturer->isAllowedToUseAsMasterFoodcoop($this->AppAuth);
            $syncDomains = $this->SyncDomain->getActiveManufacturerSyncDomains($this->AppAuth->manufacturer['Manufacturers']['enabled_sync_domains']);
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
            ]
        ])->first();

        $productAttribute = $this->Product->ProductAttributes->find('all', [
            'conditions' => [
                'ProductAttributes.id_product_attribute' => $productAttributeId
            ]
        ])->first();

        $message = 'Die Standard-Variante des Produktes "' . $product['ProductLangs']['name'] . '" vom Hersteller "' . $product['Manufacturers']['name'] . '" wurde auf "' . $productAttribute['ProductAttributeCombinations']['Attributes']['name'] . '" geändert.';
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
            throw new RecordNotFoundException('New-Status muss 0 oder 1 sein!');
        }

        if ($status == 1) {
            $newCreated = 'NOW()';
        } else {
            $newCreated = 'DATE_ADD(NOW(), INTERVAL -8 DAY)';
        }

        $this->ProductShop = TableRegistry::get('ProductShops');
        $sql = "UPDATE ".$this->Product->getTable()." p, ".$this->ProductShop->getTable()." ps 
                SET p.created  = " . $newCreated . ",
                    ps.created = " . $newCreated . "
                WHERE p.id_product = ps.id_product
                AND p.id_product = " . $productId . ";";
        $result = $this->Product->getConnection()->query($sql);

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ]
        ])->first();

        $statusText = 'ab sofort nicht mehr als "neu" angezeigt';
        $actionLogType = 'product_set_to_old';
        if ($status) {
            $statusText = 'jetzt ' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' Tage lang als "neu" angezeigt';
            $actionLogType = 'product_set_to_new';
        }

        $message = 'Das Produkt "' . $product['ProductLangs']['name'] . '" vom Hersteller "' . $product['Manufacturers']['name'] . '" wird ' . $statusText . '.';
        $this->Flash->success($message);

        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $productId, 'products', $message);

        $this->request->getSession()->write('highlightedRowId', $productId);

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
        }

        $this->Flash->success('Das Produkt "' . $product->product_lang->name . '" wurde erfolgreich ' . $statusText . '.');

        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $productId, 'products', 'Das Produkt <b>' . $product->product_lang->name . '</b> vom Hersteller "' . $product->manufacturer->name . '" wurde ' . $statusText . '.');

        $this->request->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }
}

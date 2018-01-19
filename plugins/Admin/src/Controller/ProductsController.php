<?php

use Admin\Controller\AdminAppController;
use Cake\Controller\Exception\MissingActionException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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

use Intervention\Image\ImageManagerStatic as Image;

class ProductsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->action) {
            case 'index':
            case 'add':
            case 'ajaxGetProductsForDropdown':
                return $this->AppAuth->user();
                break;
            default:
                if (!empty($this->params['data']['productId'])) {
                    $ids = $this->Product->getProductIdAndAttributeId($this->params['data']['productId']);
                    $productId = $ids['productId'];
                    $product = $this->Product->find('first', array(
                        'conditions' => array(
                            'Products.id_product' => $productId
                        )
                    ));
                    if (empty($product)) {
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
                    if (!empty($this->params['data']['productId'])) {
                        $ids = $this->Product->getProductIdAndAttributeId($this->params['data']['productId']);
                        $productId = $ids['productId'];
                    }
                    // param objectId is passed via ajaxCall
                    if (!empty($this->params['data']['objectId'])) {
                        $ids = $this->Product->getProductIdAndAttributeId($this->params['data']['objectId']);
                        $productId = $ids['productId'];
                    }
                    // param productId is passed as first argument of url
                    if (!empty($this->params['pass'][0])) {
                        $productId = $this->params['pass'][0];
                    }
                    if (!isset($productId)) {
                        return false;
                    }
                    $product = $this->Product->find('first', array(
                        'conditions' => array(
                            'Products.id_product' => $productId
                        )
                    ));
                    if (!empty($product) && $product['Products']['id_manufacturer'] == $this->AppAuth->getManufacturerId()) {
                        return true;
                    }
                }
                return false;
                break;
        }
    }

    public function beforeFilter(Event $event)
    {
        $this->ActionLog = TableRegistry::get('ActionLogs');
        parent::beforeFilter($event);
    }

    public function ajaxGetProductsForDropdown($selectedProductId, $manufacturerId = 0)
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $products = $this->Product->getForDropdown($this->AppAuth, $manufacturerId);
        $productsForDropdown = array();
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
        die(json_encode(array(
            'status' => 1,
            'products' => join('', $productsForDropdown)
        )));
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
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        $product = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        // delete db entries
        $this->Product->Image->deleteAll(array(
            'Images.id_image' => $product['Images']['id_image']
        ), false);

        // delete physical files
        $imageIdAsPath = Configure::read('AppConfig.htmlHelper')->getProductImageIdAsPath($product['Images']['id_image']);
        $thumbsPath = Configure::read('AppConfig.htmlHelper')->getProductThumbsPath($imageIdAsPath);
        foreach (Configure::read('AppConfig.productImageSizes') as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $product['Images']['id_image'] . $options['suffix'] . '.jpg';
            unlink($thumbsFileName);
        }

        $messageString = 'Bild (Id: ' . $product['Images']['id_image'] . ') wurde erfolgreich gelöscht. Produkt: "' . $product['ProductLangs']['name'] . '", Hersteller: "' . $product['Manufacturers']['name'] . '"';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_image_deleted', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->request->session()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

    public function saveUploadedImageProduct()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = $this->params['data']['objectId'];
        $filename = $this->params['data']['filename'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $product = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        if ($product['Images']['id_image'] == '') {
            // product does not yet have image => create the necessary record
            $this->Product->Image->save(array(
                'id_product' => $productId
            ));
            $imageId = $this->Product->Image->getLastInsertID();
        } else {
            $imageId = $product['Images']['id_image'];
        }

        // not (yet) implemented for attributes, only for productIds!
        $imageIdAsPath = Configure::read('AppConfig.htmlHelper')->getProductImageIdAsPath($imageId);
        $thumbsPath = Configure::read('AppConfig.htmlHelper')->getProductThumbsPath($imageIdAsPath);

        // recursively create path
        App::uses('Folder', 'Utility');
        $dir = new Folder();
        $dir->create($thumbsPath);
        $dir->chmod($thumbsPath, 0755);

        foreach (Configure::read('AppConfig.productImageSizes') as $thumbSize => $options) {
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

        $this->request->session()->write('highlightedRowId', $productId);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'success'
        )));
    }

    public function deleteProductAttribute($productId, $productAttributeId)
    {

        // get new data
        $this->Product->recursive = 4;
        $oldProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        foreach ($oldProduct['ProductAttributes'] as $productAttribute) {
            if ($productAttribute['ProductAttributeCombinations']['id_product_attribute'] == $productAttributeId) {
                $attribute = $productAttribute['ProductAttributeCombinations']['Attributes']['name'];
            }
        }

        $this->Product->deleteProductAttribute($productId, $productAttributeId, $oldProduct);

        $messageString = 'Die Variante "' . $attribute . '" des Produktes "' . $oldProduct['ProductLangs']['name'] . '" vom Hersteller "' . $oldProduct['Manufacturers']['name'] . '" wurde erfolgreich gelöscht.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_attribute_deleted', $this->AppAuth->getUserId(), $oldProduct['Products']['id_product'], 'products', $messageString);

        $this->redirect($this->referer());
    }

    public function addProductAttribute($productId, $productAttributeId)
    {
        $oldProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        $this->Product->addProductAttribute($productId, $productAttributeId);

        // get new data
        $this->Product->recursive = 3; // to get product attribute combination => Attribute
        $newProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));
        foreach ($newProduct['ProductAttributes'] as $productAttribute) {
            if ($productAttribute['ProductAttributeCombinations']['id_attribute'] == $productAttributeId) {
                $productAttributeIdForHighlighting = $productAttribute['ProductAttributeCombinations']['id_product_attribute'];
                $attribute = $productAttribute['ProductAttributeCombinations']['Attributes']['name'];
            }
        }
        $this->request->session()->write('highlightedRowId', $productId . '-' . $productAttributeIdForHighlighting);

        $messageString = 'Die Variante "' . $attribute . '" für das Produkt "' . $oldProduct['ProductLangs']['name'] . '" vom Hersteller "' . $oldProduct['Manufacturers']['name'] . '" wurde erfolgreich erstellt.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_attribute_added', $this->AppAuth->getUserId(), $oldProduct['Products']['id_product'], 'products', $messageString);

        $this->request->session()->write('highlightedRowId', $productId);

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
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));

        if (empty($manufacturer)) {
            throw new MissingActionException('manufacturer not existing');
        }

        $newProduct = $this->Product->add($manufacturer);

        $messageString = 'Ein neues Produkt für "' . $manufacturer['Manufacturers']['name'] . '" wurde erfolgreich erstellt.';
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_added', $this->AppAuth->getUserId(), $newProduct['Products']['id_product'], 'products', $messageString);

        $this->request->session()->write('highlightedRowId', $newProduct['Products']['id_product']);
        $this->redirect($this->referer());
    }

    public function editTax()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = (int) $this->params['data']['productId'];
        $taxId = (int) $this->params['data']['taxId'];

        $this->Product->recursive = 2; // to get ProductAttributeShop
        $oldProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        if ($taxId != $oldProduct['Products']['id_tax']) {
            $product2update = array(
                'id_tax' => $taxId
            );

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
                    $this->Product->ProductAttributes->ProductAttributeShop->updateAll(array(
                        'ProductAttributeShop.price' => $newNetPrice
                    ), array(
                        'ProductAttributeShop.id_product_attribute' => $attribute['id_product_attribute']
                    ));
                }
            } else {
                // update price of product without attributes
                $newNetPrice = $this->Product->getNetPriceAfterTaxUpdate($productId, $oldProduct['ProductShop']['price'], $oldProduct['Taxes']['rate']);
                $product2update = array(
                    'price' => $newNetPrice
                );
                $this->Product->ProductShop->id = $productId;
                $this->Product->ProductShop->save($product2update);
            }

            $this->Tax = TableRegistry::get('Taxs');
            $tax = $this->Tax->find('first', array(
                'conditions' => array(
                    'Taxes.id_tax' => $taxId
                )
            ));

            if (! empty($tax)) {
                $taxRate = Configure::read('AppConfig.htmlHelper')->formatTaxRate($tax['Taxes']['rate']);
            } else {
                $taxRate = 0; // 0 % does not have record in tax
            }

            if (! empty($oldProduct['Taxes'])) {
                $oldTaxRate = Configure::read('AppConfig.htmlHelper')->formatTaxRate($oldProduct['Taxes']['rate']);
            } else {
                $oldTaxRate = 0; // 0 % does not have record in tax
            }

            $messageString = 'Der Steuersatz des Produktes <b>' . $oldProduct['ProductLangs']['name'] . '</b> wurde erfolgreich von  ' . $oldTaxRate . '% auf ' . $taxRate . '% geändert.';
            $this->ActionLog->customSave('product_tax_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);
        } else {
            $messageString = 'Es wurden keine Änderungen gespeichert.';
        }

        $this->Flash->success($messageString);

        $this->request->session()->write('highlightedRowId', $productId);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'Speichern erfolgreich.'
        )));
    }

    public function editCategories()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = (int) $this->params['data']['productId'];
        $selectedCategories = array();
        if (isset($this->params['data']['selectedCategories'])) {
            $selectedCategories = $this->params['data']['selectedCategories'];
        }

        $selectedCategories[] = Configure::read('AppConfig.categoryAllProducts'); // always add 'alle-produkte'
        $selectedCategories = array_unique($selectedCategories);

        $oldProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        $this->CategoryProduct = TableRegistry::get('CategoryProducts');
        $this->CategoryProduct->deleteAll(array(
            'id_product' => $productId
        ));

        $this->Category = TableRegistry::get('Categories');
        $selectedCategoryNames = array();
        foreach ($selectedCategories as $selectedCategory) {
            // only add if entry of passed id exists in category lang table
            $oldCategory = $this->Category->find('first', array(
                'conditions' => array(
                    'Categories.id_category' => $selectedCategory
                )
            ));
            if (! empty($oldCategory)) {
                // do not track "alle-produkte"
                if ($selectedCategory != Configure::read('AppConfig.categoryAllProducts')) {
                    $selectedCategoryNames[] = $oldCategory['Categories']['name'];
                }
                $sql = 'INSERT INTO ' . $this->CategoryProduct->tablePrefix . $this->CategoryProduct->useTable . ' (`id_product`, `id_category`) VALUES(' . $productId . ', ' . $selectedCategory . ');';
                $this->CategoryProduct->query($sql);
            }
        }

        $messageString = 'Die Kategorien des Produktes <b>' . $oldProduct['ProductLangs']['name'] . '</b> wurden erfolgreich geändert: ' . join(', ', $selectedCategoryNames);
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_categories_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->request->session()->write('highlightedRowId', $productId);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'Speichern erfolgreich.'
        )));
    }

    public function editQuantity()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $originalProductId = $this->params['data']['productId'];

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $this->Product->recursive = 3; // for attribute lang
        $oldProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        if ($ids['attributeId'] > 0) {
            // override values for messages
            foreach ($oldProduct['ProductAttributes'] as $attribute) {
                if ($attribute['id_product_attribute'] != $ids['attributeId']) {
                    continue;
                }
                $oldProduct['ProductLangs'] = array(
                    'name' => $oldProduct['ProductLangs']['name'] . ' : ' . $attribute['ProductAttributeCombinations']['Attributes']['name']
                );
                $oldProduct['StockAvailables'] = array(
                    'quantity' => $attribute['StockAvailables']['quantity']
                );
            }
        }

        try {
            $this->Product->changeQuantity(
                array(
                    array($originalProductId => $this->params['data']['quantity'])
                )
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $quantity = $this->Product->getQuantityAsInteger($this->params['data']['quantity']);
        $this->Flash->success('Die Anzahl des Produktes <b>' . $oldProduct['ProductLangs']['name'] . '</b> wurde erfolgreich geändert.');
        $this->ActionLog->customSave('product_quantity_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Anzahl des Produktes <b>' . $oldProduct['ProductLangs']['name'] . '</b> vom Hersteller <b>' . $oldProduct['Manufacturers']['name'] . '</b> wurde von ' . $oldProduct['StockAvailables']['quantity'] . ' auf ' . $quantity . ' geändert.');
        $this->request->session()->write('highlightedRowId', $productId);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    public function editPrice()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $originalProductId = $this->params['data']['productId'];

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $this->Product->recursive = 3; // for attribute lang
        $oldProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        if ($ids['attributeId'] > 0) {
            // override values for messages
            foreach ($oldProduct['ProductAttributes'] as $attribute) {
                if ($attribute['id_product_attribute'] != $ids['attributeId']) {
                    continue;
                }
                $oldProduct['ProductLangs'] = array(
                    'name' => $oldProduct['ProductLangs']['name'] . ' : ' . $attribute['ProductAttributeCombinations']['Attributes']['name']
                );
                $oldProduct['ProductShop'] = array(
                    'price' => $attribute['ProductAttributeShops']['price']
                );
            }
        }

        try {
            $this->Product->changePrice(
                array(
                    array($originalProductId => $this->params['data']['price'])
                )
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $price = $this->Product->getPriceAsFloat($this->params['data']['price']);
        $this->Flash->success('Der Preis des Produktes <b>' . $oldProduct['ProductLangs']['name'] . '</b> wurde erfolgreich geändert.');
        $this->ActionLog->customSave('product_price_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Der Preis des Produktes <b>' . $oldProduct['ProductLangs']['name'] . '</b> vom Hersteller <b>' . $oldProduct['Manufacturers']['name'] . '</b> wurde von ' . Configure::read('AppConfig.htmlHelper')->formatAsEuro($this->Product->getGrossPrice($productId, $oldProduct['ProductShop']['price'])) . ' auf ' . Configure::read('AppConfig.htmlHelper')->formatAsEuro($price) . ' geändert.');
        $this->request->session()->write('highlightedRowId', $productId);

        $this->set('data', array(
            'status' => 1,
            'msg' => 'ok'
        ));

        $this->set('_serialize', 'data');
    }

    public function editDeposit()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $originalProductId = $this->params['data']['productId'];

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $this->Product->recursive = 3; // for attribute lang
        $oldProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        try {
            $this->Product->changeDeposit(
                array(
                    array($originalProductId => $this->params['data']['deposit'])
                )
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $depositEntity = $oldProduct['DepositProduct'];
        $productName = $oldProduct['ProductLangs']['name'];

        if ($ids['attributeId'] > 0) {
            $attributeName = '';
            foreach ($oldProduct['ProductAttributes'] as $productAttribute) {
                if ($productAttribute['id_product_attribute'] == $ids['attributeId']) {
                    $attributeName = $productAttribute['ProductAttributeCombinations']['Attributes']['name'];
                    $depositEntity = $productAttribute['DepositProductAttribute'];
                    break;
                }
            }
            $productName .= ' (Variante: '.$attributeName.')';
        }

        $logString = 'Der Pfand des Produktes <b>' . $productName . '</b> wurde von ';
        if (isset($depositEntity['deposit'])) {
            $logString .= Configure::read('AppConfig.htmlHelper')->formatAsEuro($depositEntity['deposit']);
        } else {
            $logString .= Configure::read('AppConfig.htmlHelper')->formatAsEuro(0);
        }

        $deposit = $this->Product->getPriceAsFloat($this->params['data']['deposit']);
        $logString .= ' auf ' . Configure::read('AppConfig.htmlHelper')->formatAsEuro($deposit) . ' geändert.';

        $this->ActionLog->customSave('product_deposit_changed', $this->AppAuth->getUserId(), $productId, 'products', $logString);

        $this->Flash->success('Der Pfand des Produktes "' . $productName . '" wurde erfolgreich geändert.');
        $this->request->session()->write('highlightedRowId', $productId);

        $this->set('data', array(
            'status' => 1,
            'msg' => 'ok'
        ));

        $this->set('_serialize', 'data');
    }

    public function editName()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $productId = $this->params['data']['productId'];

        $oldProduct = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        try {
            $this->Product->ProductLang->changeName(
                array(
                    array($productId => array(
                        'name' => $this->params['data']['name'],
                        'description' => $this->params['data']['description'],
                        'description_short' => $this->params['data']['descriptionShort'],
                        'unity' => $this->params['data']['unity'],
                        'is_declaration_ok' => $this->params['data']['isDeclarationOk']
                    ))
                )
            );
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        $this->Flash->success('Das Produkt wurde erfolgreich geändert.');

        if ($this->params['data']['name'] != $oldProduct['ProductLangs']['name']) {
            $this->ActionLog->customSave('product_name_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Das Produkt "' . $oldProduct['ProductLangs']['name'] . '" vom Hersteller "' . $oldProduct['Manufacturers']['name'] . '" wurde umbenannt in <i>"' . $this->params['data']['name'] . '"</i>.');
        }
        if ($this->params['data']['unity'] != $oldProduct['ProductLangs']['unity']) {
            $this->ActionLog->customSave('product_unity_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Einheit des Produktes "' . $oldProduct['ProductLangs']['name'] . '" vom Hersteller "' . $oldProduct['Manufacturers']['name'] . '" wurde geändert in <i>"' . $this->params['data']['unity'] . '"</i>.');
        }
        if ($this->params['data']['description'] != $oldProduct['ProductLangs']['description']) {
            $this->ActionLog->customSave('product_description_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Beschreibung des Produktes "' . $oldProduct['ProductLangs']['name'] . '" vom Hersteller "' . $oldProduct['Manufacturers']['name'] . '" wurde geändert: <br /><br /> alt: <div class="changed">' . $oldProduct['ProductLangs']['description'] . '</div>neu: <div class="changed">' . $this->params['data']['description'] . ' </div>');
        }
        if ($this->params['data']['descriptionShort'] != $oldProduct['ProductLangs']['description_short']) {
            $this->ActionLog->customSave('product_description_short_changed', $this->AppAuth->getUserId(), $productId, 'products', 'Die Kurzbeschreibung des Produktes "' . $oldProduct['ProductLangs']['name'] . '" vom Hersteller "' . $oldProduct['Manufacturers']['name'] . '" wurde geändert. <br /><br /> alt: <div class="changed">' . $oldProduct['ProductLangs']['description_short'] . '</div> neu: <div class="changed">' . $this->params['data']['descriptionShort'] . '</div>');
        }

        $this->request->session()->write('highlightedRowId', $productId);

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    public function index()
    {
        $productId = '';
        if (! empty($this->params['named']['productId'])) {
            $productId = $this->params['named']['productId'];
        }
        $this->set('productId', $productId);

        $manufacturerId = '';
        if (! empty($this->params['named']['manufacturerId'])) {
            $manufacturerId = $this->params['named']['manufacturerId'];
        }

        // always filter by manufacturer id so that no other products than the own are shown
        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }
        $this->set('manufacturerId', $manufacturerId);

        $active = 'all'; // default value
        if (isset($this->params['named']['active'])) {
            $active = $this->params['named']['active'];
        }
        $this->set('active', $active);

        $category = ''; // default value
        if (!empty($this->params['named']['category'])) {
            $category = $this->params['named']['category'];
        }
        $this->set('category', $category);

        $isQuantityZero = 0; // default value
        if (!empty($this->params['named']['isQuantityZero'])) {
            $isQuantityZero = $this->params['named']['isQuantityZero'];
        }
        $this->set('isQuantityZero', $isQuantityZero);

        $isPriceZero = 0; // default value
        if (!empty($this->params['named']['isPriceZero'])) {
            $isPriceZero = $this->params['named']['isPriceZero'];
        }
        $this->set('isPriceZero', $isPriceZero);

        if ($manufacturerId != '') {
            $pParams = $this->Product->getProductParams($this->AppAuth, $productId, $manufacturerId, $active, $category, $isQuantityZero, $isPriceZero);
            $preparedProducts = $this->Product->prepareProductsForBackend($this->Paginator, $pParams);
        } else {
            $preparedProducts = array();
        }
        $this->set('products', $preparedProducts);

        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $this->Attribute = TableRegistry::get('Attributes');
        $this->set('attributesForDropdown', $this->Attribute->getForDropdown());
        $this->Category = TableRegistry::get('Categories');
        $this->set('categoriesForSelect', $this->Category->getForSelect());
        $manufacturersForDropdown = $this->Product->Manufacturer->getForDropdown();
        array_unshift($manufacturersForDropdown, array('all' => 'Alle Hersteller'));
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->Tax = TableRegistry::get('Taxs');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());

        if ($manufacturerId > 0) {
            $manufacturer = $this->Manufacturer->find('first', array(
                'conditions' => array(
                    'Manufacturers.id_manufacturer' => $manufacturerId
                ),
                'fields' => array('Manufacturers.*', '!'.$this->Manufacturer->getManufacturerHolidayConditions().' as IsHolidayActive')
            ));
            $this->set('manufacturer', $manufacturer);
            $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer['Manufacturers']['variable_member_fee']);
            $this->set('variableMemberFee', $variableMemberFee);
        }

        $this->set('title_for_layout', 'Produkte');

        if (Configure::read('AppConfig.db_config_FCS_NETWORK_PLUGIN_ENABLED') && $this->AppAuth->isManufacturer()) {
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

        $product = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        $productAttribute = $this->Product->ProductAttributes->find('first', array(
            'conditions' => array(
                'ProductAttributes.id_product_attribute' => $productAttributeId
            ),
            'recursive' => 3
        ));

        $message = 'Die Standard-Variante des Produktes "' . $product['ProductLangs']['name'] . '" vom Hersteller "' . $product['Manufacturers']['name'] . '" wurde auf "' . $productAttribute['ProductAttributeCombinations']['Attributes']['name'] . '" geändert.';
        $this->Flash->success($message);
        $this->ActionLog->customSave('product_default_attribute_changed', $this->AppAuth->getUserId(), $productId, 'products', $message);

        $this->redirect($this->referer());
    }

    public function changeNewStatus($productId, $status)
    {
        $status = (int) $status;

        if (! in_array($status, array(
            APP_OFF,
            APP_ON
        ))) {
            throw new MissingActionException('New-Status muss 0 oder 1 sein!');
        }

        if ($status == 1) {
            $newDateAdd = 'NOW()';
        } else {
            $newDateAdd = 'DATE_ADD(NOW(), INTERVAL -8 DAY)';
        }

        $sql = "UPDATE ".$this->Product->tablePrefix."product p, ".$this->Product->tablePrefix."product_shop ps 
                SET p.date_add  = " . $newDateAdd . ",
                    ps.date_add = " . $newDateAdd . "
                WHERE p.id_product = ps.id_product
                AND p.id_product = " . $productId . ";";
        $result = $this->Product->query($sql);

        $product = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        $statusText = 'ab sofort nicht mehr als "neu" angezeigt';
        $actionLogType = 'product_set_to_old';
        if ($status) {
            $statusText = 'jetzt ' . Configure::read('AppConfig.db_config_FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' Tage lang als "neu" angezeigt';
            $actionLogType = 'product_set_to_new';
        }

        $message = 'Das Produkt "' . $product['ProductLangs']['name'] . '" vom Hersteller "' . $product['Manufacturers']['name'] . '" wird ' . $statusText . '.';
        $this->Flash->success($message);

        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $productId, 'products', $message);

        $this->request->session()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

    public function changeStatus($productId, $status)
    {

        $this->Product->changeStatus(
            array(
                array($productId => (int) $status)
            )
        );

        $product = $this->Product->find('first', array(
            'conditions' => array(
                'Products.id_product' => $productId
            )
        ));

        $statusText = 'deaktiviert';
        $actionLogType = 'product_set_inactive';
        if ($status) {
            $statusText = 'aktiviert';
            $actionLogType = 'product_set_active';
        }

        $this->Flash->success('Das Produkt "' . $product['ProductLangs']['name'] . '" wurde erfolgreich ' . $statusText . '.');

        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $productId, 'products', 'Das Produkt "' . $product['ProductLangs']['name'] . '" vom Hersteller "' . $product['Manufacturers']['name'] . '" wurde ' . $statusText . '.');

        $this->request->session()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }
}

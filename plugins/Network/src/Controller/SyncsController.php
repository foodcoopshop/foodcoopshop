<?php

namespace Network\Controller;

use App\Controller\AppController;
use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * SyncsController
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
class SyncsController extends AppController
{

    public function isAuthorized($user)
    {
        if (!$this->AppAuth->isManufacturer()) {
            return false;
        }

        $this->SyncDomain = TableRegistry::getTableLocator()->get('Network.SyncDomains');
        $this->SyncManufacturer = TableRegistry::getTableLocator()->get('Network.SyncManufacturers');
        $isAllowedToUseAsMasterFoodcoop = $this->SyncManufacturer->isAllowedToUseAsMasterFoodcoop($this->AppAuth);
        $syncDomains = $this->SyncDomain->getActiveManufacturerSyncDomains($this->AppAuth->manufacturer->enabled_sync_domains);
        return $isAllowedToUseAsMasterFoodcoop && count($syncDomains) > 0;
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $this->viewBuilder()->setLayout('Admin.default');
        $this->helpers[] = 'Network.Network';

        $this->SyncDomain = TableRegistry::getTableLocator()->get('Network.SyncDomains');
        $this->SyncProduct = TableRegistry::getTableLocator()->get('Network.SyncProducts');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
    }

    /**
     * @param array $product
     * @throws InvalidParameterException
     * @return array $syncDomain
     */
    private function doModifyProductChecks($product)
    {
        $syncDomain = $this->SyncDomain->find('all', [
            'conditions' => [
                'SyncDomains.domain' => $product['domain']
            ]
        ])->first();

        $localProductIds = $this->Product->getProductIdAndAttributeId($product['localProductId']);

        try {
            if (empty($syncDomain)) {
                throw new InvalidParameterException('Die Domain ' . $product['domain'] . ' wurde nicht gefunden.');
            }
            if (!$this->Product->isOwner($localProductIds['productId'], $this->AppAuth->getManufacturerId())) {
                throw new InvalidParameterException('Das Produkt ' . $localProductIds['productId'] . ' ist kein Produkt von Hersteller ' . $this->AppAuth->getManufacturerId());
            }
        } catch (InvalidParameterException $e) {
            $this->sendAjaxError($e);
        }

        return $syncDomain;
    }

    public function ajaxSaveProductRelation()
    {

        $this->RequestHandler->renderAs($this, 'json');

        $product = $this->getRequest()->getData('product');

        $syncDomain = $this->doModifyProductChecks($product);

        $localProductIds = $this->Product->getProductIdAndAttributeId($product['localProductId']);
        $remoteProductIds = $this->Product->getProductIdAndAttributeId($product['remoteProductId']);

        $status = $this->SyncProduct->save(
            $this->SyncProduct->newEntity(
                [
                    'sync_domain_id' => $syncDomain->id,
                    'local_product_id' =>  $localProductIds['productId'],
                    'remote_product_id' =>  $remoteProductIds['productId'],
                    'local_product_attribute_id' =>  $localProductIds['attributeId'],
                    'remote_product_attribute_id' =>  $remoteProductIds['attributeId']
                ]
            )
        );

        $message = 'Das Produkt';
        if ($remoteProductIds['attributeId'] > 0) {
            $message = 'Die Variante';
        }
        $message .= ' <b>'.$product['productName'].'</b> wurde erfolgreich zugeordnet.';
        if (!$status) {
            $message .= ' <b>'.$product['productName'].'</b> konnte <b>nicht</b> zugeordnet werden.';
        }

        $this->set('data', [
                'status' => !empty($status) ? true : $status,
                'product' => $product,
                'msg' => $message
            ]);

        $this->set('_serialize', 'data');
    }

    public function products()
    {

        $syncDomains = $this->SyncDomain->getActiveManufacturerSyncDomains($this->AppAuth->manufacturer->enabled_sync_domains);
        $this->set('syncDomains', $syncDomains);

        $matchedProducts = $this->getLocalSyncProducts($syncDomains);

        $localResponse = [
            'products' => $matchedProducts,
            'app' => [
                'name' => Configure::read('appDb.FCS_APP_NAME'),
                'domain' => Configure::read('app.cakeServerName')
            ]
        ];
        $localResponse = json_decode(json_encode($localResponse), true);
        $this->set('localResponse', $localResponse);

        $syncedProductsCount = 0;
        foreach ($matchedProducts as $matchedProduct) {
            if (!empty($matchedProduct->prepared_sync_products)) {
                $syncedProductsCount++;
                continue;
            }
        }
        $emptyProductsString = '';
        if ($syncedProductsCount == 0) {
            $emptyProductsString = $this->getEmptyProductsString($syncDomains);
        }
        $this->set('emptyProductsString', $emptyProductsString);

        $this->set('title_for_layout', 'Produkte zuordnen');
    }

    public function ajaxDeleteProductRelation()
    {

        $this->RequestHandler->renderAs($this, 'json');

        $product = $this->getRequest()->getData('product');

        $syncDomain = $this->doModifyProductChecks($product);

        $localProductIds = $this->Product->getProductIdAndAttributeId($product['localProductId']);
        $remoteProductIds = $this->Product->getProductIdAndAttributeId($product['remoteProductId']);

        $syncProduct = [
            'sync_domain_id' => $syncDomain->id,
            'local_product_id' =>  $localProductIds['productId'],
            'remote_product_id' =>  $remoteProductIds['productId'],
            'local_product_attribute_id' =>  $localProductIds['attributeId'],
            'remote_product_attribute_id' =>  $remoteProductIds['attributeId']
        ];

        $status = $this->SyncProduct->deleteAll($syncProduct) === 0 ? false : true;

        $message = 'Das Produkt <b>'.$product['productName'].'</b> wurde erfolgreich gelöscht.';
        if (!$status) {
            $message = 'Beim Löschen des Produktes <b>'.$product['productName'].'</b> ist ein Fehler aufgetreten.';
        }

        $this->set('data', [
                'status' => $status,
                'syncProduct' => $syncProduct,
                'msg' => $message
            ]);

        $this->set('_serialize', 'data');
    }

    public function productData()
    {

        $syncDomains = $this->SyncDomain->getActiveManufacturerSyncDomains($this->AppAuth->manufacturer->enabled_sync_domains);
        $this->set('syncDomains', $syncDomains);

        $this->SyncProduct = TableRegistry::getTableLocator()->get('Network.SyncProducts');
        $syncProducts = $this->SyncProduct->findAllSyncProducts($this->AppAuth->getManufacturerId());
        $preparedSyncProducts = [];
        foreach ($syncProducts as $syncProduct) {
            $preparedSyncProducts[$syncProduct->sync_domain->domain][] = [
                'remoteProductId' => $syncProduct->dash_separated_remote_product_id,
                'localProductId' => $syncProduct->dash_separated_local_product_id
            ];
        }
        $this->set('syncProducts', $preparedSyncProducts);

        $localSyncProducts = $this->getLocalSyncProducts($syncDomains);

        // keep only synced products
        $cleanedLocalSyncProducts = [];
        $foundProductIds = [];
        foreach ($localSyncProducts as $localSyncProduct) {
            foreach ($syncProducts as $syncProduct) {
                if ($syncProduct->dash_separated_local_product_id === $localSyncProduct->id_product && !in_array($localSyncProduct->id_product, $foundProductIds, true)) {
                    $cleanedLocalSyncProducts[] = $localSyncProduct;
                    $foundProductIds[] = $localSyncProduct->id_product;
                }
            }
        }
        $this->set('localSyncProducts', $cleanedLocalSyncProducts);

        $emptyProductsString = '';
        if (empty($preparedSyncProducts)) {
            $emptyProductsString = $this->getEmptyProductsString($syncDomains);
        }
        $this->set('emptyProductsString', $emptyProductsString);

        $this->set('title_for_layout', 'Produktdaten synchronisieren');
    }

    /**
     * @param array $syncDomains
     * @return string
     */
    private function getEmptyProductsString($syncDomains)
    {
        $syncDomainNames = Hash::extract($syncDomains, '{n}.SyncDomains.domain');
        $emptyProductsString = 'Du hast deinen Produkten auf der Master-Foodcoop <b>'.Configure::read('appDb.FCS_APP_NAME').'</b> noch keine Produkte ';
        $emptyProductsString .= ' der Remote-Foodcoop' . (count($syncDomainNames) != 1 ? 's' : '') . ' <b>(' . join(', ', $syncDomainNames) . ')</b> zugeordnet.<br /><br />';
        return $emptyProductsString;
    }

    /**
     * @return array
     */
    private function getLocalSyncProducts($syncDomains)
    {
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $products = $this->Product->getProductsForBackend($this->AppAuth, '', $this->AppAuth->getManufacturerId(), 'all', '', 0, 0, true);

        $indexes2Remove = [
          'DepositProducts',
          'CategoryProducts',
          'Categories',
          'Manufacturers',
          'Taxes',
          'selectedCategories'
        ];
        $products = $this->SyncProduct->removeIndexes($products, $indexes2Remove);

        $matchedProducts = $this->markProductsAsSynced($products, count($syncDomains));
        return $matchedProducts;
    }

    /**
     * check if already synced with local products
     * @param array $products
     * @param int $syncDomainsCount
     * @return array
     */
    private function markProductsAsSynced($products, $syncDomainsCount)
    {

        $syncProducts = $this->SyncProduct->findAllSyncProducts($this->AppAuth->getManufacturerId());

        foreach ($products as $product) {
            $syncCount = 0;
            $preparedSyncProducts = [];

            foreach ($syncProducts as $syncProduct) {
                if ($syncProduct->dash_separated_local_product_id === $product->id_product) {
                    $syncCount++;
                    $preparedSyncProducts[] = [
                        'domain' => $syncProduct->sync_domain->domain,
                        'name' => 'Name wird nach Login angezeigt...',
                        'remoteProductId' => $syncProduct->dash_separated_remote_product_id
                    ];
                }
            }

            if ($syncCount > 0) {
                $product->prepared_sync_products = $preparedSyncProducts;
            }
        }

        return $products;
    }
}

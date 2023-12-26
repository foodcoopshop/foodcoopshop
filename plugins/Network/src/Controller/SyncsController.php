<?php
declare(strict_types=1);

namespace Network\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SyncsController extends AppController
{

    protected $Configuration;
    protected $SyncDomain;
    protected $SyncManufacturer;
    protected $SyncProduct;
    protected $Product;

    public function isAuthorized($user)
    {
        if (!$this->identity->isManufacturer()) {
            return false;
        }

        $this->SyncDomain = $this->getTableLocator()->get('Network.SyncDomains');
        $this->SyncManufacturer = $this->getTableLocator()->get('Network.SyncManufacturers');
        $isAllowedToUseAsMasterFoodcoop = $this->SyncManufacturer->isAllowedToUseAsMasterFoodcoop($this->identity);
        $syncDomains = $this->SyncDomain->getActiveManufacturerSyncDomains(
            $this->identity->getManufacturerEnabledSyncDomains()
        );
        return $isAllowedToUseAsMasterFoodcoop && count($syncDomains) > 0;
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->Configuration = $this->getTableLocator()->get('Configurations');
        $this->viewBuilder()->setLayout('Admin.default');
        $this->viewBuilder()->addHelper('Network.Network');

        $this->SyncDomain = $this->getTableLocator()->get('Network.SyncDomains');
        $this->SyncProduct = $this->getTableLocator()->get('Network.SyncProducts');
        $this->Product = $this->getTableLocator()->get('Products');
    }

    private function doModifyProductChecks($product)
    {
        $syncDomain = $this->SyncDomain->find('all', [
            'conditions' => [
                'SyncDomains.domain' => $product['domain']
            ]
        ])->first();

        $localProductIds = $this->Product->getProductIdAndAttributeId($product['localProductId']);

        if (empty($syncDomain)) {
            throw new \Exception('the domain ' . $product['domain'] . ' was not found.');
        }
        if (!$this->Product->isOwner($localProductIds['productId'], $this->identity->getManufacturerId())) {
            throw new \Exception('product ' . $localProductIds['productId'] . ' is not associated with manufacturer ' . $this->identity->getManufacturerId());
        }

        return $syncDomain;
    }

    public function ajaxSaveProductRelation()
    {

        $this->RequestHandler->renderAs($this, 'json');

        $product = $this->getRequest()->getData('product');

        try {
            $syncDomain = $this->doModifyProductChecks($product);
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

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

        $type = __d('network', 'Product');
        if ($remoteProductIds['attributeId'] > 0) {
            $type = __d('network', 'Attribute');
        }
        $message = __d('network', '{0}_{1}_was_successfully_associated.', [$type, '<b>'.$product['productName'].'</b>']);
        if (!$status) {
            $message = __d('network', '{0}_{1}_could_not_be_associated.', [$type, '<b>'.$product['productName'].'</b>']);
        }

        $this->set([
            'status' => !empty($status) ? true : $status,
            'product' => $product,
            'msg' => $message
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'product', 'msg']);

    }

    public function products()
    {

        $syncDomains = $this->SyncDomain->getActiveManufacturerSyncDomains($this->identity->getManufacturerEnabledSyncDomains());
        $this->set('syncDomains', $syncDomains);

        $matchedProducts = $this->getLocalSyncProducts();

        $localResponse = [
            'products' => $matchedProducts,
            'app' => [
                'name' => Configure::read('appDb.FCS_APP_NAME'),
                'domain' => Configure::read('App.fullBaseUrl')
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

        $this->set('title_for_layout', __d('network', 'Associate_products'));
    }

    public function ajaxDeleteProductRelation()
    {

        $this->RequestHandler->renderAs($this, 'json');

        $product = $this->getRequest()->getData('product');

        try {
            $syncDomain = $this->doModifyProductChecks($product);
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

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

        $message = __d('network', 'The_product_{0}_has_been_deleted_successfully.', ['<b>'.$product['productName'].'</b>']);
        if (!$status) {
            $message = __d('network', 'While_deleting_the_product_{0}_there_has_an_error_occurred.', ['<b>'.$product['productName'].'</b>']);
        }

        $this->set([
            'status' => $status,
            'syncProduct' => $syncProduct,
            'msg' => $message
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'syncProduct', 'msg']);

    }

    public function productData()
    {

        $syncDomains = $this->SyncDomain->getActiveManufacturerSyncDomains($this->identity->getManufacturerEnabledSyncDomains());
        $this->set('syncDomains', $syncDomains);

        $this->SyncProduct = $this->getTableLocator()->get('Network.SyncProducts');
        $syncProducts = $this->SyncProduct->findAllSyncProducts($this->identity->getManufacturerId());
        $preparedSyncProducts = [];
        foreach ($syncProducts as $syncProduct) {
            $preparedSyncProducts[$syncProduct->sync_domain->domain][] = [
                'remoteProductId' => $syncProduct->dash_separated_remote_product_id,
                'localProductId' => $syncProduct->dash_separated_local_product_id
            ];
        }
        $this->set('syncProducts', $preparedSyncProducts);

        $localSyncProducts = $this->getLocalSyncProducts();

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

        $this->set('title_for_layout', __d('network', 'Synchronize_products'));
    }

    private function getEmptyProductsString($syncDomains)
    {
        $syncDomainNames = [];
        foreach($syncDomains as $syncDomain) {
            $syncDomainNames[] = $syncDomain->domain;
        }
        $emptyProductsString = __d('network', 'There_have_not_been_any_products_associated_from_your_master_foodcoop_{0}_to_your_remote_foodcoop(s)_{1}.', [
            '<b>'.Configure::read('appDb.FCS_APP_NAME').'</b>',
            '<b>(' . join(', ', $syncDomainNames) . ')</b>'
        ]);
        $emptyProductsString .= '<br /><br />';
        return $emptyProductsString;
    }

    private function getLocalSyncProducts()
    {
        $this->Product = $this->getTableLocator()->get('Products');
        $products = $this->Product->getProductsForBackend('', $this->identity->getManufacturerId(), 'all', '', false, false, true);
        $matchedProducts = $this->markProductsAsSynced($products);
        return $matchedProducts;
    }

    private function markProductsAsSynced($products)
    {

        $syncProducts = $this->SyncProduct->findAllSyncProducts($this->identity->getManufacturerId());

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

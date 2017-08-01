<?php
/**
 * SyncsController
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
class SyncsController extends AppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isManufacturer();
    }

    public function beforeFilter()
    {
        parent::beforeFilter();

        $this->layout = 'Admin.default';

        $this->loadModel('Network.SyncDomain');
        $this->loadModel('Network.SyncProduct');
    }

    public function ajaxSaveProduct()
    {

        $this->RequestHandler->renderAs($this, 'json');

        $product = $this->params['data']['product'];

        $syncProduct2save = array();
        $syncDomain = $this->SyncDomain->find('first', array(
            'conditions' => array(
                'SyncDomain.domain' => $product['domain']
            )
        ));
        $syncProduct2save = array(
            'sync_domain_id' => $syncDomain['SyncDomain']['id'],
            'local_product_id' =>  $product['localProductId'],
            'remote_product_id' =>  $product['remoteProductId']
        );
        $this->SyncProduct->id = null;
        $status = $this->SyncProduct->save($syncProduct2save);

        $message = 'Das Produkt <b>'.$product['productName'].'</b> wurde erfolgreich gespeichert.';
        if (!$status) {
            $message = 'Beim Speichern des Produktes <b>'.$product['productName'].'</b> ist ein Fehler aufgetreten.';
        }

        $this->set('data', array(
                'status' => !empty($status) ? true : $status,
                'product' => $product,
                'msg' => $message
            ));

        $this->set('_serialize', 'data');
    }

    public function products()
    {

        $syncDomains = $this->getSyncDomains();
        $this->set('syncDomains', $syncDomains);

        $matchedProducts = $this->getLocalSyncProducts($syncDomains);

        $localResponse = array(
            'products' => $matchedProducts,
            'app' => array(
                'name' => Configure::read('app.db_config_FCS_APP_NAME'),
                'domain' => Configure::read('app.cakeServerName')
            )
        );
        $this->set('localResponse', $localResponse);
    }

    /**
     * @return array
     */
    private function getSyncDomains()
    {
        $syncDomains = $this->SyncDomain->find('all', array(
            'conditions' => array(
                'SyncDomain.active' => APP_ON
            )
        ));
        return $syncDomains;
    }

    /**
     * @return array
     */
    private function getLocalSyncProducts($syncDomains)
    {
        $this->loadModel('Product');
        $pParams = $this->Product->getProductParams($this->AppAuth, '', $this->AppAuth->getManufacturerId(), 'all');
        $products = $this->Product->prepareProductsForBackend($this->Paginator, $pParams);

        $matchedProducts = $this->markProductsAsCompletelySynced($products, count($syncDomains));
        return $matchedProducts;
    }

    public function ajaxDeleteProduct()
    {

        $this->RequestHandler->renderAs($this, 'json');

        $remoteProductId = $this->params['data']['remoteProductId'];
        $localProductId = $this->params['data']['localProductId'];
        $domain = $this->params['data']['domain'];
        $productName = $this->params['data']['productName'];

        // TODO validation

        $syncDomain = $this->SyncDomain->find('first', array(
            'conditions' => array(
                'SyncDomain.domain' => $domain
            )
        ));

        $syncProduct = array(
            'SyncProduct.local_product_id' => $localProductId,
            'SyncProduct.remote_product_id' => $remoteProductId,
            'SyncProduct.sync_domain_id' => $syncDomain['SyncDomain']['id']
        );

        $status= $this->SyncProduct->deleteAll($syncProduct);

        $message = 'Das Produkt <b>'.$productName.'</b> wurde erfolgreich gelöscht.';
        if (!$status) {
            $message = 'Beim Löschen des Produktes <b>'.$productName.'</b> ist ein Fehler aufgetreten.';
        }

        $this->set('data', array(
                'status' => $status,
                'syncProduct' => $syncProduct,
                'msg' => $message
            ));

        $this->set('_serialize', 'data');
    }

    public function productData()
    {

        $syncDomains = $this->getSyncDomains();
        $this->set('syncDomains', $syncDomains);

        $this->loadModel('SyncProduct');
        $syncProducts = $this->SyncProduct->findAllSyncProducts();
        $preparedSyncProducts = array();
        foreach ($syncProducts as $syncProduct) {
            $preparedSyncProducts[$syncProduct['SyncDomain']['domain']][] = array(
                'remoteProductId' => $syncProduct['SyncProduct']['remote_product_id'],
                'localProductId' => $syncProduct['SyncProduct']['local_product_id']
            );
        }
        $this->set('syncProducts', $preparedSyncProducts);

        $localSyncProducts = $this->getLocalSyncProducts($syncDomains);
        // keep only synced products
        $cleanedLocalSyncProducts = array();
        $foundProductIds = array();
        foreach ($localSyncProducts as $localSyncProduct) {
            foreach ($syncProducts as $syncProduct) {
                if ($syncProduct['SyncProduct']['local_product_id'] == $localSyncProduct['Product']['id_product'] && !in_array($localSyncProduct['Product']['id_product'], $foundProductIds)) {
                    $cleanedLocalSyncProducts[] = $localSyncProduct;
                    $foundProductIds[] = $localSyncProduct['Product']['id_product'];
                }
            }
        }
        $this->set('localSyncProducts', $cleanedLocalSyncProducts);
    }

    /**
     * check if already synced with local products
     * @param array $products
     * $param int $syncDomainsCount
     * @return array
     */
    private function markProductsAsCompletelySynced($products, $syncDomainsCount)
    {

        $syncProducts = $this->SyncProduct->findAllSyncProducts();

        foreach ($products as &$product) {
            $syncCount = 0;
            $preparedSyncProducts = array();

            foreach ($syncProducts as $syncProduct) {
                if ($syncProduct['SyncProduct']['local_product_id'] == $product['Product']['id_product']) {
                    $syncCount++;
                    $preparedSyncProducts[] = array(
                        'domain' => $syncProduct['SyncDomain']['domain'],
                        'name' => 'Name wird nach Login angezeigt...',
                        'remoteProductId' => $syncProduct['SyncProduct']['remote_product_id']
                    );
                }
            }

            if ($syncCount > 0) {
                $product['PreparedSyncProducts'] = $preparedSyncProducts;
                $product['sync'] = $syncCount / $syncDomainsCount * 100;
            }
        }

        return $products;
    }
}

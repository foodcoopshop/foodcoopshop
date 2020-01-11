<?php

namespace Network\Model\Table;

use App\Model\Table\AppTable;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SyncProductsTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('SyncDomains', [
            'className' => 'Network.SyncDomains',
            'foreignKey' => 'sync_domain_id'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'local_product_id'
        ]);
    }

    public function addDashSeparatedProductIds($syncProducts)
    {
        foreach ($syncProducts as $syncProduct) {
            $remoteProductId = $syncProduct->remote_product_id;
            if ($syncProduct->remote_product_attribute_id > 0) {
                $remoteProductId = $remoteProductId . '-' . $syncProduct->remote_product_attribute_id;
            }
            $syncProduct->dash_separated_remote_product_id = $remoteProductId;

            $localProductId = $syncProduct->local_product_id;
            if ($syncProduct->local_product_attribute_id > 0) {
                $localProductId = $localProductId . '-' . $syncProduct->local_product_attribute_id;
            }
            $syncProduct->dash_separated_local_product_id = $localProductId;
        }
        return $syncProducts;
    }

    public function findAllSyncProducts($manufacturerId)
    {
        $syncProducts = $this->find('all', [
            'conditions' => [
                'SyncProducts.sync_domain_id > 0',
                'SyncDomains.active' => APP_ON,
                'Products.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'SyncDomains',
                'Products'
            ]
        ]);
        $syncProducts = $this->addDashSeparatedProductIds($syncProducts);
        return $syncProducts;
    }

    /**
     * @param array $indexes
     */
    public function removeIndexes($products, $indexes2Remove)
    {
        return $products;
    }
}

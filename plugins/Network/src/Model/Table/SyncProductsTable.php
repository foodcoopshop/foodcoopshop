<?php
declare(strict_types=1);

namespace Network\Model\Table;

use App\Model\Table\AppTable;
use Cake\ORM\Query\SelectQuery;

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

    public function addDashSeparatedProductIds($syncProducts): SelectQuery
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

    public function findAllSyncProducts($manufacturerId): SelectQuery
    {
        $syncProducts = $this->find('all',
        conditions: [
            'SyncProducts.sync_domain_id > 0',
            'SyncDomains.active' => APP_ON,
            'Products.id_manufacturer' => $manufacturerId
        ],
        contain: [
            'SyncDomains',
            'Products'
        ]);
        $syncProducts = $this->addDashSeparatedProductIds($syncProducts);
        return $syncProducts;
    }

}

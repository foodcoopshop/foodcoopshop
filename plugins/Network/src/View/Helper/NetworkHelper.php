<?php
declare(strict_types=1);

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

namespace Network\View\Helper;

use Cake\View\Helper;

class NetworkHelper extends Helper
{

    public array $helpers = ['MyHtml'];

    public function getTabs(): array
    {
        return [
            [
                'url' => $this->getSyncProducts(),
                'name' => __d('network', 'Associate_products')
            ],
            [
                'url' => $this->getSyncProductData(),
                'name' => __d('network', 'Synchronize_products')
            ]
        ];
    }

    public function getSyncProducts(): string
    {
        return '/network/syncs/products';
    }

    public function getSyncProductData(): string
    {
        return '/network/syncs/productData';
    }

    public function getSaveProductRelation(): string
    {
        return '/network/syncs/ajaxSaveProductRelation';
    }

    public function getDeleteProductRelation(): string
    {
        return '/network/syncs/ajaxDeleteProductRelation';
    }

    public function getSyncDomainAdd(): string
    {
        return '/network/sync-domains/add';
    }

    public function getSyncDomainEdit($syncDomainId): string
    {
        return '/network/sync-domains/edit/' . $syncDomainId;
    }

    public function getNetworkPluginDocs(): string
    {
        return $this->MyHtml->getDocsUrl(__d('network', 'docs_route_network_module'));
    }
}

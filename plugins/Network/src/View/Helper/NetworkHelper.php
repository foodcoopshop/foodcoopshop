<?php
/**
 * NetworkHelper
 *
 * TODO use cake's routing
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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace Network\View\Helper;

use Cake\View\Helper;

class NetworkHelper extends Helper
{

    public $helpers = ['MyHtml'];

    public function getSyncProducts()
    {
        return '/network/syncs/products';
    }

    public function getSyncProductData()
    {
        return '/network/syncs/productData';
    }

    public function getSaveProductRelation()
    {
        return '/network/syncs/ajaxSaveProductRelation';
    }

    public function getDeleteProductRelation()
    {
        return '/network/syncs/ajaxDeleteProductRelation';
    }

    public function getSyncDomainAdd()
    {
        return '/network/sync-domains/add';
    }

    public function getSyncDomainEdit($syncDomainId)
    {
        return '/network/sync-domains/edit/' . $syncDomainId;
    }

    public function getNetworkPluginDocs()
    {
        return $this->MyHtml->getDocsUrl(__d('network', 'docs_route_network_module'));
    }
}

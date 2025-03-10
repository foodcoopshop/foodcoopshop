<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait IndexTrait
{

    public function index(): void
    {
        $productId = h($this->getRequest()->getQuery('productId', ''));
        $this->set('productId', $productId);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
            if ($manufacturerId != 'all') {
                $manufacturerId = (int) $manufacturerId;
            }
        }

        $productsTable = $this->getTableLocator()->get('Products');

        // always filter by manufacturer id so that no other products than the own are shown
        if ($this->identity->isManufacturer()) {
            $manufacturerId = $this->identity->getManufacturerId();
        }
        $this->set('manufacturerId', $manufacturerId);

        $active = h($this->getRequest()->getQuery('active', 'all'));
        $this->set('active', $active);

        $categoryId = h($this->getRequest()->getQuery('categoryId', ''));
        $this->set('categoryId', $categoryId);

        if ($manufacturerId != '') {
            $preparedProducts = $productsTable->getProductsForBackend(
                productIds: $productId,
                manufacturerId: $manufacturerId,
                active: $active,
                categoryId: $categoryId,
                controller: $this,
            );
        } else {
            $preparedProducts = [];
        }
        $this->set('products', $preparedProducts);

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $attributesTable = $this->getTableLocator()->get('Attributes');
        $this->set('attributesForDropdown', $attributesTable->getForDropdown());
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $this->set('categoriesForDropdown', $categoriesTable->getForSelect(null, true));
        $this->set('categoriesForCheckboxes', $categoriesTable->getForSelect(null, true, true));
        $manufacturersForDropdown = ['all' => __d('admin', 'All_manufacturers')];
        $manufacturersForDropdown = array_merge($manufacturersForDropdown, $manufacturersTable->getForDropdown());
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $taxesTable = $this->getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $taxesTable->getForDropdown());

        if (is_int($manufacturerId)) {
            $manufacturer = $manufacturersTable->find('all',
                conditions: [
                    'Manufacturers.id_manufacturer' => $manufacturerId
                ]
            )
            ->select($manufacturersTable)
            ->first();
            $this->set('manufacturer', $manufacturer);
            $variableMemberFee = $manufacturersTable->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            $this->set('variableMemberFee', $variableMemberFee);
        }

        $advancedStockManagementEnabled = $manufacturerId == 'all' || (!empty($manufacturer) && $manufacturer->stock_management_enabled);
        $this->set('advancedStockManagementEnabled', $advancedStockManagementEnabled);

        $this->set('title_for_layout', __d('admin', 'Products'));

        if (Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS')) {
            $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
            $storageLocationsForForDropdown = $storageLocationsTable->getForDropdown();
            $this->set('storageLocationsForForDropdown', $storageLocationsForForDropdown);
        }

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED') && $this->identity->isManufacturer()) {
            $syncManufacturersTable = $this->getTableLocator()->get('Network.SyncManufacturers');
            $syncDomainsTable = $this->getTableLocator()->get('Network.SyncDomains');
            $this->viewBuilder()->addHelper('Network.Network');
            $isAllowedToUseAsMasterFoodcoop = $syncManufacturersTable->isAllowedToUseAsMasterFoodcoop($this->identity);
            $syncDomains = $syncDomainsTable->getActiveManufacturerSyncDomains($this->identity->getManufacturerEnabledSyncDomains());
            $showSyncProductsButton = $isAllowedToUseAsMasterFoodcoop && count($syncDomains) > 0;
            $this->set('showSyncProductsButton', $showSyncProductsButton);
        }
    }

}

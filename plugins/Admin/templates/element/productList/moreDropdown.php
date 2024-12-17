<?php
declare(strict_types=1);
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

$buttons = [];

if (!empty($products)) {
    $buttons[] = $this->element('productList/button/calculateSellingPriceWithSurchargeForSelectedProducts');
    $buttons[] = $this->element('productList/button/generateProductCardsOfSelectedProducts');
    $buttons[] = $this->element('productList/button/editStatusForSelectedProducts');
    $buttons[] = $this->element('productList/button/editDeliveryRhythmForSelectedProducts');
    $buttons[] = $this->element('productList/button/deleteSelectedProducts');
    $buttons[] = '<hr class="dropdown-divider" />';
    if ($identity->isManufacturer()) {
        $productImportUrl = $this->Slug->getMyProductImport();
    } else {
        $productImportUrl = $this->Slug->getProductImport($manufacturerId);
    }
    $buttons[] = '<a class="dropdown-item" href="' . $productImportUrl . '"><i class="fa-fw fas fa-file-import"></i> ' . __d('admin', 'Import_products') . '</a>';
    $buttons[] = $this->element('productList/button/exportProducts');
}

echo $this->element('dropdownWithButtons', [
    'helperLink' => $helperLink,
    'buttons' => $buttons,
    'label' => __d('admin', 'Actions') . '...'
]);

?>
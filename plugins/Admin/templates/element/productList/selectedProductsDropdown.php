<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
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
    $buttons[] = '<hr class="dropdown-divider" />';
    $buttons[] = $this->element('productList/button/deleteSelectedProducts');
}

echo $this->element('dropdownWithButtons', [
    'helperLink' => $helperLink,
    'buttons' => $buttons,
    'label' => __d('admin', 'Selected_products') . '...'
]);

?>
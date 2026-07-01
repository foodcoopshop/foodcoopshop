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

use Cake\Core\Configure;

echo '<th class="right">';
    echo $sortOrLabel('OrderDetails.product_amount', __('Amount'));
echo '</th>';

echo '<th class="stretch">';
    echo $sortOrLabel('OrderDetails.product_name', __('Product'));
echo '</th>';

echo '<th class="' . ($identity->isManufacturer() ? 'hide' : 'stretch') . '">';
    echo $sortOrLabel('Manufacturers.name', __('Manufacturer'));
echo '</th>';

echo '<th class="right">';
    echo $sortOrLabel('OrderDetails.total_price_tax_incl', __('Price'));
echo '</th>';

if (Configure::read('app.isDepositEnabled')) {
    echo '<th class="right">';
        echo $sortOrLabel('OrderDetails.deposit', __('Deposit'));
    echo '</th>';
}

echo '<th class="right">';
    echo $sortOrLabel('OrderDetailUnits.product_quantity_in_units', __('Weight'));
echo '</th>';

echo '<th class="stretch">'.$sortOrLabel('CustomerNameForOrder', __('Member')).'</th>';

if (count($pickupDay) == 2) {
    echo '<th>'.$sortOrLabel('OrderDetails.pickup_day', __('Pickup_day')) . '</th>';
}

echo '<th class="center">'.$sortOrLabel('OrderDetails.order_state', __('Status')).'</th>';
echo '<th></th>';


?>
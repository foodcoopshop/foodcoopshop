<?php
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

use Cake\Core\Configure;

echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetails.product_amount', __d('admin', 'Amount'));
echo '</th>';

echo '<th>';
    echo $this->Paginator->sort('OrderDetails.product_name', __d('admin', 'Product'));
echo '</th>';

echo '<th class="' . ($appAuth->isManufacturer() ? 'hide' : '') . '">';
    echo $this->Paginator->sort('Manufacturers.name', __d('admin', 'Manufacturer'));
echo '</th>';

echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetails.total_price_tax_incl', __d('admin', 'Price'));
echo '</th>';

if (Configure::read('app.isDepositEnabled')) {
    echo '<th class="right">';
        echo $this->Paginator->sort('OrderDetails.deposit', __d('admin', 'Deposit'));
    echo '</th>';
}

echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetailUnits.product_quantity_in_units', __d('admin', 'Weight'));
echo '</th>';

echo '<th>'.$this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Member')).'</th>';

if (count($pickupDay) == 2) {
    echo '<th>'.$this->Paginator->sort('OrderDetails.pickup_day', __d('admin', 'Pickup_day')) . '</th>';
}

echo '<th>'.$this->Paginator->sort('OrderDetails.order_state', __d('admin', 'Status')).'</th>';
echo '<th style="width:25px;"></th>';


?>
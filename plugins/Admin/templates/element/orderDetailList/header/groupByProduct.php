<?php
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
    echo $this->Paginator->sort('sum_amount', __d('admin', 'Amount'));
echo '</th>';

echo '<th>';
    echo $this->Paginator->sort('Products.name', __d('admin', 'Product'));
echo '</th>';

echo '<th class="' . ($appAuth->isManufacturer() ? 'hide' : '') . '">';
    echo $this->Paginator->sort('Manufacturers.name', __d('admin', 'Manufacturer'));
echo '</th>';

echo '<th class="right">';
    echo $this->Paginator->sort('sum_price', __d('admin', 'Price'));
echo '</th>';

if (Configure::read('app.isDepositEnabled')) {
    echo '<th class="right">';
        echo $this->Paginator->sort('sum_deposit', __d('admin', 'Deposit'));
    echo '</th>';
}
?>
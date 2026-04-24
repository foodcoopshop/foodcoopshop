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
echo $sortOrLabel('sum_amount', __('Amount'));
echo '</th>';

echo '<th class="' . ($identity->isManufacturer() ? 'hide' : '') . '">';
echo $sortOrLabel('Manufacturers.name', __('Manufacturer'));
echo '</th>';

echo '<th class="right">';
echo $sortOrLabel('sum_price', __('Price'));
echo '</th>';

if (Configure::read('app.isDepositEnabled')) {
    echo '<th>';
    echo $sortOrLabel('sum_deposit', __('Deposit'));
    echo '</th>';
}
?>
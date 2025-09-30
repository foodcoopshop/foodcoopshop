<?php
declare(strict_types=1);

use App\Services\OrderCustomerService;
use Cake\Utility\Hash;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<div class="fcs-badges">';
echo $this->element('catalog/badges/new', ['product' => $product]);
echo $this->element('catalog/badges/pickupDay', ['product' => $product]);
echo $this->element('catalog/badges/stockProduct', ['product' => $product]);
echo $this->element('catalog/badges/categories', ['product' => $product]);
echo $this->element('catalog/badges/edit', ['product' => $product]);
echo '</div>';

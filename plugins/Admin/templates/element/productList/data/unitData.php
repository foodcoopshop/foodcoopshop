<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (empty($product->unit)) {
    return;
}

echo '<span id="product-unit-object-' . $product->id_product . '" class="product-unit-object"></span>';
$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.setProductUnitData($('#product-unit-object-" . $product->id_product . "'),'" . json_encode($product->unit) . "');",
]);

?>

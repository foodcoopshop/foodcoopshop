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

echo '<td>';
if ($product->active && (! empty($product->product_attributes) || isset($product->product_attributes))) {

    echo $this->Html->link(
        '<i class="fas fa-arrow-right ok"></i>',
        $this->Slug->getProductDetail($product->id_product, $product->unchanged_name),
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'product_preview'),
            'target' => '_blank',
            'escape' => false
        ]
    );

}
echo '</td>';


?>
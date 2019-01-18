<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
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
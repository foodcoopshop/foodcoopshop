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

echo '<td>';
    if (empty($product->product_attributes)) {
        echo '<div class="table-cell-wrapper deposit">';
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light product-deposit-edit-button',
                    'title' => __d('admin', 'change_deposit'),
                    'escape' => false
                ]
            );
        if ($product->deposit > 0) {
            echo '<span class="deposit-for-dialog">';
            echo $this->Number->formatAsDecimal($product->deposit);
            echo '</span>';
        }
        echo '</div>';
    }
echo '</td>';

?>
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

use Cake\Core\Configure;

if ($groupBy == 'customer') {
    return false;
}

echo '<td class="right">';

    if (!empty($orderDetail->timebased_currency_order_detail)) {
        echo '<span id="timebased-currency-object-'.$orderDetail->id_order_detail.'" class="timebased-currency-object"></span>';
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Admin.setOrderDetailTimebasedCurrencyData($('#timebased-currency-object-".$orderDetail->id_order_detail."'),'".json_encode($orderDetail->timebased_currency_order_detail)."');"
        ]);
    }
    
    echo '<div class="table-cell-wrapper amount">';
    
        if ($groupBy == '') {
            if ($orderDetail->product_amount > 1 && $editRecordAllowed) {
                echo $this->Html->link(
                    '<i class="fas fa-pencil-alt ok"></i>',
                    'javascript:void(0);',
                    [
                        'class' => 'btn btn-outline-light order-detail-product-amount-edit-button',
                        'title' => __d('admin', 'Click_to_change_amount'),
                        'escape' => false
                    ]
                );
            }
            $amount = $orderDetail->product_amount;
            $style = '';
            if ($amount > 1) {
                $style = 'font-weight:bold;';
            }
            echo '<span class="product-amount-for-dialog" style="' . $style . '">' . $amount . '</span><span style="' . $style . '">x</span>';
        } else {
            echo $this->Number->formatAsDecimal($orderDetail['sum_amount'], 0) . 'x';
        }
    
    echo '</div>';
    
echo '</td>';

?>
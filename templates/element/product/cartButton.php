<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if ($hideButton) {
    return;
}
?>

<div class="line">
    <?php
    $availableQuantity = $stockAvailableQuantity;
    if ($product['is_stock_product'] && $product['stock_management_enabled']) {
        $availableQuantity = $stockAvailableQuantity - $stockAvailableQuantityLimit;
    }
    if (((($product['is_stock_product'] && $product['stock_management_enabled']) || !$stockAvailableAlwaysAvailable) && $availableQuantity <= 0)
        || (isset($shoppingLimitReached) && $shoppingLimitReached) 
        || $deliveryBreakEnabled) {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace') . ".Helper.disableButton($('#btn-cart-".$productId."'));"
        ]);
        
        if ($deliveryBreakEnabled) {
            $cartButtonIcon = 'fa-times';
            $cartButtonLabel = __('Delivery_break') . '!';
        }
        
    }
    ?>
    
    <a id="btn-cart-<?php echo $productId; ?>" class="btn btn-success btn-cart" href="javascript:void(0);">
        <i class="fa fa-lg <?php echo $cartButtonIcon; ?>"></i> <?php echo $cartButtonLabel; ?>
    </a>

</div>

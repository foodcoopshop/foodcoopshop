<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!$appAuth->user() || !Configure::read('appDb.FCS_CART_ENABLED')) {
    return;
}
?>

<div class="line">
    <?php
    if ($stockAvailable == 0 || (isset($shoppingLimitReached) && $shoppingLimitReached) || $appAuth->isManufacturer()) {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace') . ".Helper.disableButton($('#btn-cart-".$productId."'));"
        ]);
    }
    ?>
    
    <a id="btn-cart-<?php echo $productId; ?>" class="btn btn-success btn-cart" href="javascript:void(0);">
        <i class="fa fa-cart-plus"></i> In den Warenkorb
    </a>

</div>

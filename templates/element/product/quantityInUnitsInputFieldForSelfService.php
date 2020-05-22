<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */


if (!($pricePerUnitEnabled && $appAuth->isSelfServiceModeByUrl())) {
    return;
}
?>

<div class="quantity-in-units-input-field-wrapper">
    <span><?php echo __('Weight_in'); ?> <?php echo $unitName; ?>:</span>
    <input type="text" />
</div>


<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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


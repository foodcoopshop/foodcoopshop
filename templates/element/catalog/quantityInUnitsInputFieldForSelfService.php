<?php
declare(strict_types=1);

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
    <input class="calculator-output" type="number" />
    <a class="calculator-toggle-button" href="javascript:void(0);" style="margin-left:5px;">
        <i class="fas fa-calculator"></i>
    </a>
    <input class="calculator-input" type="text" placeholder="<?php echo __('Example_given_abbr'); ?> 167+142">
</div>


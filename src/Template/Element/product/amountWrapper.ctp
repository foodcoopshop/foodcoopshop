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

?>
<div class="amount-wrapper">

    <span class="left-of-input"><?php echo __('Amount'); ?></span>
    <input name="amount" value="1" type="text">

<?php if ($stockAvailable > 0 && $stockAvailable <= Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW')) { ?>
        <span class="right-of-input">(<?php echo $stockAvailable . ' ' . __('available'); ?>)</span>
<?php } ?>

</div>
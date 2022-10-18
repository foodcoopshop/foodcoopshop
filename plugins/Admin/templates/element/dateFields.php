<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.initNextAndPreviousDayLinks();"
]);
?>

<div class="date-field-wrapper">

    <a class="btn-arrow btn-previous-day" title="<?php echo __d('admin', '1_day_ahead'); ?>"
        href="javascript:void(0)"><i class="fas fa-arrow-circle-left fa"></i></a>
        <input type="text" autocomplete="off" class="datepicker"
            <?php echo (isset($nameFrom) ? 'name="'.$nameFrom.'"' : ''); ?>
            value="<?php echo $dateFrom; ?>" /> <a class="btn-arrow btn-next-day"
            title="<?php echo __d('admin', '1_day_back'); ?>" href="javascript:void(0)"><i
            class="fas fa-arrow-circle-right fa"></i></a>

    <?php if (!isset($showDateTo) || $showDateTo) { ?>
         <?php echo __d('admin', 'to'); ?> <a
        class="btn-arrow btn-previous-day" title="<?php echo __d('admin', '1_day_ahead'); ?>"
        href="javascript:void(0)"><i class="fas fa-arrow-circle-left fa"></i></a>
        <input type="text" autocomplete="off" class="datepicker"
            <?php echo (isset($nameTo) ? 'name="'.$nameTo.'"' : ''); ?>
            value="<?php echo $dateTo; ?>" /> <a class="btn-arrow btn-next-day"
            title="<?php echo __d('admin', '1_day_back'); ?>" href="javascript:void(0)"><i
            class="fas fa-arrow-circle-right fa"></i></a>
    <?php } ?>

</div>

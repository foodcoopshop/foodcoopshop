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

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.initNextAndPreviousDayLinks();"
]);
?>

<div class="date-field-wrapper">
    
    <a class="btn-arrow btn-previous-day" title="1 Tag zurück"
        href="javascript:void(0)"><i class="fa fa-arrow-circle-left fa"></i></a>
    <input id="dateFrom" type="text" class="datepicker"
        <?php echo (isset($nameFrom) ? 'name="'.$nameFrom.'"' : ''); ?>
        value="<?php echo $dateFrom; ?>" /> <a class="btn-arrow btn-next-day"
        title="1 Tag vor" href="javascript:void(0)"><i
        class="fa fa-arrow-circle-right fa"></i></a>
        
    <?php if (!isset($showDateTo) || $showDateTo) { ?>
         bis <a
        class="btn-arrow btn-previous-day" title="1 Tag zurück"
        href="javascript:void(0)"><i class="fa fa-arrow-circle-left fa"></i></a>
        <input id="dateTo" type="text" class="datepicker"
            <?php echo (isset($nameTo) ? 'name="'.$nameTo.'"' : ''); ?>
            value="<?php echo $dateTo; ?>" /> <a class="btn-arrow btn-next-day"
            title="1 Tag vor" href="javascript:void(0)"><i
            class="fa fa-arrow-circle-right fa"></i></a>
    <?php } ?>
    
</div>

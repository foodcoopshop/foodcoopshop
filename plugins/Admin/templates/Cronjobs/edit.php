<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [ 'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();" .
    Configure::read('app.jsNamespace') . ".Admin.initForm();".
    Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Cronjobs')."');
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
        <?php echo $this->element('printIcon'); ?>
    </div>
</div>

<?php
    echo $this->element('navTabs/configurationNavTabs', [
        'key' => 'cronjobs',
    ]);
?>
<div class="sc"></div>

<?php

echo $this->Form->create($cronjob, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $this->Slug->getCronjobEdit($cronjob->id),
    'id' => 'cronjobEditForm',
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<h1>' . $cronjob->name . '</h1>';

echo $this->Form->control('Cronjobs.time_interval', [
    'label' => __d('admin', 'Time_interval'),
    'type' => 'select',
    'options' => $timeIntervals,
]);

echo $this->Form->control('Cronjobs.day_of_month', [
    'label' => __d('admin', 'Day_of_month'),
    'type' => 'select',
    'empty' => __d('admin', 'Please_select...'),
    'options' => $daysOfMonth,
]);

echo $this->Form->control('Cronjobs.weekday', [
    'label' => __d('admin', 'Weekday'),
    'type' => 'select',
    'empty' => __d('admin', 'Please_select...'),
    'options' => $weekdays,
]);

echo $this->Form->control('Cronjobs.not_before_time', [
    'label' => __d('admin', 'Not_before_time'),
    'type' => 'time',
]);

echo $this->Form->end();

?>

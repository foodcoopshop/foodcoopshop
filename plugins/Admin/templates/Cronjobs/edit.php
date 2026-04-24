<?php
declare(strict_types=1);

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
use App\Model\Entity\Cronjob;

$this->element('addScript', [ 'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();" .
    Configure::read('app.jsNamespace') . ".Admin.initForm();".
    Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__('Website_administration')."', '".__('Configurations')."');
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __('Cancel'); ?></a>
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
    'label' => __('Time_interval'),
    'type' => 'select',
    'options' => $timeIntervals,
]);

echo $this->Form->control('Cronjobs.day_of_month', [
    'label' => __('Day_of_month'),
    'type' => 'select',
    'empty' => __('Please_select...'),
    'options' => $daysOfMonth,
]);

echo $this->Form->control('Cronjobs.weekday', [
    'label' => __('Weekday'),
    'type' => 'select',
    'empty' => __('Please_select...'),
    'options' => $weekdays,
]);

echo $this->Form->control('Cronjobs.not_before_time', [
    'label' => __('Not_before_time').' <span class="after small">'.__('Cronjob_is_called_up_to_10_min_after_the_given_time.').'</span>',
    'type' => 'time',
    'escape' => false,
]);

echo $this->Form->control('Cronjobs.active', [
    'label' => __('Active'),
    'type' => 'checkbox',
]);

if ($cronjob->id == Cronjob::SEND_INVOICES_TO_MANUFACTURERS_ID && Configure::read('app.extraBillingDayForManufacturers') != '') {
    $date = DateTime::createFromFormat('m-d', Configure::read('app.extraBillingDayForManufacturers'));
    if ($date !== false) {
        echo '<h2 class="info" style="margin-bottom: 10px;">';
            echo __('Extra billing') . ': ';
            echo $date->format('d') . '. ' . Configure::read('app.timeHelper')->getMonthName((int)$date->format('m'));
        echo '</h2>';
    }
}


echo $this->Form->end();

?>

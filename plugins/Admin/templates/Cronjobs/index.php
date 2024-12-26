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
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init(); " .
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('" . __d('admin', 'Website_administration') . "', '" . __d('admin', 'Configurations') . "');
    "
]);

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <?php
        echo $this->element('printIcon');
        ?>
    </div>

</div>

<?php

echo $this->element('navTabs/configurationNavTabs', [
    'key' => 'cronjobs',
]);

echo '<table class="list">';

echo '<tr class="sort">';
echo '<th class="hide">ID</th>';
echo '<th></th>';
echo '<th>' . __d('admin', 'Name') . '</th>';
echo '<th>' . __d('admin', 'Time_interval') . '</th>';
echo '<th style="text-align:center;">' . __d('admin', 'Day_of_month') . '</th>';
echo '<th>' . __d('admin', 'Weekday') . '</th>';
echo '<th style="text-align:center;">' . __d('admin', 'Not_before_time') . '</th>';
echo '<th>' . __d('admin', 'Active') . '</th>';
echo '<th>' . __d('admin', 'Last_run') . '</th>';
echo '</tr>';

$i = 0;
foreach ($cronjobs as $cronjob) {
    $i++;
    $rowClass = [
        'data'
    ];
    if (!$cronjob->active) {
        $rowClass[] = 'deactivated';
        $rowClass[] = 'line-through';
    }
    echo '<tr id="cronjob-' . $cronjob->id . '" class="' . implode(' ', $rowClass) . '">';

    echo '<td>';
    echo $this->Html->link(
        '<i class="fas fa-pencil-alt ok"></i>',
        $this->Slug->getCronjobEdit($cronjob->id),
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'Edit'),
            'escape' => false
        ]
    );
    echo '</td>';

    echo '<td class="hide">';
    echo $cronjob->id;
    echo '</td>';

    echo '<td>';
    echo $cronjob->name;
    echo '</td>';

    echo '<td>';
    echo $this->Time->getTranslatedTimeInterval($cronjob->time_interval);
    echo '</td>';

    echo '<td style="text-align:center;">';
    if ($cronjob->day_of_month != '') {
        $cronjobsTable = TableRegistry::getTableLocator()->get('Cronjobs');
        echo $cronjobsTable->getDaysOfMonth()[$cronjob->day_of_month];
    }
    echo '</td>';

    echo '<td>';
    if ($cronjob->weekday != '') {
        echo $this->Time->getWeekdayName(
            $this->Time->formatAsWeekday(
                strtotime('next ' . $cronjob->weekday), // trick to get eg. 6 from Saturday
            )
        );
    }
    echo '</td>';

    echo '<td style="text-align:center;">';
    echo $cronjob->not_before_time->i18nFormat($this->Time->getI18Format('TimeShort'));
    echo '</td>';

    echo '<td style="text-align:center;">';
    if ($cronjob->active == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    } else {
        echo '<i class="fas fa-minus-circle not-ok"></i>';
    }
    echo '</td>';

    echo '<td>';
    if (!empty($cronjob->cronjob_logs[0])) {
        /** @phpstan-ignore-next-line */
        $name = $cronjob->getOriginalValues()['name'];
        $cronjobFilterString = Inflector::underscore($name);
        if (preg_match('/SendInvoicesToManufacturers/', $name)) {
            $cronjobFilterString = 'send_invoices';
        }
        $cronjobFilterString = 'cronjob_' . $cronjobFilterString;
        if (preg_match('/SendInvoicesToCustomers/', $name)) {
            $cronjobFilterString = 'invoice_added';
        }
        $cronjobFilterString = $cronjobFilterString;
        echo $this->Html->link(
            $this->Time->getWeekdayName($this->Time->formatAsWeekday((int) $cronjob->cronjob_logs[0]->created->toUnixString())) . ', ' .
                $cronjob->cronjob_logs[0]->created->i18nFormat($this->Time->getI18Format('DateNTimeShort')),
            $this->Slug->getActionLogsList() . '/?dateFrom=' . date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime('-3 month')) . '&types[]=' . $cronjobFilterString,
        );
    }
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="8"><b>' . $i . '</b> ' . __d('admin', '{0,plural,=1{record} other{records}}', $i) . '</td>';
echo '</tr>';

echo '</table>';

?>
<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();".
    Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');"
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right"></div>
</div>

<?php

echo $this->element('navTabs/reportNavTabs', [
    'key' => 'credit_balance_sum',
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo
]);

echo '<table class="list">';
echo '<tr class="sort">';
    echo '<th>'.__d('admin', 'Members').'</th>';
    echo '<th>'.__d('admin', 'Sum_of_credits').'</th>';
    echo '<th>'.__d('admin', 'Included_deposit').'</th>';
echo '</tr>';

foreach($customers as $customer) {
    echo '<tr>';
        echo '<td>';
            echo $customer['customer_type'] . ($customer['count'] > 0 ? ' ('.$customer['count'].')' : '');
        echo '</td>';
        echo '<td class="' . ($customer['credit_balance'] < 0 ? 'negative' : '') . '">';
        echo $this->Number->formatAsCurrency($customer['credit_balance']);
        echo '</td>';
        echo '<td class="' . ($customer['payment_deposit_delta'] < 0 ? 'negative' : '') . '">';
        echo $this->Number->formatAsCurrency($customer['payment_deposit_delta']);
        echo '</td>';
    echo '</tr>';
}

echo '<tr>';
    echo '<td><b>Summe gesamt:</b></td>';
    echo '<td class="' . ($sums['credit_balance'] < 0 ? 'negative' : '') . '">';
        echo '<b>' . $this->Number->formatAsCurrency($sums['credit_balance']) . '</b>';
    echo '</td>';
    echo '<td class="' . ($sums['deposit_delta'] < 0 ? 'negative' : '') . '">';
        echo '<b>' . $this->Number->formatAsCurrency($sums['deposit_delta']) . '</b>';
    echo '</td>';
echo '</tr>';

echo '</table>';

?>
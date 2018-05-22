<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();".
    Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Homepage-Verwaltung', 'Finanzberichte');"
]);
?>

<div class="filter-container">
	<h1><?php echo $title_for_layout; ?></h1>
    <div class="right"></div>
</div>

<?php

echo $this->element('reportNavTabs', [
    'key' => 'credit_balance_sum',
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo
]);

echo '<table class="list">';
echo '<tr class="sort">';
    echo '<th>Mitglieder</th>';
    echo '<th>Guthaben gesamt</th>';
    echo '<th>Saldo Pfand</th>';
echo '</tr>';

foreach($customers as $customer) {
    echo '<tr>';
        echo '<td>';
            echo $customer['customer_type'] . ($customer['count'] > 0 ? ' ('.$customer['count'].')' : '');
        echo '</td>';
        echo '<td class="' . ($customer['credit_balance'] < 0 ? 'negative' : '') . '">';
        echo $this->Html->formatAsEuro($customer['credit_balance']);
        echo '</td>';
        echo '<td class="' . ($customer['payment_deposit_delta'] < 0 ? 'negative' : '') . '">';
        echo $this->Html->formatAsEuro($customer['payment_deposit_delta']);
        echo '</td>';
    echo '</tr>';
}

echo '<tr>';
    echo '<td><b>Summe gesamt:</b></td>';
    echo '<td class="' . ($sums['credit_balance'] < 0 ? 'negative' : '') . '">';
        echo '<b>' . $this->Html->formatAsEuro($sums['credit_balance']) . '</b>';
    echo '</td>';
    echo '<td class="' . ($sums['deposit_delta'] < 0 ? 'negative' : '') . '">';
        echo '<b>' . $this->Html->formatAsEuro($sums['deposit_delta']) . ' *</b>';
    echo '</td>';
echo '</tr>';

echo '</table>';

echo '<p style="padding:5px;">
          * Von den '.$this->Html->formatAsEuro($sums['credit_balance']).' auf dem Guthaben-Konto sind '.$this->Html->formatAsEuro($sums['deposit_delta']).' für Pfand-Rückzahlungen reserviert. Solange der Pfand-Betrag negativ ist, ist Geld für die Rückzahlungen vorhanden.
      </p>';
?>
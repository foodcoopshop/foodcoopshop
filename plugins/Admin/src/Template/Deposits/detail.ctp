<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>


<?php
$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Admin.initDeletePayment();"
]);
?>

<div class="filter-container">
<h1><?php echo $title_for_layout; ?></h1>
        <div class="right"></div>
    </div>

<?php

echo '<br /><p>Für '.Configure::read('app.timeHelper')->getMonthName($month) . ' ' . $year.'</p>';

echo '<table class="list no-clone-last-row">';
echo '<tr class="sort">';
    echo '<th>Datum</th>';
    echo '<th>Text</th>';
    echo '<th style="text-align:right;">Pfand-Rücknahme</th>';
    echo '<th style="width:25px;"></th>';
echo '</tr>';

$sum = 0;
foreach ($payments as $payment) {
    $sum += $payment['Payments']['amount'];
    echo '<tr class="data ' . $payment['Payments']['type'] . '">';

        echo '<td class="hide">';
            echo $payment['Payments']['id'];
        echo '</td>';

        echo '<td>';
            echo $this->Time->formatToDateNTimeLong($payment['Payments']['date_add']);
        echo '</td>';

        echo '<td>';
            echo $this->Html->getManufacturerDepositPaymentText($payment['Payments']['text']);
        echo '</td>';

        echo '<td style="text-align:right;" class="negative">';
            echo $this->Html->formatAsEuro($payment['Payments']['amount'] * -1);
        echo '</td>';

        echo '<td style="text-align:center;">';
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
                'class' => 'delete-payment-button',
                'title' => 'Pfand-Rücknahme löschen?'
            ], 'javascript:void(0);');
        echo '</td>';

    echo '</tr>';
}

    echo '<tr>';
        echo '<td></td>';
        echo '<td>Summe</td>';
        echo '<td class="right negative">';
            echo '<b style="font-size: 16px;">'.$this->Html->formatAsEuro($sum * -1).'</b>';
        echo '</td>';
        echo '<td></td>';
    echo '</tr>';


echo '</table>';

echo '<div class="bottom-button-container">';
if ($appAuth->isManufacturer()) {
    $depositOverviewUrl = $this->Slug->getMyDepositList();
} else {
    $depositOverviewUrl = $this->Slug->getDepositList($manufacturerId);
}
    echo '<a class="btn btn-default" href="'.$depositOverviewUrl.'"><i class="fa fa-arrow-circle-left"></i> Zurück zum Pfandkonto</a>';
echo '</div>';
echo '<div class="sc"></div>';


?>

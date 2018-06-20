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

echo $this->element('paymentHeader', [
    'icons' => $this->element('printIcon'),
    'extraInfo' => Configure::read('appDb.FCS_MEMBER_FEE_BANK_ACCOUNT_DATA'),
    'buttonText' => 'Eingezahlten Mitgliedsbeitrag eintragen',
    'icon' => 'fa-heart'
]);

if (count($payments) == 0) {
    ?>
<p>Es wurde noch kein <?php echo $title_for_layout; ?> erfasst.</p>
<?php
} else {
    echo '<table class="list">';
    echo '<tr class="sort">';
    echo '<th>Datum</th>';
    echo '<th>Text</th>';
    echo '<th style="text-align:right;">' . $column_title . '</th>';
    echo '<th style="width:25px;"></th>';
    echo '</tr>';

    $i = 0;
    foreach ($payments as $payment) {
        $i ++;

        echo '<tr class="data ' . $payment['type'] . '">';

        echo '<td class="hide">';
        echo $payment['payment_id'];
        echo '</td>';

        echo '<td>';
        echo $this->Time->formatToDateNTimeLong($payment['date']);
        echo '</td>';

        echo '<td>';
        if ($payment['type'] == 'member_fee') {
            echo $payment['text'];
        } else {
            echo $payment['text'];
        }
        echo '</td>';

        echo '<td style="text-align:right;">';
        echo $this->Number->formatAsCurrency($payment['amount']);
        echo '</td>';

        echo '<td style="text-align:center;">';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
            'class' => 'delete-payment-button',
            'title' => 'Aufladung löschen?'
        ], 'javascript:void(0);');
        echo '</td>';

        echo '</tr>';
    }

    echo '<tr class="fake-th">';
    echo '<td>Datum</td>';
    echo '<td>Text</td>';
    echo '<td style="text-align:right;">' . $title_for_layout . '</td>';
    echo '<td style="width:25px;"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td></td>';
    echo '<td><b style="font-size: 16px;">Summe: ' . $this->Number->formatAsCurrency($sumMemberFee) . '</b></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '</tr>';

    echo '</table>';
} // end of count($payments)

if ($this->request->getParam('action') == 'member_fee') {
    echo '<div class="bottom-button-container">';
    echo '<a class="btn btn-default" href="'.$this->Slug->getCustomerListAdmin().'"><i class="fa fa-arrow-circle-left"></i> Zurück zur Mitglieder-Übersicht</a>';
    echo '</div>';
}
?>
<div class="sc"></div>

</div>
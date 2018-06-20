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
    'icons' => $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_credit_system'))]),
    'extraInfo' => Configure::read('appDb.FCS_BANK_ACCOUNT_DATA'),
    'buttonText' => 'Eingezahltes Guthaben eintragen',
    'icon' => 'fa-'.strtolower(Configure::read('app.currencyName'))
]);

if (count($payments) == 0) {
    ?>
<p>Es wurde noch kein <?php echo $title_for_layout; ?> erfasst.</p>
<?php
} else {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initTooltip('.payment-approval-comment');"
    ]);

    if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
        $this->element('addScript', [
            'script' => Configure::read('app.jsNamespace') . ".Helper.initTooltip('.timebased-currency-time-element');"
        ]);
    }
    
    echo '<table class="list">';
    echo '<tr class="sort">';
        echo '<th>Datum</th>';
        echo '<th>Text</th>';
        echo '<th style="text-align:right;">' . $column_title . '</th>';
        echo '<th style="text-align:right;">Bestellwert</th>';
        echo '<th ' . (! Configure::read('app.isDepositPaymentCashless') ? 'class="hide" ' : '') . 'style="text-align:right;">Pfand</th>';
        echo '<th style="width:25px;"></th>';
    echo '</tr>';

    $i = 0;
    $sumPayments = 0;
    $sumDeposits = 0;
    $sumOrders = 0;

    foreach ($payments as $payment) {
        $i ++;

        $rowClass = ['data', $payment['type']];

        if (isset($oldYear) && $oldYear != $payment['year']) {
            $rowClass[] = 'last-row-of-year';
        }

        echo '<tr class="' . implode(' ', $rowClass) . '">';

        echo '<td class="hide">';
        echo $payment['payment_id'];
        echo '</td>';

        echo '<td>';
        echo $payment['dateRaw']->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
        echo '</td>';

        echo '<td>';

        if ($payment['type'] == 'product') {
            switch ($payment['approval']) {
                case APP_DEL:
                    echo $this->Html->image(
                        $this->Html->getFamFamFamPath('delete.png'),
                        [
                            'class' => 'payment-approval'
                        ]
                    );
                    break;
                case APP_OFF:
                    break;
                case APP_ON:
                    echo $this->Html->image(
                        $this->Html->getFamFamFamPath('accept.png'),
                        [
                            'class' => 'payment-approval'
                        ]
                    );
                    break;
            }
            if ($payment['approval_comment'] != '') {
                echo '<span class="payment-approval-comment-wrapper">';
                    echo $this->Html->getJqueryUiIcon(
                        $this->Html->image($this->Html->getFamFamFamPath('user_comment.png')),
                        [
                            'class' => 'payment-approval-comment',
                            'title' => $payment['approval_comment']
                        ],
                        'javascript:void(0);'
                    );
                echo '</span>';
            }
        }

        echo $payment['text'];
        echo '</td>';

        $numberClass = '';
        if ($payment['type'] == 'order') {
            $numberClass = ' class="negative"';
        }


        $productNumberClass = '';
        if (in_array($payment['type'], ['payback'])) {
            $productNumberClass = ' class="negative"';
        }
        echo '<td style="text-align:right;" ' . $productNumberClass . '>';
        if (in_array($payment['type'], ['product', 'payback'])) {
            if ($payment['type'] == 'payback') {
                $payment['amount'] = $payment['amount'] * -1;
            }
            $sumPayments += $payment['amount'];
            echo $this->Number->formatAsCurrency($payment['amount']);
        }
        echo '</td>';

        echo '<td style="text-align:right;" ' . $numberClass . '>';
        if ($payment['type'] == 'order') {
            $sumOrders += $payment['amount'];
            echo $this->Number->formatAsCurrency($payment['amount']);
            if (!empty($payment['timebased_currency_order'])) {
                echo '<b class="timebased-currency-time-element" title="Zusätzlich in '.Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'). ': ' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($payment['timebased_currency_order']->seconds_sum).'">&nbsp;*</b>';
            }
        }
        echo '</td>';

        echo '<td ' . (! Configure::read('app.isDepositPaymentCashless') ? 'class="hide" ' : '') . 'style="text-align:right;" ' . $numberClass . '>';
        if ($payment['deposit'] < 0) {
            if ($payment['type'] == 'order') {
                $sumDeposits += $payment['deposit'];
                echo $this->Number->formatAsCurrency($payment['deposit']);
            }
        }
        if ($payment['type'] == 'deposit') {
            $sumDeposits += $payment['amount'];
            echo $this->Number->formatAsCurrency($payment['amount']);
        }
        echo '</td>';

        echo '<td style="text-align:center;">';
        $deletablePaymentTypes = ['product', 'deposit'];
        if ($appAuth->isSuperadmin()) {
            $deletablePaymentTypes[] = 'payback';
        }
        if (in_array($payment['type'], $deletablePaymentTypes) && $payment['approval'] != APP_ON) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
                'class' => 'delete-payment-button',
                'title' => 'Aufladung löschen?'
            ], 'javascript:void(0);');
        }
        echo '</td>';

        echo '</tr>';

        $oldYear = $payment['year'];
    }

    echo '<tr class="fake-th">';
    echo '<td>Datum</td>';
    echo '<td>Text</td>';
    echo '<td style="text-align:right;">Guthaben</td>';
    echo '<td style="text-align:right;">Bestellwert</td>';
    echo '<td ' . (! Configure::read('app.isDepositPaymentCashless') ? 'class="hide" ' : '') . 'style="text-align:right;">Pfand</td>';
    echo '<td style="width:25px;"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2"></td>';
    echo '<td align="right"><b>' . $this->Number->formatAsCurrency($sumPayments) . '</b></td>';
    echo '<td align="right" class="negative"><b>' . $this->Number->formatAsCurrency($sumOrders) . '</b></td>';
    $sumDepositsClass = '';
    if ($sumDeposits < 0) {
        $sumDepositsClass = ' class="negative"';
    }
    if (! Configure::read('app.isDepositPaymentCashless')) {
        $sumDepositsClass = ' class="hide"';
    }
    echo '<td ' . $sumDepositsClass . 'align="right"><b>' . $this->Number->formatAsCurrency($sumDeposits) . '</b></td>';
    echo '<td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td></td>';
    $sumNumberClass = '';
    if ($creditBalance < 0) {
        $sumNumberClass = ' class="negative"';
    }
    echo '<td ' . $sumNumberClass . '><b style="font-size: 16px;">Dein Kontostand: ' . $this->Number->formatAsCurrency($creditBalance) . '</b></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '<td></td>';
    if (Configure::read('app.isDepositPaymentCashless')) {
        echo '<td></td>';
    }

    echo '</tr>';

    echo '</table>';
} // end of count($payments)

if ($this->request->getParam('action') == 'product') {
    echo '<div class="bottom-button-container">';
        echo '<a class="btn btn-default" href="'.$this->Slug->getCustomerListAdmin().'"><i class="fa fa-arrow-circle-left"></i> Zurück zur Mitglieder-Übersicht</a>';
    echo '</div>';
}

echo $this->TimebasedCurrency->getOrderInformationText($timebasedCurrencyOrderInList);

?>


<div class="sc"></div>

</div>

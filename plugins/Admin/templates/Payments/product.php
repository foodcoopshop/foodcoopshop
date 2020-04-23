<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual() || $this->request->getParam('action') == 'product') {
    echo $this->element('payment/addTypeManualHeader', [
        'icons' => $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_credit_system'))]),
        'extraInfo' => Configure::read('appDb.FCS_BANK_ACCOUNT_DATA'),
        'buttonText' => __d('admin', 'Add_transfered_credit'),
        'icon' => $this->Html->getFontAwesomeIconForCurrencyName(Configure::read('app.currencyName'))
    ]);
} else {
    echo $this->element('payment/addTypeListUploadHeader');
}
if (count($payments) == 0) {
    ?>
<p><?php echo __d('admin', 'There_is_no_{0}_available.', [$title_for_layout]); ?></p>
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
        echo '<th>'.__d('admin', 'Date').'</th>';
        echo '<th>'.__d('admin', 'Text').'</th>';
        echo '<th style="text-align:right;">' . $column_title . '</th>';
        echo '<th style="text-align:right;">'.__d('admin', 'Order_value').'</th>';
        echo '<th ' . (! Configure::read('app.isDepositPaymentCashless') ? 'class="hide" ' : '') . 'style="text-align:right;">'.__d('admin', 'Deposit').'</th>';
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
                    echo '<i class="fas fa-minus-circle not-ok payment-approval"></i>';
                    break;
                case APP_OFF:
                    break;
                case APP_ON:
                    echo '<i class="fas fa-check-circle ok payment-approval"></i>';
                    break;
            }
            if ($payment['approval_comment'] != '') {
                echo $this->Html->link(
                    '<i class="fas fa-comment-dots ok"></i>',
                    'javascript:void(0);',
                    [
                        'class' => 'btn btn-outline-light payment-approval-comment',
                        'title' => $payment['approval_comment'],
                        'escape' => false
                    ]
                );
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
            if (!empty($payment['timebased_currency_sum_seconds'])) {
                echo '<b class="timebased-currency-time-element" title="Zusätzlich in '.Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'). ': ' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($payment['timebased_currency_sum_seconds']).'"> *</b>';
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
        $deletablePaymentTypes = ['product'];
        if (!$appAuth->isCustomer() || Configure::read('app.isCustomerAllowedToModifyOwnOrders')) {
            $deletablePaymentTypes[] = 'deposit';
        }
        if ($appAuth->isSuperadmin()) {
            $deletablePaymentTypes[] = 'payback';
        }
        if (in_array($payment['type'], $deletablePaymentTypes) && $payment['approval'] != APP_ON) {
            echo $this->Html->link(
                '<i class="fas fa-times-circle not-ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light delete-payment-button',
                    'title' => __d('admin', 'Delete_upload?'),
                    'escape' => false
                ]
            );
        }
        echo '</td>';

        echo '</tr>';

        $oldYear = $payment['year'];
    }

    echo '<tr class="fake-th">';
    echo '<td>Datum</td>';
    echo '<td>Text</td>';
    echo '<td style="text-align:right;">'.__d('admin', 'Credit').'</td>';
    echo '<td style="text-align:right;">'.__d('admin', 'Order_value').'</td>';
    echo '<td ' . (! Configure::read('app.isDepositPaymentCashless') ? 'class="hide" ' : '') . 'style="text-align:right;">'.__d('admin', 'Deposit').'</td>';
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
    echo '<td ' . $sumNumberClass . '><b style="font-size: 16px;">'.__d('admin', 'Your_credit_balance').': ' . $this->Number->formatAsCurrency($creditBalance) . '</b></td>';
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
    echo '<a class="btn btn-outline-light" href="'.$this->Slug->getCustomerListAdmin().'"><i class="fas fa-arrow-circle-left"></i> '.__d('admin', 'Back_to_member_overview').'</a>';
    echo '</div>';
}

echo $this->TimebasedCurrency->getOrderInformationText($timebasedCurrencyOrderDetailInList);

?>
<div class="sc"></div>

</div>
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

$this->element('addScript', array(
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        var datefieldSelector = $('input.datepicker');
        datefieldSelector.datepicker();" . Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Helper.initTooltip('.payment-approval-comment');".
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Homepage-Verwaltung', 'Finanzberichte');"
));
?>

<div class="filter-container">
	<h1><?php echo $title_for_layout; ?></h1>
	<?php echo $this->element('dateFields', array('dateFrom' => $dateFrom, 'dateTo' => $dateTo)); ?>
    <?php echo $this->Form->input('customerId', array('type' => 'select', 'label' => '', 'empty' => 'alle Mitglieder', 'options' => $customersForDropdown, 'selected' => isset($customerId) ? $customerId: '')); ?>
    <button id="filter" class="btn btn-success">
		<i class="fa fa-search"></i> Filtern
	</button>
	<div class="right"></div>
</div>

<div id="help-container">
	<ul>
		<li>
			Hier findest du die Auswertung für: <?php echo $this->Html->getPaymentText($paymentType); ?>
		</li>
		<li>Gelöschte Einzahlungen werden ausgegraut angeführt.</li>
	</ul>
</div>

<ul class="nav nav-tabs">
	<?php
foreach ($this->Html->getPaymentTexts() as $pt => $paymentText) {
    $btnClass = '';
    if ($pt == $this->params['pass'][0]) {
        $btnClass = 'active';
    }
    // show deposit report also for cash configuration
    if ($this->Html->paymentIsCashless() || in_array($pt, array('deposit', 'member_fee', 'member_fee_flexible'))) {
        echo '<li class="' . $btnClass . '"><a href="' . $this->Slug->getReport($pt) . '/dateFrom:' . $dateFrom . '/dateTo:' . $dateTo . '">' . $paymentText . '</a></li>';
    }
}
?>
</ul>

<?php

echo '<table class="list">';
echo '<tr class="sort">';
$colspan = 2;
if ($paymentType == 'product') {
    echo '<th style="width:25px;"></th>';
    echo '<th style="width:50px;">' . $this->Paginator->sort('CakePayment.approval', 'Status') . '</th>';
    $colspan = $colspan + 2;
}
echo '<th>' . $this->Paginator->sort('Customer.name', 'Name') . '</th>';
echo '<th>' . $this->Paginator->sort('CakePayment.date_add', 'Eingetragen am') . '</th>';
echo '<th>' . $this->Html->getPaymentText($paymentType) . '</th>';
if ($showTextColumn) {
    echo '<th>' . $this->Paginator->sort('CakePayment.text', 'Text') . '</th>';
}
echo '</tr>';

$i = 0;
$paymentSum = 0;

foreach ($payments as $payment) {

    $rowClass = '';
    $additionalText = '';
    if ($payment['CakePayment']['status'] == APP_DEL) {
        $rowClass = 'deactivated';
        $additionalText = ' (' . $this->Html->getPaymentText($paymentType) . ' gelöscht am ' . $this->Time->formatToDateNTimeShort($payment['CakePayment']['date_changed']) . ' - scheint in der Summe nicht auf)';
    } else {
        $i ++;
        $paymentSum += $payment['CakePayment']['amount'];
    }

    echo '<tr class="data ' . $rowClass . '">';

    if ($paymentType == 'product') {
        echo '<td>';
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), array(
                'title' => 'Bearbeiten'
            ),
            $this->Slug->getPaymentEdit($payment['CakePayment']['id']));
        echo '</td>';
        echo '<td>';
            switch($payment['CakePayment']['approval']) {
                case -1;
                    echo $this->Html->image(
                        $this->Html->getFamFamFamPath('delete.png'),
                        array(
                            'class' => 'payment-approval'
                        )
                    );
                    break;
                case 0;
                    break;
                case 1;
                    echo $this->Html->image(
                        $this->Html->getFamFamFamPath('accept.png'),
                        array(
                            'class' => 'payment-approval'
                        )
                    );
                    break;
            }
            if ($payment['CakePayment']['approval_comment'] != '') {
                echo '<span class="payment-approval-comment-wrapper">';
                echo $this->Html->getJqueryUiIcon(
                    $this->Html->image($this->Html->getFamFamFamPath('user_comment.png')),
                    array(
                        'class' => 'payment-approval-comment',
                        'title' => $payment['CakePayment']['approval_comment']
                    ),
                    'javascript:void(0);');
                    echo '</span>';
            }
        echo '</td>';
    }

    echo '<td>';
        if (!empty($payment['Manufacturer']['name'])) {
            echo $payment['Manufacturer']['name'];
        } else {
            echo $payment['Customer']['name'];
        }
        echo $additionalText;
    echo '</td>';

    echo '<td style="text-align:right;width:110px;">';
    echo $this->Time->formatToDateNTimeShort($payment['CakePayment']['date_add']);
    echo '</td>';

    echo '<td style="text-align:right;">';
    echo $this->Html->formatAsEuro($payment['CakePayment']['amount']);
    echo '</td>';

    if ($showTextColumn) {
        echo '<td>';
        switch($paymentType) {
            case 'member_fee':
                echo $this->Html->getMemberFeeTextForFrontend($payment['CakePayment']['text']);
                break;
            case 'deposit':
                echo $this->Html->getManufacturerDepositPaymentText($payment['CakePayment']['text']);
                break;
            default:
                echo $payment['CakePayment']['text'];
        }
        echo '</td>';
    }

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="'.$colspan.'"><b>' . $i . '</b> Datensätze</td>';
echo '<td style="text-align:right;"><b>' . $this->Html->formatAsEuro($paymentSum) . '</b></td>';
if ($showTextColumn) {
    echo '<td></td>';
}
echo '</tr>';

echo '</table>';

echo '<div class="sc"></div>';

?>

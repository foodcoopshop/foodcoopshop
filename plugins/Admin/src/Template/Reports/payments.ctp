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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        $('input.datepicker').datepicker();" .
        Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Helper.initTooltip('.payment-approval-comment');".
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');"
]);
if ($paymentType == 'product') {
    $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#payment-'
    ]);
}
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameTo' => 'dateTo', 'nameFrom' => 'dateFrom']); ?>
        <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_members'), 'options' => $customersForDropdown, 'default' => isset($customerId) ? $customerId: '']); ?>
        <div class="right">
        	<?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_financial_reports'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php

echo $this->element('reportNavTabs', [
    'key' => $this->request->getParam('pass')[0],
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
]);

echo '<table class="list">';
echo '<tr class="sort">';
$colspan = 3;
if ($paymentType == 'product') {
    echo '<th style="width:25px;"></th>';
    echo '<th style="width:50px;">' . $this->Paginator->sort('Payments.approval', __d('admin', 'Status')) . '</th>';
    $colspan = $colspan + 2;
}
echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Member')) . '</th>';
echo '<th>' . $this->Paginator->sort('Payments.date_add', __d('admin', 'Added_on')) . '</th>';
echo '<th>' . $this->Paginator->sort('CreatedBy.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Added_by')) . '</th>';
echo '<th>' . $this->Html->getPaymentText($paymentType) . '</th>';
if ($showTextColumn) {
    echo '<th>' . $this->Paginator->sort('Payments.text', __d('admin', 'Text')) . '</th>';
}
echo '</tr>';

$i = 0;
$paymentSum = 0;

foreach ($payments as $payment) {
    $rowClass = '';
    $additionalText = '';
    if ($payment->status == APP_DEL) {
        $rowClass = 'deactivated line-through';
        $additionalText = ' (' . $this->Html->getPaymentText($paymentType) . ' '.__d('admin', 'deleted_on').' ' . $payment->date_changed->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')) . ' - '.__d('admin', 'does_not_appear_in_sum.').')';
    } else {
        $i ++;
        $paymentSum += $payment->amount;
    }

    echo '<tr id="payment-'.$payment->id.'" class="data ' . $rowClass . '">';

    if ($paymentType == 'product') {
        echo '<td>';
            echo $this->Html->getJqueryUiIcon(
                $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
                [
                'title' => __d('admin', 'Edit')
                ],
                $this->Slug->getPaymentEdit($payment->id)
            );
        echo '</td>';
        echo '<td>';
        switch ($payment->approval) {
            case -1:
                echo $this->Html->image(
                    $this->Html->getFamFamFamPath('delete.png'),
                    [
                        'class' => 'payment-approval'
                    ]
                );
                break;
            case 0:
                break;
            case 1:
                echo $this->Html->image(
                    $this->Html->getFamFamFamPath('accept.png'),
                    [
                        'class' => 'payment-approval'
                    ]
                );
                break;
        }
        if ($payment->approval_comment != '') {
            echo '<span class="payment-approval-comment-wrapper">';
            echo $this->Html->getJqueryUiIcon(
                $this->Html->image($this->Html->getFamFamFamPath('user_comment.png')),
                [
                    'class' => 'payment-approval-comment',
                    'title' => $payment->approval_comment
                ],
                'javascript:void(0);'
            );
            echo '</span>';
        }
        echo '</td>';
    }

    echo '<td>';
    if (!empty($payment->manufacturer)) {
        echo $payment->manufacturer->name;
    } else {
        echo $this->Html->getNameRespectingIsDeleted($payment->customer);
    }
        echo $additionalText;
    echo '</td>';

    echo '<td style="text-align:right;width:135px;">';
        echo $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td>';
    if (!empty($payment->created_by_customer)) {
        echo $payment->created_by_customer->name;
    }
    echo '</td>';

    echo '<td style="text-align:right;">';
        echo $this->Number->formatAsCurrency($payment->amount);
    echo '</td>';

    if ($showTextColumn) {
        echo '<td>';
        switch ($paymentType) {
            case 'member_fee':
                echo $this->Html->getMemberFeeTextForFrontend($payment->text);
                break;
            case 'deposit':
                echo $this->Html->getManufacturerDepositPaymentText($payment->text);
                break;
            default:
                echo $payment->text;
        }
        echo '</td>';
    }

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="'.$colspan.'"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
echo '<td style="text-align:right;"><b>' . $this->Number->formatAsCurrency($paymentSum) . '</b></td>';
if ($showTextColumn) {
    echo '<td></td>';
}
echo '</tr>';

echo '</table>';

echo '<div class="sc"></div>';

?>

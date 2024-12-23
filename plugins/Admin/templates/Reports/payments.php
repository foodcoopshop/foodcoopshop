<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use App\Model\Entity\Payment;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        $('input.datepicker').datepicker();" .
        Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Helper.initTooltip('.payment-approval-comment');".
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');".
        Configure::read('app.jsNamespace') . ".Admin.initCustomerDropdown(" . ($customerId != '' ? $customerId : '0') . ", 0, 1);"
]);
if ($paymentType == Payment::TYPE_PRODUCT) {
    $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#payment-'
    ]);
}
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameTo' => 'dateTo', 'nameFrom' => 'dateFrom']); ?>
        <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'placeholder' => __d('admin', 'all_members'), 'options' => []]); ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_infos_for_success'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php

echo $this->element('navTabs/reportNavTabs', [
    'key' => $this->request->getParam('pass')[0],
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
]);

$useCsvUpload = !Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual() && $this->request->getParam('pass')[0] == Payment::TYPE_PRODUCT;
if ($useCsvUpload) {
    echo $this->element('payment/csvUpload', [
        'csvRecords' => $csvRecords ?? null,
    ]);
}

echo '<table class="list">';
echo '<tr class="sort">';
$colspan = 3;
if ($useCsvUpload) {
    $colspan++;
}
$this->Paginator->setPaginated($payments);
if (in_array($paymentType, [Payment::TYPE_PRODUCT, Payment::TYPE_PAYBACK])) {
    echo '<th style="width:25px;"></th>';
    echo '<th style="width:50px;">' . $this->Paginator->sort('Payments.approval', __d('admin', 'Status')) . '</th>';
    $colspan = $colspan + 2;
}
echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Member')) . '</th>';
echo '<th>' . $this->Paginator->sort('Payments.date_add', __d('admin', 'Added_on')) . '</th>';
echo '<th>' . $this->Paginator->sort('CreatedByCustomers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Added_by')) . '</th>';
if ($useCsvUpload) {
    echo '<th>' . $this->Paginator->sort('Payments.date_transaction_add', __d('admin', 'Transaction_added_on')) . '</th>';
}
echo '<th style="text-align:right;">' . $this->Paginator->sort('Payments.amount', $this->Html->getPaymentText($paymentType)) . '</th>';
if ($showTextColumn) {
    echo '<th>' . $this->Paginator->sort('Payments.text', __d('admin', 'Text')) . '</th>';
}
echo '</tr>';

$i = 0;
$paymentSum = 0;

foreach ($payments as $payment) {
    $rowClass = '';
    if ($payment->status == APP_DEL) {
        $rowClass = 'deactivated line-through';
    } else {
        $i ++;
        $paymentSum += $payment->amount;
    }

    echo '<tr id="payment-'.$payment->id.'" class="data ' . $rowClass . '">';

    if (in_array($paymentType, [Payment::TYPE_PRODUCT, Payment::TYPE_PAYBACK])) {
        echo '<td>';
        if ($payment->status > APP_DEL) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                $this->Slug->getPaymentEdit($payment->id),
                [
                    'class' => 'btn btn-outline-light',
                    'title' => __d('admin', 'Edit'),
                    'escape' => false
                ]
            );
        }
        echo '</td>';
        echo '<td style="text-align:right;width:51px;">';
        echo match($payment->approval) {
            -1 => '<i class="fas fa-minus-circle not-ok payment-approval"></i>',
             0 => '',
             1 => '<i class="fas fa-check-circle ok payment-approval"></i>',
             default => '',
        };

        if ($payment->approval_comment != '') {
            echo $this->Html->link(
                '<i class="fas fa-comment-dots ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light payment-approval-comment',
                    'title' => $payment->approval_comment,
                    'escape' => false
                ]
            );
        }
        if ($payment->status == APP_DEL) {
            $infoText = $this->Html->getPaymentText($paymentType) . ' '.__d('admin', 'deleted_on').' ' . $payment->date_changed->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')) . ' - '.__d('admin', 'does_not_appear_in_sum.');
            echo '<i class="fas fa-minus-circle not-ok" title="' . h($infoText) . '"></i>';
        }
        echo '</td>';
    }

    echo '<td>';
    if (!empty($payment->manufacturer)) {
        echo $payment->manufacturer->name;
    } else {
        echo $this->Html->getNameRespectingIsDeleted($payment->customer);
    }
    echo '</td>';

    echo '<td style="text-align:right;width:140px;">';
        echo $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
    echo '</td>';

    echo '<td>';
    if (!empty($payment->created_by_customer)) {
        echo $payment->created_by_customer->name;
    }
    echo '</td>';

    if ($useCsvUpload) {
        echo '<td style="text-align:right;width:140px;">';
            if ($payment->date_transaction_add) {
                echo $payment->date_transaction_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
            }
        echo '</td>';
    }

    echo '<td style="text-align:right;">';
        echo $this->Number->formatAsCurrency($payment->amount);
    echo '</td>';

    if ($showTextColumn) {
        echo '<td>';
        if ($paymentType == Payment::TYPE_DEPOSIT) {
            echo $this->Html->getManufacturerDepositPaymentText($payment->text);
        } else {
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

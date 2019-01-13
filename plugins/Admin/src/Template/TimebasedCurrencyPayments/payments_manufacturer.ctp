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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Admin.init();".
    Configure::read('app.jsNamespace').".Admin.initForm();".
    Configure::read('app.jsNamespace').".Helper.initTooltip('.customer-detail');"
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
    	<?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_paying_with_time_module'))]); ?>
    </div>
</div>

<?php

$tableColumnHead  = '<th></th>';
$tableColumnHead .='<th style="text-align:right;">Unbestätigt</th>';
$tableColumnHead .='<th style="text-align:right;">Geleistet</th>';
$tableColumnHead .='<th style="text-align:right;">Offen</th>';
$tableColumnHead .='<th style="text-align:right;">Saldo</th>';

echo '<table class="list">';

    echo '<tr class="sort">';
        echo $tableColumnHead;
    echo '</tr>';

    foreach($payments as $payment) {

        echo '<tr>';

            echo '<td>';

                $details = $this->Html->getCustomerAddress($payment['customer']);
                $details .= '<br />' . (empty($payment['customer']) ? '' : $payment['customer']->email);
                
                echo '<span style="float:left;margin-right:5px;margin-top:1px;">';
                    echo '<i class="fas fa-phone-square ok fa-lg customer-detail" title="'.h($details).'"></i>';
                echo '</span>';

                echo '<span style="float:left;margin-right:3px;">' . $this->Html->getNameRespectingIsDeleted($payment['customer']).'</span>';

                if ($appAuth->isManufacturer()) {
                    $detailLink = $this->Slug->getTimebasedCurrencyPaymentDetailsForManufacturers($payment['customerId']);
                } else {
                    $detailLink = $this->Slug->getTimebasedCurrencyPaymentDetailsForSuperadmins($payment['manufacturerId'], $payment['customerId']);
                }
                echo $this->Html->link(
                    '<i class="fas fa-search ok"></i> ' . __d('admin', 'Details'),
                    $detailLink,
                    [
                        'class' => 'btn btn-outline-light',
                        'title' => __d('admin', 'Details'),
                        'escape' => false
                    ]
                ).'</span>';

            echo '</td>';

            echo '<td align="right">';
                if ($payment['unapprovedCount'] > 0) {
                    echo '<b>'.$payment['unapprovedCount'].'</b>';
                }
            echo '</td>';

            echo '<td class="negative" align="right">';
                echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($payment['secondsDone']);
            echo '</td>';

            echo '<td align="right">';
                echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($payment['secondsOpen']);
            echo '</td>';

            $creditBalanceClass = '';
            if ($payment['creditBalance'] < 0) {
                $creditBalanceClass = 'negative';
            }
            echo '<td class="'.$creditBalanceClass.'" align="right">';
                echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($payment['creditBalance']);
            echo '</td>';

        echo '</tr>';

    }


    echo '<tr class="fake-th">';
        echo str_replace('th', 'td', $tableColumnHead);
    echo '</tr>';

    $sumNumberClass = '';
    if ($creditBalance < 0) {
        $sumNumberClass = ' class="negative"';
    }
    echo '<tr>';
        echo '<td></td>';
        echo '<td align="right"><b>' . $sumUnapprovedPaymentsCount . '</b></td>';
        echo '<td align="right" class="negative"><b>' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($sumPayments) . '</b></td>';
        echo '<td align="right"><b>' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($sumOrders) . '</b></td>';
        echo '<td align="right" ' . $sumNumberClass . '><b>' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($creditBalance) . '</b></td>';
    echo '</tr>';

    echo '<tr>';
        echo '<td align="right" ' . $sumNumberClass . '><b style="font-size: 16px;">' .$paymentBalanceTitle . ': ' .  $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($creditBalance) . '</b></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
    echo '</tr>';

echo '</table>';


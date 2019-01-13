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

echo $this->element('paymentHeader', [
    'icons' => $this->element('printIcon'),
    'extraInfo' => Configure::read('appDb.FCS_MEMBER_FEE_BANK_ACCOUNT_DATA'),
    'buttonText' => __d('admin', 'Add_transfered_member_fee'),
    'icon' => 'fa-heart'
]);

if (count($payments) == 0) {
    ?>
<p><?php echo __d('admin', 'There_is_no_{0}_available.', [$title_for_layout]); ?></p>
<?php
} else {
    echo '<table class="list">';
    echo '<tr class="sort">';
    echo '<th>'.__d('admin', 'Date').'</th>';
    echo '<th>'.__d('admin', 'Text').'</th>';
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
        echo $payment['dateRaw']->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
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
            echo $this->Html->link(
                '<i class="fas fa-times-circle not-ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light delete-payment-button',
                    'title' => __d('admin', 'Delete_payment?'),
                    'escape' => false
                ]
            );
        echo '</td>';

        echo '</tr>';
    }

    echo '<tr class="fake-th">';
    echo '<td>'.__d('admin', 'Date').'</td>';
    echo '<td>'.__d('admin', 'Text').'</td>';
    echo '<td style="text-align:right;">' . $title_for_layout . '</td>';
    echo '<td style="width:25px;"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td></td>';
    echo '<td><b style="font-size: 16px;">'.__d('admin', 'Sum').': ' . $this->Number->formatAsCurrency($sumMemberFee) . '</b></td>';
    echo '<td></td>';
    echo '<td></td>';
    echo '</tr>';

    echo '</table>';
} // end of count($payments)

if ($this->request->getParam('action') == 'member_fee') {
    echo '<div class="bottom-button-container">';
    echo '<a class="btn btn-outline-light" href="'.$this->Slug->getCustomerListAdmin().'"><i class="fas fa-arrow-circle-left"></i> '.__d('admin', 'Back_to_member_overview').'</a>';
    echo '</div>';
}
?>
<div class="sc"></div>

</div>
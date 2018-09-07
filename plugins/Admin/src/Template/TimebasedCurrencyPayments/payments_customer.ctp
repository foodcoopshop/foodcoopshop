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
    Configure::read('app.jsNamespace').".Admin.initForm('".$this->TimebasedCurrency->getName()."');".
    Configure::read('app.jsNamespace').".Admin.selectMainMenuAdmin('Stundenkonto');".
    Configure::read('app.jsNamespace').".Helper.initTooltip('.comment');"
]);

$this->element('highlightRowAfterEdit', [
    'rowIdPrefix' => '#timebased-currency-payment-'
]);

$colspan = 5;
if ($isDeleteAllowedGlobally) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".TimebasedCurrency.initDeletePayment();"
    ]);
}
?>

<ul class="help-text-wrapper">
    <?php echo '<li>' . join('</li><li>', $helpText) . '</li>'; ?>
</ul>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <?php if ($showManufacturerDropdown) { ?>
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php
                echo $this->Form->control('manufacturerId', [
                    'type' => 'select',
                    'label' => '',
                    'options' => $manufacturersForDropdown,
                    'empty' => __d('admin', 'All_manufacturers'),
                    'default' => $manufacturerId != '' ? $manufacturerId : ''
                ]);
            ?>
        <?php echo $this->Form->end(); ?>
    <?php } ?>
    <div class="right">
    	<?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_paying_with_time_module'))]); ?>
    </div>
</div>

<?php
    if ($showAddForm) {
        echo '<div id="add-timebased-currency-payment-button-wrapper">';
        echo $this->Html->link('<i class="fa fa-clock-o fa-lg"></i> Geleistete Zeit eintragen',
            $this->Slug->getTimebasedCurrencyPaymentAdd($customerId),
            [
                'class' => 'btn btn-success',
                'escape' => false
            ]);
        echo '</div>';
    }

$tableColumnHead  = '<th>Status</th>';
$tableColumnHead .= '<th>Datum Eintragung / Bestellung</th>';
$tableColumnHead .= '<th>Arbeitstag</th>';
$tableColumnHead .= '<th>Hersteller</th>';
$tableColumnHead .= '<th>Text</th>';
$tableColumnHead .= '<th style="text-align:right;">Geleistet</th>';
$tableColumnHead .= '<th style="text-align:right;">Offen</th>';
$tableColumnHead .= '<th style="width:25px;"></th>';

echo '<table class="list">';

    echo '<tr class="sort">';
        echo $tableColumnHead;
    echo '</tr>';

    foreach($payments as $payment) {

        $rowClass = [];
        if ($payment['status'] == APP_DEL || $payment['approval'] == APP_DEL) {
            $rowClass = ['deactivated', 'line-through'];
        }

        echo '<tr id="timebased-currency-payment-'.$payment['paymentId'].'" data-payment-id="'.$payment['paymentId'].'" class="' . join(' ', $rowClass) . '">';

            echo '<td style="text-align:center;width:50px;">';
                if ($payment['status'] > APP_DEL) {
                    switch ($payment['approval']) {
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
                }
                if ($payment['approvalComment'] != '') {
                    echo $this->Html->image(
                        $this->Html->getFamFamFamPath('comment.png'),
                        [
                            'class' => 'comment',
                            'title' => $payment['approvalComment']
                        ]
                        );
                }

            echo '</td>';

            echo '<td>';
                echo $payment['dateRaw']->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
            echo '</td>';

            echo '<td>';
                if (!empty($payment['workingDay'])) {
                    echo $payment['workingDay']->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
                }
            echo '</td>';

            echo '<td style="width: 180px;">';
                echo $payment['manufacturerName'];
            echo '</td>';

            echo '<td style="width: 180px;">';
                if ($payment['paymentId'] && $payment['text'] != '') {
                    echo $this->Html->image(
                        $this->Html->getFamFamFamPath('comment.png'),
                        [
                            'class' => 'comment',
                            'title' => $payment['text']
                        ]
                    );
                } else {
                    echo $payment['text'];
                }
            echo '</td>';

            echo '<td align="right">';
                if ($payment['secondsDone']) {
                    echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($payment['secondsDone']);
                }
            echo '</td>';

            echo '<td class="negative" align="right">';
                if ($payment['secondsOpen']) {
                    echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($payment['secondsOpen']);
                }
            echo '</td>';

            echo '<td style="text-align:center;width:'.($isEditAllowedGlobally && $isDeleteAllowedGlobally ? 50 : 25).'px;">';
                if ($payment['isEditAllowed'] && $isEditAllowedGlobally) {
                    echo $this->Html->getJqueryUiIcon(
                        $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
                        [
                            'title' => 'bearbeiten?'
                        ],
                        $this->Slug->getTimebasedCurrencyPaymentEdit($payment['paymentId'])
                    );
                }
                if ($isDeleteAllowedGlobally) {
                    if ($payment['isDeleteAllowed']) {
                        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
                            'class' => 'delete-payment-button',
                            'title' => 'löschen?'
                        ], 'javascript:void(0);');
                    }
                }
            echo '</td>';

        echo '</tr>';

    }


    echo '<tr class="fake-th">';
        echo str_replace('th', 'td', $tableColumnHead);
    echo '</tr>';

    echo '<tr>';
        echo '<td colspan="'.$colspan.'"></td>';
        echo '<td align="right"><b>' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($sumPayments) . '</b></td>';
        echo '<td align="right" class="negative"><b>' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($sumOrders) . '</b></td>';
        echo '<td></td>';
    echo '</tr>';

    echo '<tr>';
        echo '<td></td>';
        $sumNumberClass = '';
        if ($creditBalance < 0) {
            $sumNumberClass = ' class="negative"';
        }
        $reducedColspan = $colspan - 1;
        echo '<td colspan="'.$reducedColspan.'" ' . $sumNumberClass . '><b style="font-size: 16px;">' . $paymentBalanceTitle . ': ' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($creditBalance) . '</b></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
    echo '</tr>';

echo '</table>';


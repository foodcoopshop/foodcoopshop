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

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();
    "
]);
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php
        if (!$appAuth->isManufacturer()) {
            echo $this->Form->control('manufacturerId', [
            'type' => 'select',
            'label' => '',
            'options' => $manufacturersForDropdown,
            'empty' => __d('admin', 'All_manufacturers'),
            'default' => $manufacturerId != '' ? $manufacturerId : ''
            ]);
        }
        ?>
        <div class="right">
        	<?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_deposit'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php
if (empty($manufacturer)) {
    echo '<br /><h2 class="info">'.__d('admin', 'Please_chose_a_manufacturer.').'</h2>';
    return;
}

echo '<div class="add-payment-deposit-wrapper">';
if ($appAuth->isManufacturer() || $appAuth->isAdmin()) {
    $buttonText = __d('admin', 'Add_return_of_empty_glasses');
}
if ($appAuth->isSuperadmin()) {
    $buttonText = __d('admin', 'Add_deposit_return');
}
    echo $this->element('addDepositPaymentOverlay', [
        'buttonText' => $buttonText,
        'rowId' => $manufacturer->id_manufacturer,
        'userName' => $manufacturer->name,
        'manufacturerId' => $manufacturer->id_manufacturer
    ]);
    echo '</div>';
    echo '<div class="sc"></div>';

    if (empty($deposits)) {
        echo '<h2 class="info">'.__d('admin', 'Since_{0}_there_was_no_deposit_delivered_or_returned.', [date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(Configure::read('app.depositForManufacturersStartDate')))]).'</h2>';
    } else {
        echo '<table class="list no-clone-last-row">';

        echo '<tr class="sort">';
            echo '<th class="right">'.__d('admin', 'Month').'</th>';
            echo '<th class="right">'.__d('admin', 'Product_with_deposit_delivered').'</th>';
            echo '<th class="right">'.__d('admin', 'Empty_glasses_returned').'</th>';
        echo '</tr>';

        foreach ($deposits as $monthAndYear => $deposit) {
            echo '<tr>';

                echo '<td>';
                    echo $deposit['monthAndYearAsString'];
                echo '</td>';

                echo '<td class="right">';
            if (isset($deposit['delivered'])) {
                echo '<span style="float: left;">'.$this->Html->getJqueryUiIcon(
                    $this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' Details',
                    [
                        'title' => __d('admin', 'Show_details'),
                    'class' => 'icon-with-text',
                    ],
                    '/admin/order-details/?manufacturerId='.$manufacturerId.'&dateFrom='.$deposit['dateFrom'].'&dateTo='.$deposit['dateTo'].'&deposit=1&orderStates[]='.join(',', $orderStates)
                ).'</span>';
                echo '<span style="float: right;">';
                echo $this->Number->formatAsCurrency($deposit['delivered']);
                echo '</span>';
            }
                echo '</td>';

                echo '<td class="right negative">';
            if (isset($deposit['returned'])) {
                echo '<span style="float: left;">'.$this->Html->getJqueryUiIcon(
                    $this->Html->image($this->Html->getFamFamFamPath('zoom.png')) . ' Details',
                    [
                    'title' => __d('admin', 'Show_details'),
                    'class' => 'icon-with-text',
                    ],
                    $appAuth->isManufacturer() ? $this->Slug->getMyDepositDetail($monthAndYear) : $this->Slug->getDepositDetail($manufacturerId, $monthAndYear)
                ).'</span>';
                echo '<span style="float: right;">';
                echo $this->Number->formatAsCurrency($deposit['returned']);
                echo '</span>';
            }
                echo '</td>';
            echo '</tr>';
        }

        echo '<tr class="fake-th">';
            echo '<td></td>';
            echo '<td class="right"><b>'.__d('admin', 'Delivered_deposit').'</b></td>';
            echo '<td class="right"><b>'.__d('admin', 'Returned_deposit').'</b></td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td></td>';
            echo '<td class="right"><b>'.$this->Number->formatAsCurrency($sumDepositsDelivered).'</b></td>';
            echo '<td class="right negative">';
        if ($sumDepositsReturned != 0) {
            echo '<b>'.$this->Number->formatAsCurrency($sumDepositsReturned).'</b>';
        }
            echo '</td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td colspan="2" class="right"><b>'.__d('admin', 'Your_deposit_balance').'</td>';
            $depositCreditBalance = $sumDepositsDelivered + $sumDepositsReturned;
            $depositCreditBalanceClasses = ['right'];
        if ($depositCreditBalance < 0) {
            $depositCreditBalanceClasses[] = 'negative';
        }
            echo '<td class="'.implode(' ', $depositCreditBalanceClasses).'"><b style="font-size: 16px;">'.$this->Number->formatAsCurrency($depositCreditBalance).'</b></td>';
        echo '</tr>';

        echo '</table>';
    }

?>

<div class="sc"></div>

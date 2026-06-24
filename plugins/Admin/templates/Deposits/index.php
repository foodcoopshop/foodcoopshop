<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
        if (!$identity->isManufacturer()) {
            echo $this->Form->control('manufacturerId', [
            'type' => 'select',
            'label' => '',
            'options' => $manufacturersForDropdown,
            'empty' => __('All_manufacturers'),
            'default' => $manufacturerId != '' ? $manufacturerId : ''
            ]);
        }
        ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__('docs_route_deposit'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php
if (empty($manufacturer)) {
    echo '<h2 class="info">'.__('Please_chose_a_manufacturer.').'</h2>';
    return;
}

echo '<div class="add-payment-deposit-wrapper">';
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".ModalPaymentAdd.initDepositInList();"
    ]);
    echo $this->element('addDepositPaymentOverlay', [
        'buttonText' => __('Add_return_of_empty_glasses'),
        'objectId' => $manufacturer->id_manufacturer,
        'userName' => $manufacturer->name,
        'manufacturerId' => $manufacturer->id_manufacturer
    ]);
    echo '</div>';
    echo '<div class="sc"></div>';

    if (empty($deposits)) {
        echo '<h2 class="info">'.__('Since_{0}_there_was_no_deposit_delivered_or_returned.', [date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(Configure::read('app.depositForManufacturersStartDate')))]).'</h2>';
    } else {
        echo '<table class="list no-clone-last-row">';

        echo '<tr class="sort">';
            echo '<th class="right">'.__('Month').'</th>';
            echo '<th class="right">'.__('Product_with_deposit_delivered').'</th>';
            echo '<th class="right">'.__('Empty_glasses_returned').'</th>';
        echo '</tr>';

        foreach ($deposits as $monthAndYear => $deposit) {
            echo '<tr>';

                echo '<td>';
                    echo $deposit['monthAndYearAsString'];
                echo '</td>';

                echo '<td>';
                    if (isset($deposit['delivered'])) {
                        echo '<div class="td-spanned">';
                            echo $this->Html->link(
                                '<i class="fas fa-search ok"></i> '  . __('Details'),
                                '/admin/order-details/?manufacturerId='.$manufacturerId.'&pickupDay[]='.$deposit['dateFrom'].'&pickupDay[]='.$deposit['dateTo'].'&deposit=1',
                                [
                                    'class' => 'btn btn-outline-light',
                                    'title' => __('Show_details'),
                                    'escape' => false
                                ]
                            );
                            echo '<span class="right">';
                                echo $this->Number->formatAsCurrency($deposit['delivered']);
                            echo '</span>';
                        }
                    echo '</div>';
                echo '</td>';

                $classes = [];
                if (isset($deposit['returned']) && (float) $deposit['returned'] < 0) {
                    $classes[] = 'negative';
                }
                echo '<td class="'.implode(' ', $classes).'">';
                        if (isset($deposit['returned'])) {
                            echo '<div class="td-spanned">';
                                echo $this->Html->link(
                                    '<i class="fas fa-search ok"></i> ' . __('Details'),
                                    $identity->isManufacturer() ? $this->Slug->getMyDepositDetail($monthAndYear) : $this->Slug->getDepositDetail($manufacturerId, $monthAndYear),
                                    [
                                        'class' => 'btn btn-outline-light',
                                        'title' => __('Show_details'),
                                        'escape' => false
                                    ]
                                );
                                echo '<span class="right">';
                                    echo $this->Number->formatAsCurrency($deposit['returned']);
                                echo '</span>';
                            echo '</div>';
                        }
                    echo '</td>';
            echo '</tr>';
        }

        echo '<tr class="fake-th">';
            echo '<td></td>';
            echo '<td class="right"><b>'.__('Delivered_deposit').'</b></td>';
            echo '<td class="right"><b>'.__('Returned_deposit').'</b></td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td></td>';
            echo '<td class="right"><b>'.$this->Number->formatAsCurrency($sumDepositsDelivered).'</b></td>';
            $classes = ['right'];
            if ($sumDepositsReturned < 0) {
                $classes[] = 'negative';
            }
            echo '<td class="'.implode(' ', $classes).'">';
            if ($sumDepositsReturned != 0) {
                echo '<b>'.$this->Number->formatAsCurrency($sumDepositsReturned).'</b>';
            }
            echo '</td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td colspan="2" class="right"><b>'.__('Your_deposit_balance').'</td>';
            $depositCreditBalance = $sumDepositsDelivered + $sumDepositsReturned;
            $depositCreditBalanceClasses = ['right'];
            if ((float) $depositCreditBalance < 0) {
                $depositCreditBalanceClasses[] = 'negative';
            }
            echo '<td class="'.implode(' ', $depositCreditBalanceClasses).'"><b style="font-size: 16px;">'.$this->Number->formatAsCurrency($depositCreditBalance).'</b></td>';
        echo '</tr>';

        echo '</table>';
    }

?>

<div class="sc"></div>

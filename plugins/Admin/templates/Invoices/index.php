<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        $('input.datepicker').datepicker();" .
        Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');".
        Configure::read('app.jsNamespace') . ".Admin.initCustomerDropdown(" . ($customerId != '' ? $customerId : '0') . ", 0, 1);"
]);
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameTo' => 'dateTo', 'nameFrom' => 'dateFrom']); ?>
        <?php echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'empty' => __d('admin', 'all_members'), 'options' => []]); ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_infos_for_success'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php

echo $this->element('reportNavTabs', [
    'key' => 'invoices',
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
]);

if (empty($invoices)) {
    echo 'no invoices yet';
    return;
}


echo '<table style="margin-top:20px;" class="list no-clone-last-row">';

    echo '<tr class="sort">';
        echo '<th>' . $this->Paginator->sort('Invoice.invoice_number', __d('admin', 'Invoice_number_abbreviation')) . '</th>';
        echo '<th>' . $this->Paginator->sort('Invoice.created', __d('admin', 'Invoice_date')) . '</th>';
        echo '<th>' . $this->Paginator->sort('Invoice.customer.name.', __d('admin', 'Name')) . '</th>';
        echo '<th>' . __d('admin', 'Sum') . '</th>';
        echo '<th>' . $this->Paginator->sort('Invoice.paid_in_cash.', __d('admin', 'Paid_in_cash')) . '</th>';
        echo '<th>' . $this->Paginator->sort('Invoice.filename.', __d('admin', 'Download')) . '</th>';
    echo '</tr>';

    foreach($invoices as $invoice) {

        echo '<tr class="data" data-invoice-id="'.$invoice->id.'">';

            echo '<td>';
                echo $invoice->invoice_number;
            echo '</td>';

            echo '<td>';
                echo $invoice->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
            echo '</td>';

            echo '<td>';
                echo $invoice->customer->name;
            echo '</td>';

            echo '<td>';
                $sumPriceIncl = 0;
                foreach($invoice->invoice_taxes as $invoiceTax) {
                    $sumPriceIncl += $invoiceTax->total_price_tax_incl;
                }
                echo $this->Number->formatAsCurrency($sumPriceIncl);
            echo '</td>';

            echo '<td>';
                echo $invoice->paid_in_cash ? __d('admin', 'yes') : __d('admin', 'no');
            echo '</td>';

            echo '<td>';
                echo $this->Html->link(
                    '<i class="fas fa-arrow-right not-ok"></i>',
                    '/admin/lists/getInvoice?file=' . $invoice->filename,
                    [
                        'class' => 'btn btn-outline-light',
                        'target' => '_blank',
                        'escape' => false
                    ],
                );
            echo '</td>';

        echo '</tr>';

    }

echo '</table>';

?>

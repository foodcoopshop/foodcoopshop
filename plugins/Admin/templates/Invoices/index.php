<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
        $('input.datepicker').datepicker();" .
        Configure::read('app.jsNamespace') . ".Admin.init();".
        Configure::read('app.jsNamespace') . ".Admin.initCustomerDropdown(" . ($customerId != '' ? $customerId : '0') . ", 0, 1);".
        Configure::read('app.jsNamespace') . ".ModalInvoiceForCustomerCancel.init();".
        Configure::read('app.jsNamespace') . ".Admin.initCopyTableContentToClipboard();"
]);

echo $this->element('autoPrintInvoice');

if ($isOverviewMode) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('".__d('admin', 'Website_administration')."', '".__d('admin', 'Financial_reports')."');"
    ]);
}

if ($isOverviewMode && !Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
    $this->element('addScript', [
        'script' =>  Configure::read('app.jsNamespace') . ".Admin.initDownloadInvoicesAsZipFile();"
    ]);
}
?>

<div class="filter-container">
    <?php echo $this->Form->create(null, ['type' => 'get']); ?>
        <h1><?php echo $title_for_layout; ?></h1>
        <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameTo' => 'dateTo', 'nameFrom' => 'dateFrom']); ?>
        <?php
            if ($isOverviewMode) {
                echo $this->Form->control('customerId', ['type' => 'select', 'label' => '', 'placeholder' => __d('admin', 'all_members'), 'options' => []]);
            }
         ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_infos_for_success'))]); ?>
        </div>
    <?php echo $this->Form->end(); ?>
</div>

<?php
if ($isOverviewMode) {
    echo $this->element('navTabs/reportNavTabs', [
        'key' => 'invoices',
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
    ]);
}
echo '<p style="margin-top:15px;"><b>' . __d('admin', 'All_amounts_in_{0}.', [Configure::read('app.currencyName')]) . '</b></p>';

if ($isOverviewMode && !empty($invoices)) {

    if (!empty($taxRates['cash'])) {
        echo '<h4>' . __d('admin', 'Tax_overview_cash') . '</h4>';
        echo $this->element('invoice/taxSumTable', ['taxRates' => $taxRates['cash'], 'taxRatesSums' => $taxRatesSums['cash']]);
    }


    if (!empty($taxRates['cashless'])) {
        echo '<h4>' . __d('admin', 'Tax_overview_cashless') . '</h4>';
        echo $this->element('invoice/taxSumTable', ['taxRates' => $taxRates['cashless'], 'taxRatesSums' => $taxRatesSums['cashless']]);
    }

    if (!empty($taxRates['total'])) {
        echo '<h4>' . __d('admin', 'Tax_overview_total') . '</h4>';
        echo $this->element('invoice/taxSumTable', ['taxRates' => $taxRates['total'], 'taxRatesSums' => $taxRatesSums['total']]);
    }

    echo '<hr />';

}

if ($isOverviewMode) {
    echo '<h4>' . __d('admin', 'Invoices') . '</h4>';
}

if ($isOverviewMode && !Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
    echo $this->Html->link(
        '<i class="fas fa-fw fa-download"></i>',
        'javascript:void(0)',
        [
            'class' => 'btn btn-outline-light btn-download-invoices-as-zip-file',
            'title' => __d('admin', 'Download_invoices'),
            'style' => 'margin-right:3px;float:left;margin-bottom:3px;',
            'escape' => false,
        ]
    );
}
echo $this->Html->link(
    '<i class="far fa-fw fa-clone"></i>',
    'javascript:void(0)',
    [
        'class' => 'btn btn-outline-light btn-clipboard-table',
        'title' => __d('admin', 'Copy_to_clipboard'),
        'style' => ';clear:both;margin-right:3px;float:left;',
        'escape' => false,
    ]
);

echo '<table class="list invoices-table no-clone-last-row">';
$this->Paginator->setPaginated($invoices);
    echo '<tr class="sort">';
        echo '<th>' . $this->Paginator->sort('Invoices.invoice_number', __d('admin', 'Invoice_number_abbreviation')) . '</th>';
        echo '<th>' . $this->Paginator->sort('Invoices.created', __d('admin', 'Invoice_date')) . '</th>';
        echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Name')) . '</th>';
        echo '<th style="text-align:right;">' . __d('admin', 'Net') . '</th>';
        echo '<th style="text-align:right;">' . __d('admin', 'VAT') . '</th>';
        echo '<th style="text-align:right;">' . __d('admin', 'Gross') . '</th>';
        echo '<th>' . $this->Paginator->sort('Invoices.paid_in_cash', __d('admin', 'Paid_in_cash')) . '</th>';
        echo '<th>' . $this->Paginator->sort('Invoices.email_status', __d('admin', 'Email_sent')) . '</th>';
        if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
            echo '<th>' . __d('admin', 'Receipt') . '</th>';
        }
        echo '<th>' . __d('admin', 'Invoice') . '</th>';
        echo '<th>' . __d('admin', 'Cancellation') . '</th>';
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
                echo $this->Html->getNameRespectingIsDeleted($invoice->customer);
            echo '</td>';

            echo '<td style="text-align:right;">';
                echo $this->Number->formatAsDecimal($invoice->sum_price_excl);
            echo '</td>';

            echo '<td style="text-align:right;">';
                echo $this->Number->formatAsDecimal($invoice->sum_tax);
            echo '</td>';

            echo '<td style="text-align:right;">';
                echo $this->Number->formatAsDecimal($invoice->sum_price_incl);
            echo '</td>';

            echo '<td>';
                echo $invoice->paid_in_cash ? __d('admin', 'yes') : __d('admin', 'no');
            echo '</td>';

            echo '<td style="text-align:center;">';
                if (is_null($invoice->email_status)) {
                    echo '<i class="fa fa-times not-ok"></i>';
                } else {
                    try {
                        $dateNTimeObject = DateTime::createFromFormat(Configure::read('DateFormat.DatabaseWithTimeAlt'), $invoice->email_status);
                        $emailStatusString = $dateNTimeObject->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort2'));
                    } catch(InvalidArgumentException $e) {
                        $emailStatusString = $invoice->email_status;
                    }
                    echo $emailStatusString;
                }
            echo '</td>';

            // hello cash has no filename set
            if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
                echo '<td style="text-align:center;">';
                if ($invoice->filename == '') {
                    $receiptLink = $this->Slug->getHelloCashReceipt($invoice->id);
                    if (!empty($invoice->cancelled_invoice)) {
                        $receiptLink = $this->Slug->getHelloCashReceipt($invoice->cancelled_invoice->id, true);
                    }
                    echo $this->Html->link(
                        '<i class="fas fa-arrow-right ok"></i>',
                        $receiptLink,
                        [
                            'class' => 'btn btn-outline-light',
                            'target' => '_blank',
                            'escape' => false,
                        ],
                    );
                }
                echo '</td>';
            }

            // hello cash has no filename set
            if ($invoice->filename == '') {
                $invoiceDownloadLink = $this->Slug->getHelloCashInvoice($invoice->id);
                if (!empty($invoice->cancelled_invoice)) {
                    $invoiceDownloadLink = $this->Slug->getHelloCashInvoice($invoice->cancelled_invoice->id, true);
                }
            } else {
                $invoiceDownloadLink = $this->Slug->getInvoiceDownloadRoute($invoice->filename);
            }

            echo '<td style="text-align:center;">';
                echo $this->Html->link(
                    '<i class="fas fa-arrow-right ok"></i>',
                    $invoiceDownloadLink,
                    [
                        'class' => 'btn btn-outline-light',
                        'target' => '_blank',
                        'escape' => false,
                    ],
                );
            echo '</td>';

            echo '<td style="text-align:center;">';

                if (!empty($invoice->cancellation_invoice)) {
                    echo $invoice->cancellation_invoice->invoice_number;
                } else if (!empty($invoice->cancelled_invoice)) {
                    echo $invoice->cancelled_invoice->invoice_number;
                } else {
                    if ($isOverviewMode) {
                        echo $this->Html->link(
                            '<i class="fas fa-times-circle neutral"></i>',
                            'javascript:void(0);',
                            [
                                'class' => 'btn btn-outline-light invoice-for-customer-cancel-button',
                                'escape' => false,
                            ],
                        );
                    }
                }
            echo '</td>';

        echo '</tr>';

    }
    echo '<tr style="font-weight:bold;">';

        echo '<td colspan="3" style="text-align:right;">';
            echo __d('admin', 'Total_sum');
        echo '</td>';

        echo '<td style="text-align:right;">';
            echo $this->Number->formatAsDecimal($invoiceSums['total_sum_price_excl']);
        echo '</td>';

        echo '<td style="text-align:right;">';
            echo $this->Number->formatAsDecimal($invoiceSums['total_sum_tax']);
        echo '</td>';

        echo '<td style="text-align:right;">';
            echo $this->Number->formatAsDecimal($invoiceSums['total_sum_price_incl']);
        echo '</td>';


        $colspan = 4;
        if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
            $colspan++;
        }

        echo '<td colspan="'.$colspan.'">';
        echo '</td>';

    echo '</tr>';

echo '</table>';

?>

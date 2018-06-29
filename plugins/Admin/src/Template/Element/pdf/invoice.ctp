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

use App\Lib\Pdf\AppTcpdf;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Filesystem\Folder;

$pdf = new AppTcpdf();
$pdf->SetLeftMargin(12);
$pdf->AddPage();

$pdf->SetTitle(__d('admin', 'Invoice_for_the_order_period_{0}_to_{1}', [$dateFrom, $dateTo]));

$html = '<table border="1" cellspacing="0" cellpadding="7"><tr>';
$html .= '<td width="200">';
$html .= '<p><b>'.__d('admin', 'conveyed_for').'</b></p>';
$manufacturerAddress = '<p>' . $results_product[0]['ManufacturerName'] . '<br />';
$manufacturerAddress .= $results_product[0]['ManufacturerFirstname'] . ' ' . $results_product[0]['ManufacturerLastname'] . '<br />';
$manufacturerAddress .= $results_product[0]['ManufacturerAddress1'] . '<br />';
$manufacturerAddress .= $results_product[0]['ManufacturerPostcode'] . ' ' . $results_product[0]['ManufacturerCity'];
$html .= $manufacturerAddress . '</p>';
$html .= '</td>';

// invoice number is only set if invoice is sent
if (! isset($newInvoiceNumber)) {
    $newInvoiceNumber = 'xxx';
}

$html .= '<td width="330">';
$html .= '<h2>'.__d('admin', 'Invoice_number_abbreviation').': ' . $newInvoiceNumber . '</h2>';
$html .= '<h3>'.__d('admin', 'Orders_from').' ' . Configure::read('app.timeHelper')->getLastMonthNameAndYear() . '</h3>';
$html .= '<h3>'.__d('admin', 'Invoice_date').': ' . date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt')) . '</h3>';
$html .= '</td>';
$html .= '</tr></table>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->infoTextForFooter = $results_product[0]['ManufacturerName'];
if ($results_product[0]['ManufacturerUidNumber'] != '') {
    $pdf->infoTextForFooter .= ', ' . __('VAT_number') . ': ' . $results_product[0]['ManufacturerUidNumber'];
}
$pdf->infoTextForFooter .= ', '.__d('admin', 'Invoice_number_abbreviation').' ' . $newInvoiceNumber;

// Produktauflistung Start
$widths = [
    33,
    332,
    55,
    55,
    55
];
$headers = [
    __d('admin', 'Amount'),
    __d('admin', 'Product'),
    __d('admin', 'Price_excl.'),
    __d('admin', 'VAT'),
    __d('admin', 'Price_incl.')
];
$pdf->renderDetailedOrderList($results_product, $widths, $headers, 'product', true);
$pdf->addLastSumRow(
    $headers,
    $this->MyNumber->formatAsDecimal($sumPriceExcl),
    $this->MyNumber->formatAsDecimal($sumTax),
    $this->MyNumber->formatAsDecimal($sumPriceIncl)
);
$pdf->renderTable();
// Produktauflistung End

if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && $variableMemberFee > 0 && $sumTimebasedCurrencyPriceIncl == 0) {

    $m = TableRegistry::getTableLocator()->get('Manufacturers');
    $compensatedPrice = $m->getVariableMemberFeeAsFloat($sumPriceIncl, $variableMemberFee);
    $newSumPriceIncl = $m->decreasePriceWithVariableMemberFee($sumPriceIncl, $variableMemberFee);
    $firstColumnWidth = 365;
    $secondColumnWidth = 165;

    $html = '<table border="1" cellspacing="0" cellpadding="1">';

    $html .= '<tr>';
    $html .= '<td width="' . $firstColumnWidth . '">';
    $html .= '<h3> - ' . $variableMemberFee . '% '.__d('admin', 'variable_member_fee').'</h3>';
    $html .= '</td>';

    $html .= '<td align="right" width="' . $secondColumnWidth . '">';
    $html .= '<h3> - ' . $this->MyNumber->formatAsDecimal($compensatedPrice) . '</h3>';
    $html .= '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="' . $firstColumnWidth . '">';
    $html .= '<h3>'.__d('admin', 'New_total_sum').'</h3>';
    $html .= '</td>';

    $html .= '<td align="right" width="' . $secondColumnWidth . '">';
    $html .= '<h3>' . $this->MyNumber->formatAsDecimal($newSumPriceIncl) . '</h3>';
    $html .= '</td>';
    $html .= '</tr>';

    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $html = '<p>'.__d('admin', 'The_total_sum_below_right_(minus_{0}_%_variable_member_fee)_will_be_transfered_to_your_bank_account_as_soon_as_possible.', [$variableMemberFee]).'</p>';
    $pdf->Ln(3);
    $pdf->writeHTML($html, true, false, true, false, '');
} else {
    $html = '<p>'.__d('admin', 'The_total_sum_to_the_right_(price_incl)_will_be_transfered_to_your_bank_account_as_soon_as_possible.').'</p>';
    $pdf->Ln(3);
    $pdf->writeHTML($html, true, false, true, false, '');
}

if ($sumTimebasedCurrencyPriceIncl > 0) {

    $sumPriceForTimebasedCurrency = $sumPriceIncl;
    if (isset($newSumPriceIncl)) {
        $sumPriceForTimebasedCurrency = $newSumPriceIncl;
    }
    $sumPriceForTimebasedCurrency -= $sumTimebasedCurrencyPriceIncl;

    $firstColumnWidth = 200;
    $secondColumnWidth = 60;

    $html = '<table border="0" cellspacing="0" cellpadding="1">';

        $html .= '<tr>';
            $html .= '<td width="' . $firstColumnWidth . '">';
                $html .= __d('admin', 'Paid_by_members_in_{0}', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]) . ':';
            $html .= '</td>';
            $html .= '<td align="right" width="' . $secondColumnWidth . '">';
                $html .= '<b>' .  $this->MyNumber->formatAsCurrency($sumTimebasedCurrencyPriceIncl) . '</b>';
            $html .= '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
            $html .= '<td width="' . $firstColumnWidth . '">';
                $html .= __d('admin', 'Paid_by_members_in') . ' ' . Configure::read('app.currencyName') . ':';
            $html .= '</td>';
            $html .= '<td align="right" width="' . $secondColumnWidth . '">';
                $html .= '<b>' .  $this->MyNumber->formatAsCurrency($sumPriceForTimebasedCurrency) . '</b>';
            $html .= '</td>';
        $html .= '</tr>';

    if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && $variableMemberFee > 0) {
        $m = TableRegistry::getTableLocator()->get('Manufacturers');
        $compensatedPrice = $m->getVariableMemberFeeAsFloat($sumPriceForTimebasedCurrency, $variableMemberFee);
        $sumPriceForTimebasedCurrencyDecreasedWithVariableMemberFee = $m->decreasePriceWithVariableMemberFee($sumPriceForTimebasedCurrency, $variableMemberFee);

        $html .= '<tr>';
            $html .= '<td width="' . $firstColumnWidth . '">';
                $html .= __d('admin', 'Kept_variable_member_fee') . ':';
            $html .= '</td>';
            $html .= '<td align="right" width="' . $secondColumnWidth . '">';
                $html .= '<b>'.$this->MyNumber->formatAsCurrency($compensatedPrice).'</b>';
            $html .= '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
            $html .= '<td width="' . $firstColumnWidth . '">';
                $html .= __d('admin', 'Amount_that_will_be_transferred_to_your_bank_account') . ':';
            $html .= '</td>';
            $html .= '<td align="right" width="' . $secondColumnWidth . '">';
                $html .= '<b>'.$this->MyNumber->formatAsCurrency($sumPriceForTimebasedCurrencyDecreasedWithVariableMemberFee).'</b>';
            $html .= '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';

    $pdf->Ln(3);
    $pdf->writeHTML($html, true, false, true, false, '');
}

if ($results_product[0]['ManufacturerAdditionalTextForInvoice'] != '') {
    $html = '<p>' . nl2br($results_product[0]['ManufacturerAdditionalTextForInvoice']) . '</p>';
    $pdf->Ln(3);
    $pdf->writeHTML($html, true, false, true, false, '');
}

$pdf->Ln(3);
$html = '<p>'.__d('admin', 'Thank_you_very_much_for_delivering_your_products_to_us!').'</p>';
$pdf->writeHTML($html, true, false, true, false, '');

// Detailübersicht Start
$pdf->AddPage();
$html = '<h2>'.__d('admin', 'Detail_view').'</h2>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(5);

$widths = [
    33,
    178,
    45,
    45,
    45,
    50,
    134
];
$headers = [
    __d('admin', 'Amount'),
    __d('admin', 'Product'),
    __d('admin', 'Price_excl.'),
    __d('admin', 'VAT'),
    __d('admin', 'Price_incl.'),
    __d('admin', 'Date'),
    __d('admin', 'Member')
];
$pdf->renderDetailedOrderList($results_customer, $widths, $headers, 'customer');
$pdf->renderTable();
// Detailübersicht End

$pdf->lastPage();

$filename = $this->MyHtml->getInvoiceLink($results_product[0]['ManufacturerName'], $results_product[0]['ManufacturerId'], date('Y-m-d'), $newInvoiceNumber);

if ($saveParam == 'F') {
    // pdf saved on server
    if (file_exists($filename)) {
        unlink($filename);
    }
    // assure that folder structure exists
    $dir = new Folder();
    $path = dirname($filename);
    $dir->create($path);
    $dir->chmod($path, 0755);
} else {
    // pdf is generated on the fly and NOT saved on server
    // set custom filename
    $filename = explode(DS, $filename);
    $filename = end($filename);
    $filename = substr($filename, 11);
    $filename = $this->request->getQuery('dateFrom'). '-' . $this->request->getQuery('dateTo') . '-' . $filename;
}

echo $pdf->Output($filename, $saveParam);

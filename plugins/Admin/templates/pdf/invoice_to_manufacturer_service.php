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
use Cake\ORM\TableRegistry;

$pdf->setTextHelper($this->Text);
$pdf->SetLeftMargin(12);
$pdf->AddPage();

$html = '<table border="1" cellspacing="0" cellpadding="7"><tr>';
$html .= '<td width="200">';
$html .= '<p><b>'.__d('admin', 'conveyed_for').'</b></p>';
$manufacturerAddress = '<p>' . $productResults[0]['ManufacturerName'] . '<br />';
$manufacturerAddress .= $productResults[0]['ManufacturerFirstname'] . ' ' . $productResults[0]['ManufacturerLastname'] . '<br />';
$manufacturerAddress .= $productResults[0]['ManufacturerAddress1'] . '<br />';
$manufacturerAddress .= $productResults[0]['ManufacturerPostcode'] . ' ' . $productResults[0]['ManufacturerCity'];
$html .= $manufacturerAddress . '</p>';
$html .= '</td>';

$html .= '<td width="330">';
$html .= '<h2>'.__d('admin', 'Invoice_number_abbreviation').': ' . $newInvoiceNumber . '</h2>';
$html .= '<h3>'.__d('admin', 'Orders_from').' ' . $period . '</h3>';
$html .= '<h3>'.__d('admin', 'Invoice_date').': ' . $invoiceDate . '</h3>';
$html .= '</td>';
$html .= '</tr></table>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->infoTextForFooter = $productResults[0]['ManufacturerName'];
if ($productResults[0]['ManufacturerUidNumber'] != '') {
    $pdf->infoTextForFooter .= ', ' . __d('admin', 'VAT_number') . ': ' . $productResults[0]['ManufacturerUidNumber'];
}
$pdf->infoTextForFooter .= ', '.__d('admin', 'Invoice_number_abbreviation').' ' . $newInvoiceNumber;

// product list start
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
$pdf->renderDetailedOrderList($productResults, $widths, $headers, 'product', true);
$pdf->addLastSumRow(
    $headers,
    $this->MyNumber->formatAsDecimal($sumAmount, 0),
    $this->MyNumber->formatAsDecimal($sumPriceExcl),
    $this->MyNumber->formatAsDecimal($sumTax),
    $this->MyNumber->formatAsDecimal($sumPriceIncl),
);
$pdf->renderTable();
// product list end

if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && $variableMemberFee > 0) {

    $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
    $compensatedPrice = $manufacturersTable->getVariableMemberFeeAsFloat($sumPriceIncl, $variableMemberFee);
    $newSumPriceIncl = $manufacturersTable->decreasePriceWithVariableMemberFee($sumPriceIncl, $variableMemberFee);
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

$taxRates = $pdf->prepareTaxSumData($productResults);
$pdf->renderTaxSumTable($taxRates);

if ($productResults[0]['ManufacturerAdditionalTextForInvoice'] != '') {
    $html = '<p>' . nl2br($productResults[0]['ManufacturerAdditionalTextForInvoice']) . '</p>';
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
    152,
    45,
    45,
    45,
    48,
    48,
    114
];
$headers = [
    __d('admin', 'Amount'),
    __d('admin', 'Product'),
    __d('admin', 'Price_excl.'),
    __d('admin', 'VAT'),
    __d('admin', 'Price_incl.'),
    __d('admin', 'Order_day'),
    __d('admin', 'Delivery_day'),
    __d('admin', 'Member')
];
$pdf->renderDetailedOrderList($customerResults, $widths, $headers, 'customer');
$pdf->renderTable();
// Detailübersicht End

$pdf->lastPage();

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

$pdf->setTextHelper($this->Text);
$pdf->infoTextForFooter = __d('admin', 'Invoice_number_abbreviation').': ' . $newInvoiceNumber;
$pdf->AddPage();

$html = '<h2>';
if ($result->cancelledInvoice) {
    $html .= __d('admin', 'Cancellation_invoice');
} else {
    $html .=__d('admin', 'Invoice');
}
$html .= '</h2>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(7);

$html = '<table border="0" cellspacing="0" cellpadding="0"><tr>';
$html .= '<td width="272">';
    $html .= '<p>';
        $html .= $result->name . '<br />';
        $html .= strip_tags($this->Html->getCustomerAddress($result), '<br>') . '<br />';
    $html .= '</p>';
$html .= '</td>';

$html .= '<td width="230" align="right">';
    $html .= '<p style="font-weight:bold;">'.__d('admin', 'Invoice_number_abbreviation').': ' . $newInvoiceNumber;
    if ($result->cancelledInvoice) {
        $html .= '<br />' . __d('admin', 'Cancellation_invoice') . ' ' .  __d('admin', 'for') .': ' . $result->cancelledInvoice->invoice_number . '<br />';
    }
    $html .= '<br />' . __d('admin', 'Invoice_date').': ' . $invoiceDate . '</p>';
    $html .= '</td>';
$html .= '</tr></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->prepareTableHeader();
$pdf->prepareTableData($result, $sumPriceExcl, $sumPriceIncl, $sumTax);
$pdf->renderTable();

$pdf->renderTaxSumTable($result->tax_rates);

if (!$result->cancelledInvoice) {
    $pdf->Ln(3);
    $html = '<p>'.__d('admin', '{0}_thanks_you_for_your_purchase!', [Configure::read('appDb.FCS_APP_NAME')]).'</p>';
    $pdf->writeHTML($html, true, false, true, false, '');
}

if (!$result->cancelledInvoice && $paidInCash) {
    $pdf->Ln(3);
    $html = '<p>'.__d('admin', 'Paid_in_cash_on_{0}.', [$invoiceDate]).'</p>';
    $pdf->writeHTML($html, true, false, true, false, '');
}

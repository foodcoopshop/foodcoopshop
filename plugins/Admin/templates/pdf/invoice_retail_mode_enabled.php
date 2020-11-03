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
$pdf->SetLeftMargin(12);
$pdf->AddPage();

$html = '<h2>'.__d('admin', 'Invoice').'</h2>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(7);

$html = '<table border="0" cellspacing="0" cellpadding="0"><tr>';
$html .= '<td width="300">';
    $html .= '<p>';
        $html .= $result->name . '<br />';
        $html .= strip_tags($this->Html->getCustomerAddress($result), '<br>') . '<br />';
    $html .= '</p>';
$html .= '</td>';

$html .= '<td width="230" align="right">';
    $html .= '<p style="font-weight:bold;">'.__d('admin', 'Invoice_number_abbreviation').': ' . $newInvoiceNumber . '<br />';
    $html .= __d('admin', 'Invoice_date').': ' . $invoiceDate . '</p>';
    $html .= '</td>';
$html .= '</tr></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->prepareTableHeader();
$pdf->prepareTableData($result, $sumPriceExcl, $sumPriceIncl, $sumTax);
$pdf->renderTable();

$pdf->Ln(3);
$html = '<p>'.__d('admin', '{0}_thanks_you_for_your_purchase!', [Configure::read('appDb.FCS_APP_NAME')]).'</p>';
$pdf->writeHTML($html, true, false, true, false, '');

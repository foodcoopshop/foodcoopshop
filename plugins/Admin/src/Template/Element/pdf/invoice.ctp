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

$pdf->SetTitle('Rechnung für den Bestell-Zeitraum ' . $from . ' - ' . $to);

$html = '<table border="1" cellspacing="0" cellpadding="7"><tr>';
$html .= '<td width="200">';
$html .= '<p><b>vermittelt für</b></p>';
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
$html .= '<h2>Rechnung Nr.: ' . $newInvoiceNumber . '</h2>';
$html .= '<h3>Bestellungen vom ' . Configure::read('app.timeHelper')->getLastMonthNameAndYear() . '</h3>';
$html .= '<h3>Rechnungsdatum: ' . date('d.m.Y') . '</h3>';
$html .= '</td>';
$html .= '</tr></table>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->infoTextForFooter = $results_product[0]['ManufacturerName'];
if ($results_product[0]['ManufacturerUidNumber'] != '') {
    $pdf->infoTextForFooter .= ', UID-Nummer: ' . $results_product[0]['ManufacturerUidNumber'];
}
$pdf->infoTextForFooter .= ', Rechnung Nr. ' . $newInvoiceNumber;

// Produktauflistung Start
$widths = [
    30,
    335,
    55,
    55,
    55
];
$headers = [
    'Anzahl',
    'Produkt',
    'Preis exkl.',
    'USt.',
    'Preis inkl.'
];
$pdf->renderDetailedOrderList($results_product, $widths, $headers, 'product', true);
$pdf->addLastSumRow(
    $headers,
    $this->Html->formatAsDecimal($sumPriceExcl),
    $this->Html->formatAsDecimal($sumTax),
    $this->Html->formatAsDecimal($sumPriceIncl)
);
$pdf->renderTable();
// Produktauflistung End

if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && $variableMemberFee > 0) {
    // TODO put that in controller where it belongs to :-)
    $m = TableRegistry::get('Manufacturers');
    $compensatedPrice = $m->getVariableMemberFeeAsFloat($sumPriceIncl, $variableMemberFee);
    $newSumPriceIncl = $m->decreasePriceWithVariableMemberFee($sumPriceIncl, $variableMemberFee);
    $firstColumnWidth = 365;
    $secondColumnWidth = 165;

    $html = '<table border="1" cellspacing="0" cellpadding="1">';

    $html .= '<tr>';
    $html .= '<td width="' . $firstColumnWidth . '">';
    $html .= '<h3> - ' . $variableMemberFee . '% variabler Mitgliedsbeitrag</h3>';
    $html .= '</td>';

    $html .= '<td align="right" width="' . $secondColumnWidth . '">';
    $html .= '<h3> - ' . $this->Html->formatAsDecimal($compensatedPrice) . '</h3>';
    $html .= '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td width="' . $firstColumnWidth . '">';
    $html .= '<h3>Neue Gesamtsumme</h3>';
    $html .= '</td>';

    $html .= '<td align="right" width="' . $secondColumnWidth . '">';
    $html .= '<h3>' . $this->Html->formatAsDecimal($newSumPriceIncl) . '</h3>';
    $html .= '</td>';
    $html .= '</tr>';

    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $html = '<p>Die neue Gesamtsumme rechts unten (abzüglich ' . $variableMemberFee . '% variabler Mitgliedsbeitrag) wird so bald wie möglich auf dein Konto überwiesen.</p>';
    $pdf->Ln(3);
    $pdf->writeHTML($html, true, false, true, false, '');
} else {
    $html = '<p>Die Gesamtsumme ganz rechts (Preis inkl.) wird so bald wie möglich auf dein Konto überwiesen.</p>';
    $pdf->Ln(3);
    $pdf->writeHTML($html, true, false, true, false, '');
}

if ($sumTimebasedCurrencyPriceIncl > 0) {
    $html = '<p>Gesamtbetrag: <b>' . $this->Html->formatAsEuro($sumPriceIncl). '</b>';
    $html .= '<br />Davon zahlbar in ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME') . ': <b>' .  $this->Html->formatAsEuro($sumTimebasedCurrencyPriceIncl) . '</b></p>';
    $pdf->Ln(3);
    $pdf->writeHTML($html, true, false, true, false, '');
}

if ($results_product[0]['ManufacturerAdditionalTextForInvoice'] != '') {
    $html = '<p>' . nl2br($results_product[0]['ManufacturerAdditionalTextForInvoice']) . '</p>';
    $pdf->Ln(3);
    $pdf->writeHTML($html, true, false, true, false, '');
}

$pdf->Ln(3);
$html = '<p>Vielen Dank, dass du uns belieferst!</p>';
$pdf->writeHTML($html, true, false, true, false, '');

// Detailübersicht Start
$pdf->AddPage();
$html = '<h2>Detailübersicht</h2>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(5);

$widths = [
    30,
    181,
    45,
    45,
    45,
    50,
    134
];
$headers = [
    'Anzahl',
    'Produkt',
    'Preis exkl.',
    'USt.',
    'Preis inkl.',
    'Datum',
    'Mitglied'
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
    $filename = $this->request->getParam('pass')[1] . '-' . $this->request->getParam('pass')[2] . '-' . $filename;
}

echo $pdf->Output($filename, $saveParam);

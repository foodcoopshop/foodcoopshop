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

App::uses('AppTcpdf', 'Lib/Pdf');
$pdf = new AppTcpdf();
$pdf->SetLeftMargin(12);
$pdf->AddPage();

$pdf->SetTitle('Rechnung für den Bestell-Zeitraum ' . $from . ' - ' . $to);

$html = '<table border="1" cellspacing="0" cellpadding="7"><tr>';
$html .= '<td width="200">';
$html .= '<p><b>vermittelt für</b></p>';
$manufacturerAddress = '<p>' . $results_product[0]['m']['Hersteller'] . '<br />';
$manufacturerAddress .= $results_product[0]['ma']['firstname'] . ' ' . $results_product[0]['ma']['lastname'] . '<br />';
$manufacturerAddress .= $results_product[0]['ma']['address1'] . '<br />';
$manufacturerAddress .= $results_product[0]['ma']['postcode'] . ' ' . $results_product[0]['ma']['city'];
$html .= $manufacturerAddress . '</p>';
$html .= '</td>';

// invoice number is only set if invoice is sent
if (! isset($newInvoiceNumber)) {
    $newInvoiceNumber = 'xxx';
}

$html .= '<td width="330">';
$html .= '<h2>Rechnung Nr.: ' . $newInvoiceNumber . '</h2>';
$html .= '<h3>Bestellungen vom ' . Configure::read('timeHelper')->getLastMonthNameAndYear() . '</h3>';
$html .= '<h3>Rechnungsdatum: ' . date('d.m.Y') . '</h3>';
$html .= '</td>';
$html .= '</tr></table>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->infoTextForFooter = $results_product[0]['m']['Hersteller'];
if ($results_product[0]['m']['UID'] != '') {
    $pdf->infoTextForFooter .= ', UID-Nummer: ' . $results_product[0]['m']['UID'];
}
$pdf->infoTextForFooter .= ', Rechnung Nr. ' . $newInvoiceNumber;

// Produktauflistung Start
$widths = array(
    30,
    360,
    45,
    50,
    45
);
$headers = array(
    'Anzahl',
    'Produkt',
    'Preis exkl.',
    'MWSt.',
    'Preis inkl.'
);
$pdf->renderDetailedOrderList($results_product, $widths, $headers, 'product', true);
$pdf->addLastSumRow($headers, $sumAmount, $sumPriceExcl, $sumTax, $sumPriceIncl);
$pdf->renderTable();
// Produktauflistung End

if (Configure::read('app.db_config_FCS_USE_VARIABLE_MEMBER_FEE') && $variableMemberFee > 0) {
    $sumPriceIncl = str_replace(',', '.', $sumPriceIncl);
    $compensatedPrice = round($sumPriceIncl * $variableMemberFee / 100, 2);
    $newSumPriceIncl = $sumPriceIncl - $compensatedPrice;
    $firstColumnWidth = 390;
    $secondColumnWidth = 140;

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

if ($results_product[0]['m']['Zusatztext'] != '') {
    $html = '<p>' . nl2br($results_product[0]['m']['Zusatztext']) . '</p>';
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

$widths = array(
    30,
    181,
    45,
    45,
    45,
    50,
    134
);
$headers = array(
    'Anzahl',
    'Produkt',
    'Preis exkl.',
    'MWSt.',
    'Preis inkl.',
    'Datum',
    'Mitglied'
);
$pdf->renderDetailedOrderList($results_customer, $widths, $headers, 'customer');
$pdf->renderTable();
// Detailübersicht End

$pdf->lastPage();

$filename = $this->MyHtml->getInvoiceLink($results_product[0]['m']['Hersteller'], $results_product[0]['m']['HerstellerID'], date('Y-m-d'), $newInvoiceNumber);

// if send method is called, prepare chrononlogical folders on server
if ($saveParam == 'F') {
    if (file_exists($filename)) {
        unlink($filename);
    }

    App::uses('Folder', 'Utility');
    $dir = new Folder();
    $path = dirname($filename);
    $dir->create($path);
    $dir->chmod($path, 0755);
}

echo $pdf->Output($filename, $saveParam);

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
$pdf->SetLeftMargin(16);
$pdf->AddPage();

$title = $results[0]['m']['Hersteller'] . ': Bestellliste sortiert nach ' . $groupType_de;

$pdf->infoTextForFooter = $title;
$pdf->SetTitle($title);

$html = '<h2>' . $title;

/**
 * if order lists are sent on wednesday, thursday or friday, eventually changed deliveryDayDelta
 * important if allowManualOrderListSending = true
 */
if (! $bulkOrdersAllowed && Configure::read('app.db_config_FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS') != '') {
    $deliveryDate = strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day');
    $html .= '<br />Liefertermin: ' . $this->MyTime->getWeekdayName(date('N', $deliveryDate)) . ', ' . date('d.m.Y', $deliveryDate);
    $html .= Configure::read('app.db_config_FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS') . '</h2>';
}
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(5);

$widths = array(
    30,
    235,
    45,
    50,
    144
);
$headers = array(
    'Anzahl',
    'Artikelname',
    'Preis',
    'Datum',
    'Mitglied'
);

$pdf->renderDetailedOrderList($results, $widths, $headers, $groupType);
$pdf->addLastSumRow($headers, $sumAmount, $sumPriceExcl, $sumTax, $sumPriceIncl);
$pdf->renderTable();

$pdf->Ln(5);
$html = '<p>Vielen Dank, dass du uns belieferst!</p>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->lastPage();

$filename = $this->MyHtml->getOrderListLink($results[0]['m']['Hersteller'], $results[0]['m']['HerstellerID'], $deliveryDay, $groupType_de);

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

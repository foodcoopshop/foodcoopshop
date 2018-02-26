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
use Cake\Filesystem\Folder;

$pdf = new AppTcpdf();
$pdf->SetLeftMargin(16);
$pdf->AddPage();

$title = $results[0]['ManufacturerName'] . ': Bestellliste sortiert nach ' . $groupType_de;

$pdf->infoTextForFooter = $title;
$pdf->SetTitle($title);

$html = '<h2>' . $title;

/**
 * if order lists are sent on wednesday, thursday or friday, eventually changed deliveryDayDelta
 * important if allowManualOrderListSending = true
 */
if (! $bulkOrdersAllowed && Configure::read('appDb.FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS') != '') {
    $deliveryDate = strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day');
    $html .= '<br />Liefertermin: ' . $this->MyTime->getWeekdayName(date('N', $deliveryDate)) . ', ' . date('d.m.Y', $deliveryDate);
    $html .= Configure::read('appDb.FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS') . '</h2>';
}
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(5);

$widths = [
    30,
    235,
    45,
    50,
    144
];
$headers = [
    'Anzahl',
    'Produkt',
    'Preis',
    'Datum',
    'Mitglied'
];

$pdf->renderDetailedOrderList($results, $widths, $headers, $groupType);
$pdf->addLastSumRow(
    $headers,
    $this->Html->formatAsDecimal($sumPriceExcl),
    $this->Html->formatAsDecimal($sumTax),
    $this->Html->formatAsDecimal($sumPriceIncl)
);
$pdf->renderTable();

$pdf->Ln(5);
$html = '<p>Vielen Dank, dass du uns belieferst!</p>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->lastPage();

$filename = $this->MyHtml->getOrderListLink($results[0]['ManufacturerName'], $results[0]['ManufacturerId'], $deliveryDay, $groupType_de);

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

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

$title = $results[0]['ManufacturerName'] . ': '.__d('admin', 'Order_list_grouped_by').' ' . $groupTypeLabel;

$pdf->infoTextForFooter = $title;
$pdf->SetTitle($title);

$html = '<h2>' . $title;

/**
 * if order lists are sent on wednesday, thursday or friday, eventually changed deliveryDayDelta
 * important if allowManualOrderListSending = true
 */
if (! $bulkOrdersAllowed && Configure::read('appDb.FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS') != '') {
    $deliveryDate = strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day');
    $html .= '<br />'.__d('admin', 'Delivery_date').': ' . $this->MyTime->getWeekdayName(date('N', $deliveryDate)) . ', ' . date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), $deliveryDate);
    $html .= Configure::read('appDb.FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS') . '</h2>';
}
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(5);

$widths = [
    33,
    232,
    45,
    50,
    144
];
$headers = [
    __d('admin', 'Amount'),
    __d('admin', 'Product'),
    __d('admin', 'Price'),
    __d('admin', 'Date'),
    __d('admin', 'Member')
];

$pdf->renderDetailedOrderList($results, $widths, $headers, $groupType, false);
$pdf->addLastSumRow(
    $headers,
    $this->MyNumber->formatAsDecimal($sumPriceExcl),
    $this->MyNumber->formatAsDecimal($sumTax),
    $this->MyNumber->formatAsDecimal($sumPriceIncl)
);
$pdf->renderTable();

$pdf->Ln(5);
$html = '<p>'.__d('admin', 'Thank_you_very_much_for_delivering_your_products_to_us!').'</p>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->lastPage();

$filename = $this->MyHtml->getOrderListLink($results[0]['ManufacturerName'], $results[0]['ManufacturerId'], $deliveryDay, $groupTypeLabel);

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

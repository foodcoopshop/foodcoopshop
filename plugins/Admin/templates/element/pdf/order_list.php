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

$pdf->setTextHelper($this->Text);
$pdf->SetLeftMargin(16);
$pdf->AddPage();

$title = $results[0]['ManufacturerName'] . ': '.__d('admin', 'Order_list_grouped_by').' ' . $groupTypeLabel;
$deliveryDateString = __d('admin', 'Delivery_day').': ' . $this->MyTime->getWeekdayName(date('N', strtotime($results[0]['OrderDetailPickupDay'])));
$deliveryDateString .= ', ' . date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime($results[0]['OrderDetailPickupDay']));

$pdf->infoTextForFooter = $results[0]['ManufacturerName'] . ', ' . $deliveryDateString;
$pdf->SetTitle($title);

$html = '<h2>' . $title . '</h2>';
$html .= '<p><b>' . $deliveryDateString . '</b>';
if (Configure::read('appDb.FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS') != '') {
    $html .= Configure::read('appDb.FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS');
}
$html .= '</p>';
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
    __d('admin', 'Order_day'),
    __d('admin', 'Member')
];

if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
    $widths[1] += $widths[2];
    array_splice($widths, 2, 1);
    array_splice($headers, 2, 1);
}
$pdf->renderDetailedOrderList($results, $widths, $headers, $groupType, false);

$pdf->addLastSumRow(
    $headers,
    $this->MyNumber->formatAsDecimal($sumAmount, 0),
    null,
    null,
    (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') ? null : $this->MyNumber->formatAsDecimal($sumPriceIncl)),
);

$pdf->renderTable();

$pdf->Ln(5);
$html = '<p>'.__d('admin', 'Thank_you_very_much_for_delivering_your_products_to_us!').'</p>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->lastPage();

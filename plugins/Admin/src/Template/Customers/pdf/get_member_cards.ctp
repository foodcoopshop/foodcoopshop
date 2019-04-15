<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Lib\Pdf\BarCodeTcpdf;
use Cake\Core\Configure;

$pdf = new BarCodeTcpdf();
$pdf->SetLeftMargin(16);
$pdf->AddPage();

$i = 0;
$pdf->table = '<table border="1" cellspacing="0" cellpadding="8">';
foreach($customers as $customer) {
    $pairRecord = $i % 2 == 0;
    if ($pairRecord) {
        $pdf->table .= '<tr>';
    }
    $pdf->table .= '<td style="width:240px;height:154px;">'; // roughly 85,60mm x 53,98mm
    $pdf->table .= __d('admin', 'Customer_ID') . ': ' . $customer->id_customer . '<br />';
    $pdf->table .= __d('admin', 'Name') . ': <b>' . $customer->name . '</b><br />';
    $pdf->table .= __d('admin', 'Register_date') . ': ' . $customer->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . ' ';
    $pdf->table .= '<br /><br />';
    $barcodeObject = new TCPDFBarcode($customer->id_customer, 'EAN8');
    //https://stackoverflow.com/a/54520065/2100184
    $imgBase64Encoded = base64_encode($barcodeObject->getBarcodePngData(2, 30));
    $pdf->table .= '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $imgBase64Encoded) . '">';
    $pdf->table .= '</td>';
    if (!$pairRecord) {
        $pdf->table .= '</tr>';
    }
    $i++;
}
$pdf->table .= '</table>';
$pdf->writeHTML($pdf->table, true, false, true, false, '');

// echo $pdf->outputHtml();
echo $pdf->Output();
exit;

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
$pdf->table = '<table border="1" cellspacing="0" cellpadding="5">';
foreach($customers as $customer) {
    $pairRecord = $i % 2 == 0;
    if ($pairRecord) {
        $pdf->table .= '<tr>';
    }
    $pdf->table .= '<td style="width:239px;height:154px;">'; // roughly 85,60mm x 53,98mm
    $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0">';
        $pdf->table .= '<tr>';
        $pdf->table .= '<td style="width:80px;">';
            $pdf->table .= '<img src="' . $pdf->logoPath .'">';
            $pdf->table .= '<br /><span style="font-size:8px;">'.Configure::read('appDb.FCS_APP_NAME') . '</span>';
        $pdf->table .= '</td>';
        $pdf->table .= '<td style="width:5px;"></td>'; //spacer
        $pdf->table .= '<td style="width:144px;">';
        $pdf->table .= __d('admin', 'Customer_ID') . ': ' . $customer->id_customer . '<br />';
        $pdf->table .= '<b>' . $customer->name . '</b><br />';
        $pdf->table .= __d('admin', 'Register_date') . ': ' . $customer->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . ' ';
        $pdf->table .= '</td>';
        $pdf->table .= '</tr>';
        $pdf->table .= '<tr>';
        $pdf->table .= '<td colspan="2">';
        $barcodeObject = new TCPDFBarcode($customer->id_customer, 'EAN8');
        //https://stackoverflow.com/a/54520065/2100184
        $imgBase64Encoded = base64_encode($barcodeObject->getBarcodePngData(1.5, 50));
        // move barcode to bottom
        $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0"><tr><td style="height:40px;"></td></tr></table>';
        $pdf->table .= '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $imgBase64Encoded) . '">';
        $pdf->table .= '</td>';
        $pdf->table .= '<td style="width:5px;"></td>'; //spacer
        $pdf->table .= '<td style="width:133px;" align="right">';
        $customerImage = Configure::read('app.customerImagesDir') . DS . $customer->id_customer . '-xxl.jpg';
        if (file_exists($customerImage)) {
            $customerImageBase64Encoded = base64_encode(file_get_contents($customerImage));
            $pdf->table .= '<img style="width:85px;" src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $customerImageBase64Encoded) . '">';
        }
        $pdf->table .= '</td>';
        
        $pdf->table .= '</tr>';
    $pdf->table .= '</table>';
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
// exit;

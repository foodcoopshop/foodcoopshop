<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$pdf->SetLeftMargin(16);
$pdf->SetTopMargin(54);

$pdf->AddPage();

$i = 0;
$pdf->table = '<table border="1" cellspacing="0" cellpadding="4">';
foreach($customers as $customer) {
    $pairRecord = $i % 2 == 0;
    if ($pairRecord) {
        $pdf->table .= '<tr>';
    }
    $pdf->table .= '<td style="width:240px;">'; // roughly 85,60mm x 53,98mm
    $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0">';
        
        // START ROW logo and name block
        $pdf->table .= '<tr>';
            $pdf->table .= '<td style="width:80px;">';
                $pdf->table .= '<img src="' . $pdf->logoPath .'">';
            $pdf->table .= '</td>';
            $pdf->table .= '<td style="width:10px;"></td>'; //spacer between logo and top right name block
            $pdf->table .= '<td style="width:138px;">';
                $pdf->table .= __d('admin', 'Customer_ID') . ': <b>' . $customer->id_customer . '</b><br />';
                $pdf->table .= __d('admin', 'Register_date') . ': ' . $customer->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . '<br />';
                $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0"><tr><td style="height:25px;"><b>' . $customer->name . '</b></td></tr></table>';
            $pdf->table .= '</td>';
        $pdf->table .= '</tr>';
        $pdf->table .= '<tr>';
            // spacer before FCS_APP_NAME
            $pdf->table .= '<td colspan="3" style="font-size:4px;"></td>';
        $pdf->table .= '</tr>';
        $pdf->table .= '<tr>';
            $pdf->table .= '<td colspan="3" style="line-height:20px;height:20px;border-bottom:1px solid dotted;border-top:1px solid dotted;">';
                $pdf->table .= __d('admin', 'Member_card') . ': <b>' . Configure::read('appDb.FCS_APP_NAME') . '</b>';
            $pdf->table .= '</td>';
        $pdf->table .= '</tr>';
        // END ROW with logo and name block
        
        // START ROW barcode and customer image
        $pdf->table .= '<tr>';
            $pdf->table .= '<td style="width:120px;">';
            $barcodeObject = new TCPDFBarcode($customer->bar_code, 'C39');
            //https://stackoverflow.com/a/54520065/2100184
            $imgBase64Encoded = base64_encode($barcodeObject->getBarcodePngData(1.5, 102));
            // move barcode to bottom
            $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0"><tr><td style="font-size:6px;"></td></tr></table>';
            $pdf->table .= '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $imgBase64Encoded) . '">';
            $pdf->table .= '</td>';
            $pdf->table .= '<td style="width:10px;"></td>'; //spacer between barcode and customer image
            // move user image to bottom
            $pdf->table .= '<td style="width:100px;" align="right">';
                $customerImage = Configure::read('app.customerImagesDir') . DS . $customer->id_customer . '-xxl.jpg';
                if (file_exists($customerImage)) {
                    $fileinfos = getimagesize($customerImage);
                    $ratio = $fileinfos[1] / $fileinfos[0];
                    $customerImageBase64Encoded = base64_encode(file_get_contents($customerImage));
                    $height = 68;
                    $width = $height / $ratio;
                    // move image to bottom
                    $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0"><tr><td style="font-size:3px;"></td></tr></table>';
                    $pdf->table .= '<img style="width:'.$width.'px;height:'.$height.'px;" src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $customerImageBase64Encoded) . '">';
                }
            $pdf->table .= '</td>';
        $pdf->table .= '</tr>';
        // END ROW barcode and customer image
        
    $pdf->table .= '</table>';
    $pdf->table .= '</td>';
    if (!$pairRecord || $i==$customers->count()-1) {
        $pdf->table .= '</tr>';
    }
    $i++;
}
$pdf->table .= '</table>';
$pdf->writeHTML($pdf->table, true, false, true, false, '');

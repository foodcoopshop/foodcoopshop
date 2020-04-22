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

$pdf->SetLeftMargin(16);
$pdf->SetTopMargin(15);

$pdf->AddPage();

$i = 0;
$pdf->table = '<table border="1" cellspacing="0" cellpadding="4">';
foreach($products as $product) {
    $pairRecord = $i % 2 == 0;
    if ($pairRecord) {
        $pdf->table .= '<tr>';
    }
    $pdf->table .= '<td style="width:240px;">'; // roughly 85,60mm x 53,98mm
    $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0">';
    
    // START ROW logo and name block
    $pdf->table .= '<tr>';
    $pdf->table .= '<td>';
    $pdf->table .= '<b style="font-size:12px;">' . $product->name . '</b> <br />';
    $pdf->table .= __d('admin', 'Price') . ': ' . $product->prepared_price . '<br />';
    $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0"><tr><td style="height:5px;">'.__d('admin', 'Manufacturer') . ': <b>' . $product->manufacturer->name . '</b> / ' . __d('admin', 'Product_ID') . ': <b>' . $product->id_product . '</b></td></tr></table>';
    $pdf->table .= '</td>';
    $pdf->table .= '</tr>';
    // END ROW with logo and name block
    
    // START ROW barcode and product image
    $pdf->table .= '<tr>';
    $pdf->table .= '<td style="width:120px;">';
    $barcodeObject = new TCPDFBarcode($product->bar_code, 'C39');
    //https://stackoverflow.com/a/54520065/2100184
    $imgBase64Encoded = base64_encode($barcodeObject->getBarcodePngData(1.3, 102));
    // move barcode to bottom
    $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0"><tr><td style="font-size:6px;"></td></tr></table>';
    $pdf->table .= '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $imgBase64Encoded) . '">';
    $pdf->table .= '</td>';
    $pdf->table .= '<td style="width:10px;"></td>'; //spacer between barcode and customer image
    // move user image to bottom
    $pdf->table .= '<td style="width:100px;" align="right">';
    
    if ($product->image) {
        $srcProductImage = $this->Html->getProductImageSrc($product->image->id_image, 'home');
        $srcProductImage = $this->Html->removeTimestampFromFile($srcProductImage);
        $largeImageExists = preg_match('/de-default/', $srcProductImage);
        if (!$largeImageExists) {
            $productImage = WWW_ROOT . $srcProductImage;
            $fileinfos = getimagesize($productImage);
            $ratio = $fileinfos[1] / $fileinfos[0];
            $productImageBase64Encoded = base64_encode(file_get_contents($productImage));
            $height = 68;
            $width = $height / $ratio;
            // move image to bottom
            $pdf->table .= '<table border="0" cellspacing="0" cellpadding="0"><tr><td style="font-size:3px;"></td></tr></table>';
            $pdf->table .= '<img style="width:'.$width.'px;height:'.$height.'px;" src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $productImageBase64Encoded) . '">';
        }
    }
    $pdf->table .= '</td>';
    $pdf->table .= '</tr>';
    // END ROW barcode and customer image
    
    $pdf->table .= '</table>';
    $pdf->table .= '</td>';
    if (!$pairRecord || $i==count($products)-1) {
        $pdf->table .= '</tr>';
    }
    $i++;
}
$pdf->table .= '</table>';
$pdf->writeHTML($pdf->table, true, false, true, false, '');

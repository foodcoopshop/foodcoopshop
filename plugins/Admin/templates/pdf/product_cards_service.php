<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$pdf->setLeftMargin(16);
$pdf->setTopMargin(8);
$pdf->setHtmlVSpace([]);

$pdf->addPage();

$pdf->table = '<style>td{vertical-align:top;} .product-card-details,.product-card-details *{line-height:125%;}</style>';
foreach (array_chunk($products, 2) as $productPair) {
    $pdf->table .= '<table nobr="true" border="0" cellspacing="0" cellpadding="4"><tr>';
    foreach ($productPair as $product) {
    $pdf->table .= '<td colspan="2" style="width:375px;height:48px;border-top:1px solid #E6E6E3;border-left:1px solid #E6E6E3;border-right:1px solid #E6E6E3;">';
    $pdf->table .= '<div class="product-card-details">';
    $pdf->table .= '<b style="font-size:12px;">' . $product->name . '</b> <br />';
    $pdf->table .= __('Price') . ': ' . $product->prepared_price . '<br />';
    $pdf->table .= __('Manufacturer') . ': <b>' . $product->manufacturer->name . '</b> / ' . __('Product_ID') . ': <b>' . $product->id_product . '</b>';
    $pdf->table .= '</div>';
    $pdf->table .= '</td>';
    }
    $pdf->table .= '</tr><tr>';
    foreach ($productPair as $product) {
    $pdf->table .= '<td style="width:275px;height:120px;border-left:1px solid #E6E6E3;border-bottom:1px solid #E6E6E3;padding-left:9px;padding-top:7.5px;font-size:0;line-height:0;">';
    $pdf->table .= $pdf->getBarcodeImageTag((string) $product->system_bar_code, 1.3, 108, 255);
    $pdf->table .= '</td><td style="width:100px;height:120px;border-right:1px solid #E6E6E3;border-bottom:1px solid #E6E6E3;padding-top:15px;text-align:right;font-size:0;line-height:0;">';
    if ($product->image) {
        $srcProductImage = $this->Html->getProductImageSrc($product->image->id_image, 'thickbox');
        $srcProductImage = $this->Html->removeTimestampFromFile($srcProductImage);
        $largeImageExists = $this->Html->largeImageExists($srcProductImage);
        if ($largeImageExists) {
            $productImage = WWW_ROOT . $srcProductImage;
            $fileinfos = getimagesize($productImage);
            if ($fileinfos !== false) {
                $ratio = $fileinfos[1] / $fileinfos[0];
                $productImageContents = file_get_contents($productImage);
                if ($productImageContents !== false) {
                    $productImageBase64Encoded = base64_encode($productImageContents);
                    $height = min(100, 90 * $ratio);
                    $width = $height / $ratio;
                    $pdf->table .= '<img style="width:'.$width.'px;height:'.$height.'px;" src="data:image/jpeg;base64,' . $productImageBase64Encoded . '">';
                }
            }
        }
    }
    $pdf->table .= '</td>';
    }
    $pdf->table .= '</tr></table>';
}
$pdf->writeHTML($pdf->table);

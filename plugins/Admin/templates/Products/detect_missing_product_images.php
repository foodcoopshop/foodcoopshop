<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', [
'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();"
]);

$i = 0;
$outputHtml = '';
foreach($products as $product) {
    if (!empty($product->image)) {
        $imageIdAsPath = $this->Html->getProductImageIdAsPath($product->image->id_image);
        $thumbsPath = $this->Html->getProductThumbsPath($imageIdAsPath);
        $size = 'home';
        $imageFilename = $this->Html->getImageFile($thumbsPath, $product->image->id_image . '-' . $size . '_default');
        $src = $thumbsPath . DS . $imageFilename;
        if (!file_exists($src)) {
            $i++;
            $outputHtml .= 'Geändert am: ' . $product->modified->i18nFormat($this->Time->getI18Format('DateNTimeLong'));
            $outputHtml .= ' / ID: ' . $product->id_product;
            $outputHtml .= ' / ' . $this->Html->link($product->name, $this->Slug->getProductDetail($product->id_product, $product->name));
            $outputHtml .= ' / ' . $this->Html->link('Admin',  $this->Slug->getProductAdmin($product->id_manufacturer, $product->id_product));
            $outputHtml .= ' / ' . $product->manufacturer->name . '<br />';
        }
    }
}

$outputHtml = '<b>Anzahl: ' . $i . '</b><br />' . $outputHtml;
echo $outputHtml;
?>

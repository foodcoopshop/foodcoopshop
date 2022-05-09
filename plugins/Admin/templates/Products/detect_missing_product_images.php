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

echo '<div class="filter-container">';
echo '</div>';

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
            $outputHtml .= $this->Html->link($product->name, $this->Slug->getProductDetail($product->id_product, $product->name));
            $outputHtml .= ' / ' . $this->Html->link('Admin',  $this->Slug->getProductAdmin($product->id_manufacturer, $product->id_product));
            $outputHtml .= ' / ' . $product->manufacturer->name . '<br />';
        }
    }
}

if ($i == 0) {
    $outputHtml = Configure::read('appDb.FCS_APP_NAME') . ' ist vom Bug nicht betroffen! Alle Produkt-Bilder sind ok. 👍';
} else {
    $introText = '<b>Aufgrund eines Bugs sind die Bilder zu folgenden Produkten leider nicht mehr vorhanden. Trotz intensiver Suche habe ich den Fehler bisher noch nicht eingrenzen können (Stand: 27.04.2022), dh. auch nach einem erneuten Upload kann es sein, dass das Bild wieder verschwindet. Das Fehlen der Bilder fällt nicht sofort auf, da im Produkt-Admin das fehlende Bild nicht rot hinterlegt ist. Hinweise bitte per Mail an office@foodcoopshop.com.<br />Danke für die Mithilfe, Mario<br /><br /></b>';
    $outputHtml = $introText . '<b>Anzahl: ' . $i . '</b><br />' . $outputHtml;
}

echo $outputHtml;
?>

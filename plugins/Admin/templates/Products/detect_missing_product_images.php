<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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
    $introText = '<b>Aufgrund eines Bugs (der Ende Februar 2023 gefixt wurde) sind die Bilder zu folgenden Produkten leider nicht mehr vorhanden. Bitte lade sie erneut hoch.<br />Sorry für die Umstände, Mario<br /><br /></b>';
    $outputHtml = $introText . '<b>Anzahl: ' . $i . '</b><br />' . $outputHtml;
}

echo $outputHtml;
?>

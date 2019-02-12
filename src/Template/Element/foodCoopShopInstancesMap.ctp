<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>

<?php

echo '<h1 style="float:left;' . (!$isFirstElement ? 'margin-top:20px;' : '') . '">';
    echo __('Other_initiaives_working_with_FoodCoopShop');
echo '</h1>';

?>

<iframe src="https://umap.openstreetmap.fr/de/map/verbreitung-foodcoopshop_211165?scaleControl=false&amp;miniMap=false&amp;scrollWheelZoom=true&amp;zoomControl=true&amp;allowEdit=false&amp;moreControl=false&amp;searchControl=null&amp;tilelayersControl=false&amp;embedControl=true&amp;datalayersControl=false&amp;onLoadPanel=undefined&amp;captionBar=false" width="100%" height="400px" frameborder="0"></iframe>

<a href="https://foodcoops.at/map/" class="btn btn-outline-light" target="_blank"><?php echo __('Map_with_all_Austrian_foodocops'); ?></a>
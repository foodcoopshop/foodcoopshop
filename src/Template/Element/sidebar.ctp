<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<div class="sidebar">
    <?php
    if (!empty($categoriesForMenu)) {
        echo $this->Menu->render($categoriesForMenu, ['id' => 'categories-menu', 'class' => 'vertical menu', 'header' => __('Categories')]);
    }
    if (!empty($manufacturersForMenu)) {
        echo $this->Menu->render($manufacturersForMenu, ['id' => 'manufacturers-menu', 'class' => 'vertical menu', 'header' => __('Manufacturers')]);
    }
    ?>
</div>
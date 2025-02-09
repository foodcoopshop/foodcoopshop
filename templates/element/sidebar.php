<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$classes = ['sidebar'];
if (empty($categoriesForMenu) && empty($manufacturersForMenu)) {
    $classes[] = 'empty';
}

?>
<div class="<?php echo join(' ', $classes); ?>">
    <?php
    if (!empty($filtersForMenu)) {
        echo $this->Menu->render($filtersForMenu, ['id' => 'filters-menu', 'class' => 'vertical menu', 'header' => __('Filters')]);
    }
    if (!empty($categoriesForMenu)) {
        echo $this->Menu->render($categoriesForMenu, ['id' => 'categories-menu', 'class' => 'vertical menu', 'header' => __('Categories')]);
    }
    if (!empty($manufacturersForMenu)) {
        echo $this->Menu->render($manufacturersForMenu, ['id' => 'manufacturers-menu', 'class' => 'vertical menu', 'header' => __('Manufacturers')]);
    }
    ?>
</div>
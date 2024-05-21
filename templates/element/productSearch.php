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
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.initSearchForm();"
]);
?>
<div class="product-search-form-wrapper">
    
    <form id="product-search-1" action="/<?php echo $action;?>">
        <input placeholder="<?php echo $placeholder; ?>" name="keyword" type="text" required="required" <?php echo isset($keyword) ? 'value="'.$keyword.'"' : ''; ?> />
        <button type="submit" class="btn btn-success submit"><i class="fas fa-search"></i></button>
    </form>

    <?php if ($includeCategoriesDropdown) { ?>
        <form id="product-search-2" class="product-search-form" action="/<?php echo $action;?>">
        <?php
            echo $this->Form->control('categoryId', [
                'type' => 'select',
                'label' => '',
                'empty' => __('chose_category...'),
                'options' => $categoriesForSelect,
                'default' => isset($categoryId) ? $categoryId : '',
            ]);
        ?>
        </form>
    <?php } ?>

    <?php if ( (isset($keyword) && $keyword != '') || ($includeCategoriesDropdown && $categoryId > 0)) { ?>
        <a href="<?php echo $resetSearchUrl; ?>" class="btn btn-success do-not-change-to-target-blank reset" title="<?php echo __('Reset_search'); ?>"><i class="fas fa-backspace"></i></a>
    <?php } ?>

</div>

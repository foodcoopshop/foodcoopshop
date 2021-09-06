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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.initSearchForm();"
]);
?>
<form id="product-search" action="/<?php echo $action;?>">
    <input placeholder="<?php echo $placeholder; ?>" name="keyword" type="text" required="required" <?php echo isset($keyword) ? 'value="'.$keyword.'"' : ''; ?> />
    <?php
        if ($includeCategoriesDropdown) {
            echo $this->Form->control('categoryId', [
                'type' => 'select',
                'label' => '',
                'empty' => __('chose_category...'),
                'options' => $categoriesForSelect,
                'default' => isset($categoryId) ? $categoryId : ''
            ]);
        }
    ?>
    <?php if (isset($keyword) || ($includeCategoriesDropdown && $categoryId != Configure::read('app.categoryAllProducts'))) { ?>
        <a href="<?php echo $resetSearchUrl; ?>" class="btn btn-success do-not-change-to-target-blank reset" title="<?php echo __('Reset_search'); ?>"><i class="fas fa-backspace"></i></a>
    <?php } ?>
    <button type="submit" class="btn btn-success submit"><i class="fas fa-search"></i></button>
</form>

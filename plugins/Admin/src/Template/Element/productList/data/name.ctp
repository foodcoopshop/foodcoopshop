<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<td>';

    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
            'class' => 'product-name-edit-button',
            'title' => '<b>'.__d('admin', 'Short_description').'</b><br />'.$product->description_short.'<br /><br /><b>'.__d('admin', 'Long_description').'</b><br />'.$product->description,
        ], 'javascript:void(0);');
    }
    
    if (! isset($product->product_attributes)) {
        echo '<span style="float:left;margin-right: 5px;">';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
            'class' => 'delete-product-attribute-button',
            'title' => __d('admin', 'Delete_attribute_for_product_{0}', [$product->unchanged_name])
        ], 'javascript:void(0);');
        echo '</span>';
        
        echo '<span style="float:left;">';
        if ($product->default_on == 1) {
            echo $this->Html->image($this->Html->getFamFamFamPath('star.png'), [
                'title' => __d('admin', 'This_attribute_is_the_default_attribute.')
            ]);
        } else {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('bullet_star.png')), [
                'class' => 'set-as-default-attribute-button',
                'title' => __d('admin', 'Define_as_new_default_attribute')
            ], 'javascript:void(0);');
        }
        echo '</span>';
    }
    
    echo '<span class="name-for-dialog">';
        echo $product->name;
    echo '</span>';
    
    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        echo '<span data-is-declaration-ok="'.$product->is_declaration_ok.'" class="is-declaration-ok-wrapper">' . ($product->is_declaration_ok ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>').'</span>';
    }
    
    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        echo $this->Form->hidden('Products.selected_categories', [
            'value' => implode(',', $product->selected_categories),
            'id' => 'selected-categories-' . $product->id_product
        ]);
        echo '<br />';
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
            'class' => 'product-categories-edit-button',
            'title' => __d('admin', 'change_category'),
            'data-object-id' => $product->id_product
        ], 'javascript:void(0);');
        echo '<span class="categories-for-dialog">';
            if (empty($product->category->names)) {
                echo __d('admin', 'chose_category...');
            } else {
                echo join(', ', $product->category->names);
            }
        echo '</span>';
        if (! $product->category->all_products_found) {
            echo ' - <b>'.__d('admin', 'Category_"all_products"_is_missing!').'</b>';
        }
    }
    
    echo '<span class="description-short-for-dialog">';
        echo $product->description_short;
    echo '</span>';
    
    echo '<span class="description-for-dialog">';
        echo $product->description;
    echo '</span>';

echo '</td>';

?>
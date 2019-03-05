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

echo '<td class="cell-name">';

    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-name-edit-button',
                'title' => h('<b>'.__d('admin', 'Short_description').'</b><br />'.$product->description_short.'<br /><br /><b>'.__d('admin', 'Long_description').'</b><br />'.$product->description),
                'escape' => false
            ]
        );
    }
    
    if (! isset($product->product_attributes)) {
        echo '<span style="float:left;margin-right: 5px;">';
        echo $this->Html->link(
            '<i class="fas fa-times-circle not-ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light delete-product-attribute-button',
                'title' => __d('admin', 'Delete_attribute_for_product_{0}', [$product->unchanged_name]),
                'escape' => false
            ]
        );
        echo '</span>';
        
        echo '<span style="float:left;">';
        if ($product->default_on == 1) {
            echo '<i class="fas fa-star gold" title="'.__d('admin', 'This_attribute_is_the_default_attribute.').'"></i>';
        } else {
            
            echo $this->Html->link(
                '<i class="fas fa-star gold fa-xs"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light set-as-default-attribute-button',
                    'title' => __d('admin', 'Define_as_new_default_attribute'),
                    'escape' => false
                ]
            );
        }
        echo '</span>';
    }
    
    echo '<span class="name-for-dialog">';
        echo $product->name;
    echo '</span>';
    
    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        echo '<span data-is-declaration-ok="'.$product->is_declaration_ok.'" class="is-declaration-ok-wrapper">' . ($product->is_declaration_ok ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>').'</span>';
    }
    
    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        echo $this->Form->hidden('Products.selected_categories', [
            'value' => implode(',', $product->selected_categories),
            'id' => 'selected-categories-' . $product->id_product
        ]);
        echo '<br />';
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-categories-edit-button',
                'title' => __d('admin', 'change_category'),
                'data-object-id' => $product->id_product,
                'escape' => false
            ]
        );
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
<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

echo '<td class="cell-name">';

    if (! empty($product->product_attributes) || isset($product->product_attributes)) {
        $title = [];
        if ($product->description_short != '') {
            $title[] = '<b>'.__d('admin', 'Short_description').'</b><br />'.$product->description_short;
        }
        if ($product->description != '') {
            $title[] = '<b>'.__d('admin', 'Long_description').'</b><br />'.$product->description;
        }
        if (Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS') && isset($storageLocationsForForDropdown[$product->id_storage_location])) {
            $title[] = '<b>'.__d('admin', 'Storage_location').'</b>: '.$storageLocationsForForDropdown[$product->id_storage_location];
        }

        $title[] = '<b>'.__d('admin', 'changed').'</b><br /> '.date(Configure::read('DateFormat.DateNTimeShortWithSecsAlt'), strtotime($product->modified));
        $title[] = '<b>'.__d('admin', 'created').'</b><br />'.date(Configure::read('DateFormat.DateNTimeShortWithSecsAlt'), strtotime($product->created));

        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light product-name-edit-button',
                'title' => h(join('<br /><br />', $title)),
                'escape' => false
            ]
        );
    }

    if (! isset($product->product_attributes)) {
        echo '<span style="float:left;margin-right: 5px;">';
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light edit-product-attribute-button',
                'title' => __d('admin', 'Edit_attribute_for_product_{0}', [$product->name]),
                'escape' => false
            ]
        );
        echo '</span>';

        echo '<span style="float:left;">';
        if ($product->default_on == 1) {
            echo '<i class="fas fa-star gold" title="'.__d('admin', 'This_attribute_is_the_default_attribute.').'"></i>';
        } else {

            echo $this->Html->link(
                '<i class="fas fa-star fa-xs neutral"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light set-default-attribute-button',
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
        echo '<span data-is-declaration-ok="'.$product->is_declaration_ok.'" class="is-declaration-ok-wrapper">' . ($product->is_declaration_ok ? '<i class="fas fa-check ok"></i>' : '<i class="fas fa-times"></i>').'</span>';
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

        if (Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS')) {
            echo '<span class="storage-location-for-dialog">';
            echo $product->id_storage_location;
            echo '</span>';
        }

    }

    echo '<span class="description-short-for-dialog">';
        echo $product->description_short;
    echo '</span>';

    echo '<span class="description-for-dialog">';
        echo $product->description;
    echo '</span>';

    echo '<span class="barcode-for-dialog">';
        if (!empty($product->barcode_product)) {
            echo $product->barcode_product->barcode;
        }
        echo '</span>';

    echo '</td>';

?>
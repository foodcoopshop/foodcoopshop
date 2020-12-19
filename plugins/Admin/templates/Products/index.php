<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="products" class="product-list">

        <?php
        $this->element('addScript', [
        'script' =>
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductStatusEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductDepositEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductNameEdit.init();" .
            Configure::read('app.jsNamespace') . ".Admin.initProductQuantityList('#products');" .
            Configure::read('app.jsNamespace') . ".Helper.setIsManufacturer(" . $appAuth->isManufacturer() . ");" .
            Configure::read('app.jsNamespace') . ".ModalProductQuantityEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductCategoriesEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductTaxEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductStatusNewEdit.init();" .
            Configure::read('app.jsNamespace') . ".Upload.initImageUpload('#products .add-image-button', foodcoopshop.Upload.saveProductImage);" .
            Configure::read('app.jsNamespace') . ".ModalProductAttributeAdd.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductAttributeDelete.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductAttributeSetDefault.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductPriceEdit.init();" .
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.add-image-button, .product-name-edit-button');".
            Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ", " . ($manufacturerId > 0 ? $manufacturerId : '0') . ");".
            Configure::read('app.jsNamespace') . ".ModalProductDeliveryRhythmEdit.init();
        "
        ]);

        $this->element('highlightRowAfterEdit', [
            'rowIdPrefix' => '#product-'
        ]);

        if ($advancedStockManagementEnabled) {
            $this->element('addScript', [
                'script' =>
                    Configure::read('app.jsNamespace') . ".ModalProductIsStockProductEdit.init();"
            ]);
        }
    ?>

    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php
            if ($manufacturerId > 0) {
                echo $this->Form->control('productId', [
                    'type' => 'select',
                    'label' => '',
                    'empty' => __d('admin', 'all_products'),
                    'options' => []
                ]);
            }
            if (! $appAuth->isManufacturer()) {
                echo $this->Form->control('manufacturerId', [
                    'type' => 'select',
                    'label' => '',
                    'options' => $manufacturersForDropdown,
                    'empty' => __d('admin', 'chose_manufacturer...'),
                    'default' => isset($manufacturerId) ? $manufacturerId : ''
                ]);
            }
            echo $this->Form->control('active', [
                'type' => 'select',
                'label' => '',
                'options' => $this->MyHtml->getActiveStates(),
                'default' => isset($active) ? $active : ''
            ]);
            echo $this->Form->control('categoryId', [
                'type' => 'select',
                'label' => '',
                'multiple' => true,
                'empty' => __d('admin', 'chose_category...'),
                'options' => $categoriesForDropdown,
                'default' => isset($categoryId) ? $categoryId : ''
            ]);
            ?>
            <?php echo $this->Form->control('isQuantityMinFilterSet', ['type'=>'checkbox', 'label' => __d('admin', 'amount') . ' < 3', 'checked' => $isQuantityMinFilterSet]);?>
            <?php echo $this->Form->control('isPriceZero', ['type'=>'checkbox', 'label' => __d('admin', 'price') . ' = 0', 'checked' => $isPriceZero]);?>

            <div class="right">
                <?php
                // only show button if no manufacturer filter is applied
                if ($manufacturerId > 0) {
                    $this->element('addScript', [
                        'script' => Configure::read('app.jsNamespace') . ".ModalProductAdd.init();"
                    ]);
                    echo '<div id="add-product-button-wrapper" class="add-button-wrapper">';
                    echo $this->Html->link('<i class="fas fa-plus-circle"></i> ' . __d('admin', 'Add_product'), 'javascript:void(0);', [
                        'class' => 'btn btn-outline-light',
                        'escape' => false
                    ]);
                    echo '</div>';
                }

                if (isset($showSyncProductsButton) && $showSyncProductsButton) {
                    $this->element('addScript', [
                        'script' => Configure::read('app.jsNamespace') . ".Admin.addLoaderToSyncProductDataButton($('.toggle-sync-button-wrapper a'));"
                    ]);
                    echo '<div class="toggle-sync-button-wrapper">';
                        echo $this->Html->link(
                            '<i class="fas fa-arrow-circle-right"></i> ' . __d('admin', 'Synchronize_products'),
                            $this->Network->getSyncProductData(),
                            [
                                'class' => 'btn btn-outline-light',
                                'escape' => false
                            ]
                        );
                    echo '</div>';
                }
                echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_products'))]);
                ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

    <?php

    if (!Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
        if (!empty($manufacturer)) {
            $globalNoDeliveryDaysString = $this->Html->getGlobalNoDeliveryDaysString();
            if ($globalNoDeliveryDaysString != '') {
                echo '<h2 class="info">' . $globalNoDeliveryDaysString . '</h2>';
            }
            $manufacturerNoDeliveryDaysString = $this->Html->getManufacturerNoDeliveryDaysString($manufacturer, true);
            if ($manufacturerNoDeliveryDaysString != '') {
                echo '<h2 class="info">'.$manufacturerNoDeliveryDaysString.'</h2>';
            }
        }
    }

    if (empty($products) && $manufacturerId == '') {
        echo '<h2 class="info">'.__d('admin', 'Please_chose_a_manufacturer.').'</h2>';
    }

    echo '<table class="list no-clone-last-row">';

    echo '<tr class="sort">';
        echo $this->element('rowMarker/rowMarkerAll', [
            'enabled' => !empty($products)
        ]);
        echo '<th class="hide">ID</th>';
        echo '<th>'.__d('admin', 'Attribute').'</th>';
        echo '<th>' . $this->Paginator->sort('Images.id_image', __d('admin', 'Image')) . '</th>';
        echo '<th>' . $this->Paginator->sort('Products.name', __d('admin', 'Name_and_categories')) . '<span class="product-declaration-header">' . $this->Paginator->sort('Products.is_declaration_ok', __d('admin', 'Product_declaration')) . '</span></th>';
        if ($manufacturerId == 'all') {
            echo '<th>' . $this->Paginator->sort('Manufacturers.name', __d('admin', 'Manufacturer')) . '</th>';
        }
        if ($advancedStockManagementEnabled) {
            echo '<th>' . $this->Paginator->sort('Products.is_stock_product', __d('admin', 'Stock_product')) . '</th>';
        }
        echo '<th style="width:62px;">'.__d('admin', 'Amount').'</th>';
        echo '<th>'.__d('admin', 'Price').'</th>';
        echo '<th style="width:70px;">' . $this->Paginator->sort('Taxes.rate', __d('admin', 'Tax_rate')) . '</th>';
        echo '<th class="center" style="width:69px;">' . $this->Paginator->sort('Products.created', __d('admin', 'New?')) . '</th>';
        if (Configure::read('app.isDepositEnabled')) {
            echo '<th>'.__d('admin', 'Deposit').'</th>';
        }
        echo '<th>' . $this->Paginator->sort('Products.delivery_rhythm_type', __d('admin', 'Delivery_rhythm')) . '</th>';
        echo '<th>' . $this->Paginator->sort('Products.active', __d('admin', 'Status')) . '</th>';
        echo '<th style="width:29px;"></th>';
    echo '</tr>';

    $i = 0;
    foreach ($products as $product) {
        $i ++;

        echo '<tr id="product-' . $product->id_product . '" class="data ' . $product->row_class . '" data-manufacturer-id="'.(isset($product->id_manufacturer) ? $product->id_manufacturer : '').'">';

        echo $this->element('rowMarker/rowMarker', [
            'show' => (!empty($product->product_attributes) || isset($product->product_attributes))
        ]);

        echo $this->element('productList/data/id', [
            'product' => $product
        ]);

        echo $this->element('productList/data/addAttributeButton', [
            'product' => $product
        ]);

        echo $this->element('productList/data/image', [
            'product' => $product
        ]);

        echo $this->element('productList/data/name', [
            'product' => $product
        ]);

        echo $this->element('productList/data/manufacturerName', [
            'product' => $product,
            'manufacturerId' => $manufacturerId
        ]);

        echo $this->element('productList/data/isStockProduct', [
            'product' => $product,
            'advancedStockManagementEnabled' => $advancedStockManagementEnabled
        ]);

        echo $this->element('productList/data/amount', [
            'product' => $product
        ]);

        echo $this->element('productList/data/price', [
            'product' => $product
        ]);

        echo $this->element('productList/data/tax', [
            'product' => $product
        ]);

        echo $this->element('productList/data/isNew', [
            'product' => $product
        ]);

        echo $this->element('productList/data/deposit', [
            'product' => $product
        ]);

        echo $this->element('productList/data/deliveryRhythm', [
            'product' => $product
        ]);

        echo $this->element('productList/data/status', [
            'product' => $product
        ]);

        echo $this->element('productList/data/preview', [
            'product' => $product
        ]);

        echo '</tr>';
    }

    echo '<tr>';

    $colspan = 13;
    if ($manufacturerId == 'all') {
        $colspan++;
    }
    if ($advancedStockManagementEnabled) {
        $colspan++;
    }

    echo '<td colspan="'.$colspan.'"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
    echo '</tr>';

    echo '</table>';

    echo '<div class="bottom-button-container">';
        echo $this->element('productList/button/deleteSelectedProducts');
        echo $this->element('productList/button/generateProductCardsOfSelectedProducts');
        echo $this->element('productList/button/editDeliveryRhythmForSelectedProducts', [
            'products' => $products
        ]);
    echo '</div>';

    ?>

    <div class="sc"></div>

</div>

<?php

    // dropdowns and checkboxes for overlays are only rendered once (performance)
    echo $this->Form->control('productAttributeId', ['type' => 'select', 'class' => 'hide', 'label' => '', 'options' => $attributesForDropdown]);

    echo '<div class="categories-checkboxes">';
        echo '<input type="hidden" class="product-id" />';
        echo $this->Form->control('Products.CategoryProducts', [
            'label' => '',
            'multiple' => 'checkbox',
            'options' => $categoriesForCheckboxes,
            'escape' => false,
        ]);
        echo '</div>';
        echo '<div class="tax-dropdown-wrapper">';
        echo '<input type="hidden" class="product-id" />';
        echo $this->Form->control('Taxes.id_tax', [
            'type' => 'select',
            'label' => '',
            'options' => $taxesForDropdown,
        ]);
    echo '</div>';

    echo '<div class="delivery-rhythm-dropdown-wrapper">';
        echo $this->Form->control('RhythmTypes', [
            'type' => 'select',
            'label' => '',
            'options' => $this->Html->getDeliveryRhythmTypesForDropdown()
        ]);
        echo $this->Form->control('Weekdays', [
            'type' => 'select',
            'label' => '',
            'options' => $this->Time->getSendOrderListsWeekdayOptions()
        ]);
    echo '</div>';

?>

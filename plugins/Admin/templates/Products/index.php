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

use Cake\Core\Configure;

$paginator = $this->loadHelper('Paginator', [
    'className' => 'ArraySupportingSortOnlyPaginator',
]);

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
            Configure::read('app.jsNamespace') . ".Helper.setIsManufacturer(" . $identity->isManufacturer() . ");" .
            Configure::read('app.jsNamespace') . ".Helper.setIsSelfServiceModeEnabled(" . Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') . ");" .
            Configure::read('app.jsNamespace') . ".ModalProductQuantityEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductCategoriesEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductTaxEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductStatusNewEdit.init();" .
            Configure::read('app.jsNamespace') . ".Upload.initImageUpload('#products .add-image-button', foodcoopshop.Upload.saveProductImage);" .
            Configure::read('app.jsNamespace') . ".ModalProductAttributeAdd.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductAttributeEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductAttributeSetDefault.init();" .
            Configure::read('app.jsNamespace') . ".ModalProductPriceEdit.init(".Configure::read('app.changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled').", " . !$identity->isManufacturer() . ");" .
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.add-image-button, .product-name-edit-button, .purchase-price-not-set-info-text');".
            Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ", " . ((int) $manufacturerId > 0 ? $manufacturerId : '0') . ");".
            Configure::read('app.jsNamespace') . ".ModalProductDeliveryRhythmEdit.init();
        "
        ]);

        $this->element('highlightRowAfterEdit', [
            'rowIdPrefix' => '#product-'
        ]);

        if ($advancedStockManagementEnabled) {
            $this->element('addScript', [
                'script' => Configure::read('app.jsNamespace') . ".ModalProductIsStockProductEdit.init();"
            ]);
        }

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && !$identity->isManufacturer()) {
            $this->element('addScript', [
                'script' => Configure::read('app.jsNamespace') . ".ModalProductPurchasePriceEdit.init();"
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
                    'placeholder' => __d('admin', 'all_products'),
                    'options' => [],
                ]);
            }
            if (! $identity->isManufacturer()) {
                echo $this->Form->control('manufacturerId', [
                    'type' => 'select',
                    'label' => '',
                    'options' => $manufacturersForDropdown,
                    'empty' => __d('admin', 'chose_manufacturer...'),
                    'default' => isset($manufacturerId) ? $manufacturerId : ''
                ]);
            } else {
                echo $this->Form->hidden('manufacturerId', [
                    'id' => 'manufacturerid',
                    'val' => $identity->getManufacturerId(),
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
                if ($manufacturerId != 'all' && $manufacturerId != '') {
                    $this->element('addScript', [
                        'script' => Configure::read('app.jsNamespace') . ".ModalProductAdd.init();"
                    ]);
                    echo '<div id="add-product-button-wrapper" class="add-button-wrapper">';
                    echo $this->Html->link('<i class="fas fa-plus-circle ok"></i> ' . __d('admin', 'Add_product'), 'javascript:void(0);', [
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

                echo $this->element('productList/selectedProductsDropdown', [
                    'helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_products')),
                    'manufacturerId' => $manufacturerId,
                ]);

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
        echo '<th>' . $paginator->sort('Images.id_image', __d('admin', 'Image')) . '</th>';
        echo '<th>' . $paginator->sort('Products.name', __d('admin', 'Name_and_categories')) . '<span class="product-declaration-header">' . $paginator->sort('Products.is_declaration_ok', __d('admin', 'Product_declaration')) . '</span></th>';
        if ($manufacturerId == 'all') {
            echo '<th>' . $paginator->sort('Manufacturers.name', __d('admin', 'Manufacturer')) . '</th>';
        }
        if ($advancedStockManagementEnabled) {
            echo '<th>' . $paginator->sort('Products.is_stock_product', __d('admin', 'Stock_product')) . '</th>';
        }
        echo '<th style="width:65px;">'.__d('admin', 'Amount').'</th>';

        $showSellingPriceAndDeposit = false;
        $showSellingPriceTax = false;
        $showPurchasePrice = false;
        $showPurchasePriceTax = false;
        if ($identity->isSuperadmin() || $identity->isAdmin()) {
            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                $showSellingPriceAndDeposit = true;
                $showPurchasePrice = true;
                $showSellingPriceTax = true;
                $showPurchasePriceTax = true;
                echo '<th style="text-align:right;width:98px;">'.__d('admin', 'Purchase_price_abbreviation') . ' ' . __d('admin', 'gross') . '</th>';
                echo '<th style="text-align:center;">'.__d('admin', 'Surcharge') . ' ' . __d('admin', 'net') . '</th>';
                echo '<th style="text-align:right;width:98px;">'.__d('admin', 'Selling_price_abbreviation') . ' ' . __d('admin', 'gross') . '</th>';
            } else {
                $showSellingPriceAndDeposit = true;
                $showSellingPriceTax = true;
                echo '<th>'.__d('admin', 'Price').'</th>';
            }
        }

        if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && $identity->isManufacturer()) {
            $showSellingPriceAndDeposit = true;
            $showSellingPriceTax = true;
            echo '<th>'.__d('admin', 'Price').'</th>';
        } else {
            // do not show purchase price, selling price and deposit for manufacturers in retail mode
        }

        $taxWidth = 80;
        if ($showSellingPriceTax && $showPurchasePrice) {
            $taxWidth = 106;
        }
        if ($showSellingPriceTax || $showPurchasePrice) {
            echo '<th style="width:'.$taxWidth.'px;">' . $paginator->sort('Taxes.rate', __d('admin', 'Tax_rate')) . '</th>';
        }
        echo '<th class="center" style="width:69px;">' . $paginator->sort('Products.created', __d('admin', 'New?')) . '</th>';
        if (Configure::read('app.isDepositEnabled') && $showSellingPriceAndDeposit) {
            echo '<th>'.__d('admin', 'Deposit').'</th>';
        }
        echo '<th>' . $paginator->sort('Products.delivery_rhythm_type', __d('admin', 'Delivery_rhythm')) . '</th>';
        echo '<th>' . $paginator->sort('Products.active', __d('admin', 'Status')) . '</th>';
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

        if ($showPurchasePrice) {
            echo $this->element('productList/data/purchasePrice', [
                'product' => $product
            ]);
            echo $this->element('productList/data/surcharge', [
                'product' => $product
            ]);
        }

        if ($showSellingPriceAndDeposit) {
            echo $this->element('productList/data/price', [
                'product' => $product
            ]);
        }

        if ($showSellingPriceTax || $showPurchasePrice) {
            echo $this->element('productList/data/tax', [
                'product' => $product,
                'showPurchasePriceTax' => $showPurchasePriceTax,
            ]);
        }

        echo $this->element('productList/data/isNew', [
            'product' => $product
        ]);

        if ($showSellingPriceAndDeposit) {
            echo $this->element('productList/data/deposit', [
                'product' => $product
            ]);
        }

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

    $colspan = 14;
    if ($manufacturerId == 'all') {
        $colspan++;
    }
    if ($advancedStockManagementEnabled) {
        $colspan++;
    }

    if (!$showPurchasePrice) {
        $colspan = $colspan - 2;
    }
    if (!$showSellingPriceAndDeposit) {
        $colspan--;
    }

    if (Configure::read('app.isDepositEnabled') && !$showSellingPriceAndDeposit) {
        $colspan--;
    }

    if (!$showSellingPriceTax && !$showPurchasePrice) {
        $colspan--;
    }

    echo '<td colspan="'.$colspan.'"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
    echo '</tr>';

    echo '</table>';

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
            'label' => __d('admin', 'Selling_price'),
            'options' => $taxesForDropdown,
        ]);
     echo '</div>';

    if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
        echo '<div class="purchase-price-tax-dropdown-wrapper">';
            echo '<input type="hidden" class="product-id" />';
            echo $this->Form->control('PurchasePriceTaxes.id_tax', [
                'type' => 'select',
                'label' => __d('admin', 'Purchase_price'),
                'options' => $taxesForDropdown,
            ]);
        echo '</div>';
    }


    if (Configure::read('appDb.FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS')) {
        echo '<div class="storage-location-dropdown-wrapper">';
            echo '<input type="hidden" class="product-id" />';
            echo $this->Form->control('Products.id_storage_location', [
                'type' => 'select',
                'label' => __d('admin', 'Storage_location'),
                'options' => $storageLocationsForForDropdown,
            ]);
        echo '</div>';
    }

    echo '<div class="delivery-rhythm-dropdown-wrapper">';
        echo $this->Form->control('RhythmTypes', [
            'type' => 'select',
            'label' => '',
            'options' => $this->Html->getDeliveryRhythmTypesForDropdown()
        ]);
        echo $this->Form->control('Weekdays', [
            'type' => 'select',
            'label' => '',
            'options' => $this->Html->getSendOrderListsWeekdayOptions()
        ]);
    echo '</div>';

?>

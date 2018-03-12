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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="products" class="product-list">
     
        <?php
        $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.init();" . Configure::read('app.jsNamespace') . ".Admin.initProductChangeActiveState();" . Configure::read('app.jsNamespace') . ".Admin.initProductDepositEditDialog('#products');" . Configure::read('app.jsNamespace') . ".Admin.initProductNameEditDialog('#products');" . Configure::read('app.jsNamespace') . ".Admin.initProductQuantityEditDialog('#products');" . Configure::read('app.jsNamespace') . ".Admin.initProductCategoriesEditDialog('#products');" . Configure::read('app.jsNamespace') . ".Admin.initProductTaxEditDialog('#products');" . Configure::read('app.jsNamespace') . ".Admin.initChangeNewState();" . Configure::read('app.jsNamespace') . ".Upload.initImageUpload('#products .add-image-button', foodcoopshop.Upload.saveProductImage, foodcoopshop.AppFeatherlight.closeLightbox);" . Configure::read('app.jsNamespace') . ".Admin.initAddProductAttribute('#products');" . Configure::read('app.jsNamespace') . ".Admin.initDeleteProductAttribute('#products');" . Configure::read('app.jsNamespace') . ".Admin.initSetDefaultAttribute('#products');" . Configure::read('app.jsNamespace') . ".Admin.initProductPriceEditDialog('#products');" . Configure::read('app.jsNamespace') . ".Admin.initProductDropdown(" . ($productId != '' ? $productId : '0') . ", " . ($manufacturerId > 0 ? $manufacturerId : '0') . ");
        "
        ]);
        $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#product-'
        ]);
    ?>
    
    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php
            if ($manufacturerId > 0) {
                echo $this->Form->control('productId', [
                    'type' => 'select',
                    'label' => '',
                    'empty' => 'alle Produkte',
                    'options' => []
                ]);
            }
            if (! $appAuth->isManufacturer()) {
                echo $this->Form->control('manufacturerId', [
                    'type' => 'select',
                    'label' => '',
                    'options' => $manufacturersForDropdown,
                    'empty' => 'Bitte wählen...',
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
                'empty' => 'Kategorie auswählen...',
                'options' => $categoriesForSelect,
                'default' => isset($categoryId) ? $categoryId : ''
            ]);
            ?>
            <?php echo $this->Form->control('isQuantityZero', ['type'=>'checkbox', 'label' =>'Anzahl 0?', 'checked' => $isQuantityZero]);?>
            <?php echo $this->Form->control('isPriceZero', ['type'=>'checkbox', 'label' =>'Preis 0?', 'checked' => $isPriceZero]);?>
            
            <div class="right">
                <?php
                // only show button if no manufacturer filter is applied
                if ($manufacturerId > 0) {
                    $this->element('addScript', [
                        'script' => Configure::read('app.jsNamespace') . ".Admin.initAddProduct('#products');"
                    ]);
                    echo '<div id="add-product-button-wrapper" class="add-button-wrapper">';
                    echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neues Produkt', 'javascript:void(0);', [
                        'class' => 'btn btn-default',
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
                            '<i class="fa fa-arrow-circle-right"></i> Produkte synchronisieren',
                            $this->Network->getSyncProductData(),
                            [
                                'class' => 'btn btn-default',
                                'escape' => false
                            ]
                        );
                    echo '</div>';
                }

                ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

    <div id="help-container">
        <ul>
            <li>Auf dieser Seite werden deine <b>Produkt</b> verwaltet.
            </li>
            <li>
                Du kannst neue Produkte erstellen (Button rechts oben), mit einem Klick auf einen der Bearbeiten-Icons <?php echo $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')); ?> kannst den entsprechenden (z.B. Kategorien, Anzahl, Preis...) ändern.
            </li>
            <li>Hinweis zum Ändern der Beschreibung: <b><i>Kurze</i> Beschreibung</b>
                steht im Shop immer neben dem Bild und ist in den Listen zu lesen. <b><i>Lange</i>
                    Beschreibung</b> steht nur auf der Produkt-Detailseite (z.B. für
                das Anführen von Inhaltsstoffen geeignet).
            </li>
            <li>Du kannst deine Produkte <b>online bzw. offline setzen</b> (Icons <?php echo $this->Html->image($this->Html->getFamFamFamPath('accept.png')); ?> bzw. <?php echo $this->Html->image($this->Html->getFamFamFamPath('delete.png')); ?> ganz rechts).
            </li>
            <li><b>Varianten: </b>Mit dem <?php echo $this->Html->image($this->Html->getFamFamFamPath('add.png')); ?>-Icon kannst du eine neue Variante (z.B. 1kg, 2kg und 5kg) zu deinen Produkten anlegen. Das <?php echo $this->Html->image($this->Html->getFamFamFamPath('star.png')); ?>-Icon sagt dir, welche Variante beim Bestellen standardmäßig ausgewählt ist, diese Standardvariante kannst du ändern.
                Varianten können auf "nicht bestellbar" gesetzt werden, in dem du die Anzahl auf 0 setzt.
            </li>
            <li>Falls eine gewünschte Variante noch nicht zur Verfügung steht,
                sag uns bitte Bescheid. Wir legen sie dann für dich an.</li>
            <li>Wenn du von einem Produkt nur eine <b>beschränkte Anzahl</b>
                liefern kannst, ändere die Anzahl bitte dementsprechend. Unser
                System vermindert bei jeder Bestellung den Lagerbestand und stoppt
                die Bestellmöglichkeit, wenn keine Produkte mehr verfügbar sind,
                automatisch. Somit bekommt jeder, der bestellt, seine Ware und es
                muss nichts storniert werden.
            </li>
            <li><b>Bilder hochladen:</b> Durch Anklicken des <?php echo $this->Html->image($this->Html->getFamFamFamPath('image_add.png')); ?>-Icons kannst ein Bild zu deinem Produkt hochladen. Wenn zu einem Produkt noch kein Bild hochgeladen wurde, ist das Icon rot hinterlegt. Bilder zu Varianten sind nicht möglich. 
            </li>
            <li>Du siehst, für welche Produkte wir Pfand einheben. Möchtest du den
                Pfand ändern, sag uns bitte Bescheid.</li>
            <li><b>Neue Produkte</b> können im Shop als "neu" gekennzeichnet werden und scheinen dann <?php echo Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW'); ?> Tage lang unter <a
                href="<?php echo Configure::read('app.cakeServerName'); ?>/neue-produkte"
                target="_blank">"Neue Produkte"</a> auf.</li>
        </ul>
    </div>
    
    <?php

    if (!empty($manufacturer)) {
        $manufacturerHolidayString = $this->Html->getManufacturerHolidayString($manufacturer->holiday_from, $manufacturer->holiday_to, $manufacturer->is_holiday_active, true, $manufacturer->name);
        if ($manufacturerHolidayString != '') {
            echo '<h2 class="info">'.$manufacturerHolidayString.'</h2>';
        }
    }

    if (empty($products) && $manufacturerId == '') {
        echo '<h2 class="info">Bitte wähle einen Hersteller aus.</h2>';
    }

    echo '<table class="list no-clone-last-row">';

    echo '<tr class="sort">';
    echo '<th class="hide">ID</th>';
    echo '<th>Variante</th>';
    echo '<th>' . $this->Paginator->sort('Images.id_image', 'Bild') . '</th>';
    echo '<th>' . $this->Paginator->sort('ProductLangs.name', 'Name') . '<span class="product-declaration-header">' . $this->Paginator->sort('ProductLangs.is_declaration_ok', 'Produktdeklaration') . '</span></th>';
    if ($manufacturerId == 'all') {
        echo '<th>' . $this->Paginator->sort('Manufacturers.name', 'Hersteller') . '</th>';
    }
    echo '<th>Kategorien</th>';
    echo '<th>Anzahl</th>';
    echo '<th>Preis</th>';
    echo '<th>' . $this->Paginator->sort('Taxes.rate', 'Steuersatz') . '</th>';
    echo '<th class="center" style="width:69px;">' . $this->Paginator->sort('ProductShops.created', 'Neu?') . '</th>';
    echo '<th>Pfand</th>';
    echo '<th>' . $this->Paginator->sort('Products.active', 'Status') . '</th>';
    echo '<th style="width:29px;"></th>';
    echo '</tr>';

    $i = 0;
    foreach ($products as $product) {
        $i ++;

        echo '<tr id="product-' . $product->id_product . '" class="data ' . $product->row_class . '" data-manufacturer-id="'.(isset($product->id_manufacturer) ? $product->id_manufacturer : '').'">';

        echo '<td class="hide">';
        echo $product->id_product;
        echo '</td>';

        echo '<td style="text-align: center;padding-left:16px;width:50px;">';
        if (! empty($product->product_attributes) || isset($product->product_attributes)) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('add.png')), [
                'class' => 'add-product-attribute-button',
                'title' => 'Neue Variante für Produkt "' . $product->product_lang->name . '" erstellen'
            ], 'javascript:void(0);');
        }
        echo '</td>';

        $imageExists = !empty($product->image);
        echo '<td width="29px;" class="' . ((! empty($product->product_attributes) || isset($product->product_attributes)) && !$imageExists ? 'not-available' : '') . '">';
        if ((! empty($product->product_attributes) || isset($product->product_attributes))) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('image_add.png')), [
                'class' => 'add-image-button',
                'title' => 'Neues Bild hochladen bzw. austauschen',
                'data-object-id' => $product->id_product
            ], 'javascript:void(0);');
            echo $this->element('imageUploadForm', [
                'id' => $product->id_product,
                'action' => '/admin/tools/doTmpImageUpload/' . $product->id_product,
                'imageExists' => $imageExists,
                'existingImageSrc' => $imageExists ? $this->Html->getProductImageSrc($product->image->id_image, 'thickbox') : ''
            ]);
        }
        echo '</td>';

        echo '<td>';

        if (! empty($product->product_attributes) || isset($product->product_attributes)) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'product-name-edit-button',
                'title' => 'Name und Beschreibung ändern'
            ], 'javascript:void(0);');
        }

        if (! isset($product->product_attributes)) {
            echo '<span style="float:left;margin-right: 5px;">';
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
                'class' => 'delete-product-attribute-button',
                'title' => 'Variante für Produkt "' . $product->product_lang->name . '" löschen'
            ], 'javascript:void(0);');
            echo '</span>';

            echo '<span style="float:left;">';
            if ($product->product_attribute_shop->default_on == 1) {
                echo $this->Html->image($this->Html->getFamFamFamPath('star.png'), [
                    'title' => 'Diese Variante ist die Standardvariante.'
                ]);
            } else {
                echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('bullet_star.png')), [
                    'class' => 'set-as-default-attribute-button',
                    'title' => 'Als neue Standard-Variante festlegen'
                ], 'javascript:void(0);');
            }
            echo '</span>';
        }

        echo '<span class="name-for-dialog">';
            echo $product->product_lang->name;
        echo '</span>';

        if (! empty($product->product_attributes) || isset($product->product_attributes)) {
            echo '<span data-is-declaration-ok="'.$product->product_lang->is_declaration_ok.'" class="is-declaration-ok-wrapper">' . ($product->product_lang->is_declaration_ok ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>').'</span>';
        }

        // show unity only if product has no attributes and field "unity" is not empty
        if (empty($product->product_attributes)) {
            if (!empty($product->product_lang) && $product->product_lang->unity != '') {
                echo ': ';
                echo '<span class="unity-for-dialog">';
                echo $product->product_lang->unity;
                echo '</span>';
            }
        }

        echo '<span class="description-short-for-dialog">';
        echo $product->product_lang->description_short;
        echo '</span>';

        echo '<span class="description-for-dialog">';
        echo $product->product_lang->description;
        echo '</span>';

        echo '</td>';

        if ($manufacturerId == 'all') {
            echo '<td>';
            if (! empty($product->product_attributes) || isset($product->product_attributes)) {
                echo $this->Html->link(
                    $product->manufacturer->name,
                    $this->Slug->getProductAdmin($product->id_manufacturer)
                );
            }
            echo '</td>';
        }

        echo '<td>';
        if (! empty($product->product_attributes) || isset($product->product_attributes)) {
            echo $this->Form->hidden('Products.selected_categories', [
                'value' => implode(',', $product->selected_categories),
                'id' => 'selected-categories-' . $product->id_product
            ]);
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'product-categories-edit-button',
                'title' => 'Kategorien ändern',
                'data-object-id' => $product->id_product
            ], 'javascript:void(0);');
            if (!isset($product->category)) {
                pr($product);
            }
            echo '<span class="categories-for-dialog">' . join(', ', $product->category->names) . '</span>';
            if (! $product->category->all_products_found) {
                echo ' - <b>Kategorie "Alle Produkte" fehlt!</b>';
            }
        }
        echo '</td>';

        echo '<td class="' . (empty($product->product_attributes) && $product->stock_available->quantity == 0 ? 'not-available' : '') . '">';

        if (empty($product->product_attributes)) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'product-quantity-edit-button',
                'title' => 'Anzahl ändern'
            ], 'javascript:void(0);');
            echo '<span class="quantity-for-dialog">';
            echo $this->Html->formatAsDecimal($product->stock_available->quantity, 0);
            echo '</span>';
        }

        echo '</td>';

        echo '<td class="' . (empty($product->product_attributes) && $product->gross_price == 0 ? 'not-available' : '') . '">';
        echo '<div class="table-cell-wrapper price">';
        if (empty($product->product_attributes)) {
            echo '<span class="price-for-dialog">';
            echo $this->Html->formatAsDecimal($product->gross_price);
            echo '</span>';
        }
        if (empty($product->product_attributes)) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'product-price-edit-button',
                'title' => 'Preis ändern'
            ], 'javascript:void(0);');
        }
        echo '</div>';
        echo '</td>';

        echo '<td>';
        if (! empty($product->product_attributes) || isset($product->product_attributes)) {
            echo $this->Form->hidden('Products.id_tax', [
                'id' => 'tax-id-' . $product->id_product,
                'value' => $product->id_tax
            ]);
            $taxRate = $product->tax->rate;
            echo '<span class="tax-for-dialog">' . ($taxRate != intval($taxRate) ? $this->Html->formatAsDecimal($taxRate, 1) : $this->Html->formatAsDecimal($taxRate, 0)) . '%' . '</span>';
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                'class' => 'product-tax-edit-button',
                'title' => 'Steuer ändern',
                'data-object-id' => $product->id_product
            ], 'javascript:void(0);');
        }
        echo '</td>';

        echo '<td>';
        if (! empty($product->product_attributes) || isset($product->product_attributes)) {
            if (! $product->is_new) {
                echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')) . ' Neu', [
                    'class' => 'icon-with-text change-new-state change-new-state-active',
                    'id' => 'change-new-state-' . $product->id_product,
                    'title' => 'Produkt die nächsten ' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' Tage als "neu" anzeigen?'
                ], 'javascript:void(0);');
            } else {
                echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('accept.png')) . ' Neu', [
                    'class' => 'icon-with-text change-new-state change-new-state-inactive',
                    'id' => 'change-new-state-' . $product->id_product,
                    'title' => 'Produkt nicht mehr als "neu" anzeigen?'
                ], 'javascript:void(0);');
            }
        }
        echo '</td>';

        echo '<td>';
        if (empty($product->product_attributes)) {
            echo '<div class="table-cell-wrapper price">';
            if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || Configure::read('app.isDepositPaymentCashless')) {
                echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                    'class' => 'product-deposit-edit-button',
                    'title' => 'Zum Ändern des Pfands anklicken'
                ], 'javascript:void(0);');
            }
            if ($product->deposit > 0) {
                echo '<span class="deposit-for-dialog">';
                echo $this->Html->formatAsDecimal($product->deposit);
                echo '</span>';
            }
            echo '</div>';
        }
        echo '</td>';

        echo '<td style="text-align: center;padding-left:10px;width:42px;">';

        if ($product->active == 1) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('accept.png')), [
                'class' => 'set-state-to-inactive change-active-state',
                'id' => 'change-active-state-' . $product->id_product,
                'title' => 'Zum Deaktivieren anklicken'
            ], 'javascript:void(0);');
        }

        if ($product->active == '') {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
                'class' => 'set-state-to-active change-active-state',
                'id' => 'change-active-state-' . $product->id_product,
                'title' => 'Zum Aktivieren anklicken'
            ], 'javascript:void(0);');
        }

        echo '</td>';

        echo '<td>';
        if ($product->active && (! empty($product->product_attributes) || isset($product->product_attributes))) {
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('arrow_right.png')), [
                'title' => 'Produkt-Vorschau',
                'target' => '_blank'
            ], $url = $this->Slug->getProductDetail($product->id_product, $product->product_lang->name));
        }
        echo '</td>';

        echo '</tr>';
    }

    echo '<tr>';

    $colspan = 12;
    if ($manufacturerId == 'all') {
        $colspan++;
    }

    echo '<td colspan="'.$colspan.'"><b>' . $i . '</b> Datensätze</td>';
    echo '</tr>';

    echo '</table>';
    ?>    
    
    <div class="sc"></div>
    
</div>

<?php
    // dropdowns and checkboxes for overlays are only rendered once (performance)
    echo $this->Form->control('productAttributeId', ['type' => 'select', 'class' => 'hide', 'label' => '', 'options' => $attributesForDropdown]);
    echo '<div class="categories-checkboxes">';
        echo $this->Form->control('Products.CategoryProducts', [
            'label' => '',
            'multiple' => 'checkbox',
            'options' => $categoriesForSelect
        ]);
        echo '</div>';
        echo '<div class="tax-dropdown-wrapper">';
        echo $this->Form->control('Taxes.id_tax', [
            'type' => 'select',
            'label' => '',
            'options' => $taxesForDropdown,
        ]);
        echo '</div>';
?>

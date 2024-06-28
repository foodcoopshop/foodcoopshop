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
foodcoopshop.SyncProductData = {

    syncProducts : [],

    syncProductsButton : $('.sync-button-wrapper a.sync-products-button'),

    showPreviewButton : $('.sync-button-wrapper a.show-preview-button'),

    everythingAllrightContainer : $('#everything-allright'),

    toggleCleanRowsSelector : '#toggle-clean-rows',

    tmpFlashMessage : '',

    implementedSyncAttributes : [
        {
            name: 'image',
            label: foodcoopshop.LocalizedJs.syncProductData.Image,
            data: 'image.src',
            column: 2
        },
        {
            name: 'name',
            label: foodcoopshop.LocalizedJs.syncProductData.Name,
            additionalInfo: foodcoopshop.LocalizedJs.syncProductData.NameAdditionalInfo,
            data: {
                name: 'unchanged_name',
                unity: 'unity',
                description: 'description',
                description_short: 'description_short',
                is_declaration_ok: 'is_declaration_ok'
            },
            column: 3
        },
        {
            name: 'is_stock_product',
            label: foodcoopshop.LocalizedJs.syncProductData.StockProduct,
            data: 'is_stock_product',
            column: 4
        },
        {
            name: 'quantity',
            label: foodcoopshop.LocalizedJs.syncProductData.Quantity,
            data: {
                'stock_available_quantity': 'stock_available.quantity',
                'stock_available_quantity_limit': 'stock_available.quantity_limit',
                'stock_available_sold_out_limit': 'stock_available.sold_out_limit',
                'stock_available_always_available': 'stock_available.always_available',
                'stock_available_default_quantity_after_sending_order_lists': 'stock_available.default_quantity_after_sending_order_lists',
            },
            column: 5
        },
        {
            name: 'price',
            label: foodcoopshop.LocalizedJs.syncProductData.Price,
            data: {
                gross_price: 'gross_price',
                unit_product_price_incl_per_unit: 'unit.price_incl_per_unit',
                unit_product_name: 'unit.name',
                unit_product_amount: 'unit.amount',
                unit_product_quantity_in_units: 'unit.quantity_in_units',
                unit_product_price_per_unit_enabled: 'unit.price_per_unit_enabled',
                unit_product_use_weight_as_amount: 'unit.use_weight_as_amount',
            },
            column: 6
        },
        {
            name: 'deposit',
            label: foodcoopshop.LocalizedJs.syncProductData.Deposit,
            data: 'deposit',
            column: 7
        },
        {
            name: 'delivery_rhythm',
            label: foodcoopshop.LocalizedJs.syncProductData.DeliveryRhythm,
            data: {
                delivery_rhythm_type: 'delivery_rhythm_type',
                delivery_rhythm_count: 'delivery_rhythm_count',
                delivery_rhythm_first_delivery_day: 'delivery_rhythm_first_delivery_day',
                delivery_rhythm_order_possible_until: 'delivery_rhythm_order_possible_until',
                delivery_rhythm_order_send_order_list_weekday: 'delivery_rhythm_order_send_order_list_weekday',
                delivery_rhythm_order_send_order_list_day: 'delivery_rhythm_order_send_order_list_day'
            },
            column: 8
        },
        {
            name: 'active',
            label: foodcoopshop.LocalizedJs.syncProductData.Status,
            data: 'active',
            column: 9
        }
    ],

    init : function (syncProducts, products) {

        var localStorageCredentialsCount = foodcoopshop.SyncBase.loadCredentialsFromLocalStorage();
        foodcoopshop.SyncBase.showSyncForm();
        foodcoopshop.SyncBase.reformatProductListRows($('table.list'), true);
        this.syncProducts = $.parseJSON(syncProducts);
        this.products = $.parseJSON(products);
        this.bindSyncProductDataButton();
        this.bindShowPreviewButton();

        if (localStorageCredentialsCount > 0) {
            this.showPreviewButton.trigger('click');
        }

    },

    getProductTableHeadElements : function () {
        return  [
            '<input type="checkbox" id="row-marker-all" />',
            foodcoopshop.LocalizedJs.syncProductData.Image,
            foodcoopshop.LocalizedJs.syncProductData.Name,
            foodcoopshop.LocalizedJs.syncProductData.StockProduct,
            foodcoopshop.LocalizedJs.syncProductData.Quantity,
            foodcoopshop.LocalizedJs.syncProductData.Price,
            foodcoopshop.LocalizedJs.syncProductData.Deposit,
            foodcoopshop.LocalizedJs.syncProductData.DeliveryRhythm,
            foodcoopshop.LocalizedJs.syncProductData.Status
        ];
    },

    showEverythingAllrightMessage : function () {
        this.everythingAllrightContainer.show();
    },

    hideEverythingAllrightMessage : function () {
        this.everythingAllrightContainer.hide();
    },

    bindToggleCleanRows : function () {
        $(this.toggleCleanRowsSelector).on('click', function () {
            var checked = $(foodcoopshop.SyncProductData.toggleCleanRowsSelector + ':checked').length > 0;
            var productList = $('table.product-list');
            if (checked) {
                foodcoopshop.SyncProductData.hideDirtyProducts(productList);
            } else {
                foodcoopshop.SyncProductData.showAllProducts(productList);
            }
            foodcoopshop.SyncBase.reformatProductListRows(productList, true);
        });
        $('span.toggle-clean-rows').on('click', function () {
            $(foodcoopshop.SyncProductData.toggleCleanRowsSelector).trigger('click');
        });
    },

    hideDirtyProducts : function (productList) {
        var dirtyProducts = productList.find('tr.ok:not(.dirty)');
        dirtyProducts.hide();
        dirtyProducts.nextUntil('.ok').hide();
    },

    showAllProducts : function (productList) {
        var cleanProducts = productList.find('tr.ok');
        cleanProducts.show();
        cleanProducts.nextUntil('.ok').show();
    },

    getProductTableRows : function (products) {

        var productRows = [];

        for (var product of products) {
            if (product.prepared_sync_products) {
                product.row_class += ' ok';
                var isAttribute = foodcoopshop.SyncProduct.isAttribute(product);
                var hasAttributes = foodcoopshop.SyncProduct.hasAttributes(product);
                var tableData = '<tr class="' + [product.row_class].join(' ') + '" data-product-id="' + product.id_product + '">';
                tableData += '<td class="sync-checkbox"><input type="checkbox" class="row-marker" disabled="disabled" /></td>';
                tableData += '<td class="image">';
                if (!isAttribute && product.image && product.image.src) {
                    tableData += foodcoopshop.SyncProduct.getProductImageTag(product.image.src);
                }
                tableData += '</td>';
                tableData += '<td class="name">';
                tableData += foodcoopshop.SyncProduct.getProductNameWithUnity(product, isAttribute, hasAttributes);
                if (!isAttribute) {
                    tableData += foodcoopshop.SyncProduct.getIsDeclarationOkString(product.is_declaration_ok);
                }
                tableData += '</td>';
                tableData += '<td class="is_stock_product">';
                if (!isAttribute) {
                    tableData += foodcoopshop.SyncProduct.getIsStockProductString(product.is_stock_product);
                }
                tableData += '</td>';
                tableData += '<td class="quantity">';
                if (isAttribute || !hasAttributes) {
                    tableData += foodcoopshop.SyncProduct.getQuantityString(
                        product.is_stock_product,
                        product.stock_available.quantity,
                        product.stock_available.quantity_limit,
                        product.stock_available.sold_out_limit,
                        product.stock_available.always_available,
                        product.stock_available.default_quantity_after_sending_order_lists);
                }
                tableData += '</td>';
                tableData += '<td class="price">';
                if (isAttribute || !hasAttributes) {
                    if (product.unit && product.unit.price_per_unit_enabled) {
                        tableData += foodcoopshop.SyncProduct.getPricePerUnitBaseInfo(
                            product.unit.price_incl_per_unit,
                            product.unit.name,
                            product.unit.amount,
                            product.unit.quantity_in_units
                        );
                    } else {
                        tableData += foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(product.gross_price));
                    }
                }
                tableData += '</td>';
                tableData += '<td class="deposit">' + (product.deposit > 0 ? foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(product.deposit)) : '') + '</td>';
                tableData += '<td class="delivery_rhythm">';
                if (!isAttribute) {
                    tableData += foodcoopshop.SyncProduct.getDeliveryRhythmString(
                        product.delivery_rhythm_string,
                        product.is_stock_product,
                        product.delivery_rhythm_type,
                        product.delivery_rhythm_count,
                        product.delivery_rhythm_first_delivery_day,
                        product.delivery_rhythm_order_possible_until,
                        product.last_order_weekday,
                        product.delivery_rhythm_send_order_list_day
                    );
                }
                tableData += '</td>';
                tableData += '<td class="active">' + (!isAttribute ? (product.active ? '<i class="fas fa-check ok"></i>' : '<i class="fas fa-times not-ok"></i>') : '') + '</td>';
                tableData += '</tr>';

                if (product.prepared_sync_products) {
                    for (var syncProduct of product.prepared_sync_products) {
                        tableData += foodcoopshop.SyncProductData.getHtmlForAssignedProduct(syncProduct.domain, syncProduct.name, syncProduct.remoteProductId);
                    }
                }

                productRows.push(tableData);
            }

        }

        return productRows;

    },

    getHtmlForAssignedProduct : function (syncServer, productName, remoteProductId) {

        var html = '<tr class="assigned-products" data-domain="' + syncServer + '" data-remote-product-id="' + remoteProductId + '">';
        html += '<td></td>'; // for checkboxes
        html += '<td class="image"></td>';
        html += '<td class="name">';
        html += '<span class="app-name"></span>';
        html += '<span class="product-name">' + productName + '</span>';
        html += '</td>';
        html += '<td class="is_stock_product"></td>';
        html += '<td class="quantity"></td>';
        html += '<td class="price"></td>';
        html += '<td class="deposit"></td>';
        html += '<td class="delivery_rhythm"></td>';
        html += '<td class="active"></td>';
        html += '</tr>';
        return html;
    },


    showLocalProductList : function () {

        var productTableRows = foodcoopshop.SyncProductData.getProductTableRows(this.products);

        var productListHtml = '<table class="product-list list" style="display: table;">';
        productListHtml += '<tr>';
        productListHtml += '<th>' + foodcoopshop.SyncProductData.getProductTableHeadElements().join('</th><th>');

        var columnCount = foodcoopshop.SyncProductData.getProductTableHeadElements().length;
        productListHtml += '<tr class="horizontal-checkboxes">' + new Array(++columnCount).join('<td></td>') + '</tr>';

        productListHtml += '</th></tr>' + productTableRows.join('');
        productListHtml += '</table>';

        var productList = $('body .product-list');
        productList.html(productListHtml);

        // add horizontal checkboxes
        $(foodcoopshop.SyncProductData.implementedSyncAttributes).each(function () {
            var checkboxHtml = '<input type="checkbox" data-name="' + $(this)[0].column + '"/>';
            if ($(this)[0].additionalInfo) {
                checkboxHtml += '<span>' + $(this)[0].additionalInfo + '</span>';
            }
            productList.find('tr.horizontal-checkboxes').find('td:nth-of-type(' + $(this)[0].column + ')').append(checkboxHtml);
        });

        foodcoopshop.Admin.initRowMarkerAll();

        foodcoopshop.SyncBase.reformatProductListRows(productList, true);
        foodcoopshop.SyncProductData.bindToggleCleanRows();

    },

    bindShowPreviewButton : function () {

        this.showPreviewButton.on('click', function () {

            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-refresh');
            foodcoopshop.Helper.disableButton($(this));

            var horizontalCheckboxes = foodcoopshop.SyncProductData.getHorizontalCheckboxes();
            horizontalCheckboxes.removeClass('error');

            var verticalCheckboxes = foodcoopshop.SyncProductData.getVerticalCheckboxes();
            verticalCheckboxes.removeClass('error');

            // add loader
            var nonEmptyLoginForms = foodcoopshop.SyncBase.getNonEmptyLoginForms(foodcoopshop.SyncBase.getLoginForms());
            var postData = {};
            nonEmptyLoginForms.each(function () {
                var productList = $('div.product-list.remote[data-sync-domain=\'' + $(this).data('sync-domain') +'\']');
                foodcoopshop.SyncProducts.addLoaderToProductList(productList);
                productList.html('');
                postData[$(this).data('sync-domain')] = {};
            });
            foodcoopshop.SyncBase.doApiCall('/api/getProducts.json', 'POST', postData, foodcoopshop.SyncProductData.renderPreviewList);

        });

    },

    /**
     * rounds correctly and returns number with two digits - .toFixed(2) does not round correctly
     */
    roundToTwo : function (num) {
        return +(Math.round(num + 'e+2') + 'e-2');
    },

    renderPreviewList : function (response) {

        if (foodcoopshop.SyncProductData.tmpFlashMessage != '') {
            foodcoopshop.Helper.showSuccessMessage(foodcoopshop.SyncProductData.tmpFlashMessage);
        }

        var productRows = $('table.product-list tr.assigned-products[data-domain=\'' + response.app.domain + '\']');
        productRows.show();
        productRows.find('td').removeClass('dirty');

        var productList = $('table.product-list');

        foodcoopshop.SyncProductData.showAllProducts(productList);

        productRows.each(function () {

            for (var product of response.products) {

                if (product.id_product == $(this).data('remoteProductId')) {

                    var localProductRow = $(this).prevAll('.ok').first();
                    var localProductId = localProductRow.data('productId');

                    var localProduct = null;
                    for(var tmpLocalProduct of foodcoopshop.SyncProductData.products) {
                        if (tmpLocalProduct.id_product == localProductId) {
                            localProduct = tmpLocalProduct;
                            continue;
                        }
                    }

                    var isAttribute = foodcoopshop.SyncProduct.isAttribute(product);
                    var hasAttributes = foodcoopshop.SyncProduct.hasAttributes(product);

                    // image
                    if (!isAttribute) {
                        if (product.image && product.image.src) {
                            $(this).find('td.image').html(foodcoopshop.SyncProduct.getProductImageTag(product.image.src));
                        }
                        var localProductImageHash = localProduct.image && localProduct.image.hash || '';
                        var remoteProductImageHash = product.image && product.image.hash || '';
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.image', remoteProductImageHash, localProductImageHash, $(this), localProductRow);
                    }

                    // name
                    var remoteProductName = foodcoopshop.SyncProduct.getProductNameWithUnity(product, isAttribute, hasAttributes);

                    if (!isAttribute) {
                        // check all name fields to set the field dirty or not
                        var localProductNameFields = '';
                        var remoteProductNameFields = '';
                        $(foodcoopshop.SyncProductData.implementedSyncAttributes).each(function() {
                            if ($(this)[0].name == 'name') {
                                var keys = Object.keys($(this)[0].data);
                                for(var key in keys) {
                                    localProductNameFields += product[keys[key]];
                                    remoteProductNameFields += localProduct[keys[key]];
                                }
                            }
                        });
                        remoteProductName += foodcoopshop.SyncProduct.getIsDeclarationOkString(product.is_declaration_ok);
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.name', remoteProductNameFields, localProductNameFields, $(this), localProductRow);
                    }
                    $(this).find('td.name').html(remoteProductName);
                    $(this).find('td.name').prepend('<span class="app-name">' + response.app.name + ': </span>');

                    // is_stock_product
                    if (!isAttribute) {
                        var remoteProductIsStockProductString = foodcoopshop.SyncProduct.getIsStockProductString(product.is_stock_product);
                        var localProductIsStockProductString = foodcoopshop.SyncProduct.getIsStockProductString(localProduct.is_stock_product);
                        $(this).find('td.is_stock_product').html(remoteProductIsStockProductString);
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.is_stock_product', remoteProductIsStockProductString, localProductIsStockProductString, $(this), localProductRow);
                    }

                    // quantity
                    if (!hasAttributes) {
                        var remoteProductQuantityString = foodcoopshop.SyncProduct.getQuantityString(
                            product.is_stock_product,
                            product.stock_available.quantity,
                            product.stock_available.quantity_limit,
                            product.stock_available.sold_out_limit,
                            product.stock_available.always_available,
                            product.stock_available.default_quantity_after_sending_order_lists);
                        var localProductQuantityString = foodcoopshop.SyncProduct.getQuantityString(
                            localProduct.is_stock_product,
                            localProduct.stock_available.quantity,
                            localProduct.stock_available.quantity_limit,
                            localProduct.stock_available.sold_out_limit,
                            localProduct.stock_available.always_available,
                            localProduct.stock_available.default_quantity_after_sending_order_lists);
                        $(this).find('td.quantity').html(remoteProductQuantityString);
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.quantity', remoteProductQuantityString, localProductQuantityString, $(this), localProductRow);
                    }

                    // price
                    if (isAttribute || !hasAttributes) {

                        var remotePriceAsString;
                        var localPriceAsString;

                        var localProductGrossPrice = parseFloat(localProduct.gross_price);
                        var remoteProductGrossPrice = parseFloat(product.gross_price);

                        if (product.unit && product.unit.price_per_unit_enabled) {
                            remoteProductGrossPrice = product.unit.price_incl_per_unit;
                        }
                        if (localProduct.unit && localProduct.unit.price_per_unit_enabled) {
                            localProductGrossPrice = localProduct.unit.price_incl_per_unit;
                        }

                        // if remote manufacturer has variable member fee enabled, compare local price including the remote variable member fee
                        if (response.app.variableMemberFee > 0) {
                            localProductGrossPrice = foodcoopshop.SyncProductData.roundToTwo(localProductGrossPrice + (localProductGrossPrice * response.app.variableMemberFee / 100));
                        }

                        remotePriceAsString = foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(remoteProductGrossPrice));
                        if (product.unit && product.unit && product.unit.price_per_unit_enabled) {
                            remotePriceAsString = foodcoopshop.SyncProduct.getPricePerUnitBaseInfo(
                                remoteProductGrossPrice,
                                product.unit.name,
                                product.unit.amount,
                                product.unit.quantity_in_units
                            );
                        }

                        localPriceAsString = foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(localProductGrossPrice));
                        if (localProduct.unit && localProduct.unit.price_per_unit_enabled) {
                            localPriceAsString = foodcoopshop.SyncProduct.getPricePerUnitBaseInfo(
                                localProductGrossPrice,
                                localProduct.unit.name,
                                localProduct.unit.amount,
                                localProduct.unit.quantity_in_units
                            );
                        }

                        var additionalInfo = '';
                        if (response.app.variableMemberFee > 0) {
                            additionalInfo += ' (' + response.app.variableMemberFee + '%)';
                        }
                        $(this).find('td.price').html(remotePriceAsString + additionalInfo);
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.price', remotePriceAsString, localPriceAsString, $(this), localProductRow);
                    }

                    // deposit
                    if (!hasAttributes) {
                        var remoteDeposit = (localProduct.deposit > 0 ? foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(product.deposit)) : '');
                        $(this).find('td.deposit').html(remoteDeposit);
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.deposit', product.deposit, localProduct.deposit, $(this), localProductRow);
                    }

                    // delivery_rhythm
                    if (!isAttribute) {
                        var remoteProductDeliveryRhythmString = foodcoopshop.SyncProduct.getDeliveryRhythmString(
                            product.delivery_rhythm_string,
                            product.is_stock_product,
                            product.delivery_rhythm_type,
                            product.delivery_rhythm_count,
                            product.delivery_rhythm_first_delivery_day,
                            product.delivery_rhythm_order_possible_until,
                            product.last_order_weekday,
                            product.delivery_rhythm_send_order_list_day
                        );
                        var localProductDeliveryRhythmString = foodcoopshop.SyncProduct.getDeliveryRhythmString(
                            localProduct.delivery_rhythm_string,
                            localProduct.is_stock_product,
                            localProduct.delivery_rhythm_type,
                            localProduct.delivery_rhythm_count,
                            localProduct.delivery_rhythm_first_delivery_day,
                            localProduct.delivery_rhythm_order_possible_until,
                            localProduct.last_order_weekday,
                            localProduct.delivery_rhythm_send_order_list_day
                        );
                        $(this).find('td.delivery_rhythm').html(remoteProductDeliveryRhythmString);
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.delivery_rhythm', remoteProductDeliveryRhythmString, localProductDeliveryRhythmString, $(this), localProductRow);
                    }

                    // active
                    var remoteActive = (!isAttribute ? (product.active ? '<i class="fas fa-check ok"></i>' : '<i class="fas fa-times not-ok"></i>') : '');
                    $(this).find('td.active').html(remoteActive);
                    foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.active', product.active, localProduct.active, $(this), localProductRow);

                    var checkbox = localProductRow.find('.sync-checkbox input');
                    if (localProductRow.hasClass('dirty')) {
                        checkbox.prop('checked', true);
                        checkbox.prop('disabled', false);
                    } else {
                        checkbox.prop('checked', false);
                        checkbox.prop('disabled', true);
                    }

                    continue;

                }
            }

        });

        var localProductRow = productList.find('tr.ok');
        var dirtyRowFound = false;
        localProductRow.each(function () {
            var dirtyChildFound = false;
            $(this).nextUntil('.ok', '.assigned-products').find('td').each(function () {
                dirtyChildFound |= $(this).hasClass('dirty');
                dirtyRowFound |= $(this).hasClass('dirty');
            });
            if (!dirtyChildFound) {
                $(this).removeClass('dirty');
            }
        });
        if (dirtyRowFound) {
            foodcoopshop.SyncProductData.hideEverythingAllrightMessage();
        } else {
            foodcoopshop.SyncProductData.showEverythingAllrightMessage();
        }

        foodcoopshop.Helper.enableButton(foodcoopshop.SyncProductData.syncProductsButton);

        if ($(foodcoopshop.SyncProductData.toggleCleanRowsSelector + ':checked').length > 0) {
            foodcoopshop.SyncProductData.hideDirtyProducts(productList);
        }
        foodcoopshop.SyncBase.reformatProductListRows(productList, true);

    },

    doIsAttributeDirtyActions : function (attributeCellSelector, remoteValue, localValue, remoteProductRow, localProductRow) {
        if (remoteValue != localValue) {
            localProductRow.addClass('dirty');
            remoteProductRow.find(attributeCellSelector).addClass('dirty');
            //            console.log(attributeCellSelector + ' - productId: ' + localProductRow.data('productId') + ': ' + remoteValue + ' / ' + localValue);
        }
    },

    getHorizontalCheckboxes : function () {
        return $('table.list tr.horizontal-checkboxes');
    },

    getVerticalCheckboxes : function () {
        return $('table.list tr.ok td.sync-checkbox');
    },

    bindSyncProductDataButton : function () {

        foodcoopshop.Helper.disableButton(foodcoopshop.SyncProductData.syncProductsButton);

        foodcoopshop.SyncProductData.syncProductsButton.on('click', function () {

            var horizontalCheckboxes = foodcoopshop.SyncProductData.getHorizontalCheckboxes();
            horizontalCheckboxes.removeClass('error');

            var verticalCheckboxes = foodcoopshop.SyncProductData.getVerticalCheckboxes();
            verticalCheckboxes.removeClass('error');

            var preparedData = $.extend(true, {}, foodcoopshop.SyncProductData.syncProducts); // $.extend: no reference
            var domains = Object.keys(preparedData);

            var postData = {};
            var checkedAttributeLabels = [];
            $(domains).each(function (key, domain) {
                postData[domain] = [];
                var i = 0;
                $(preparedData[domain]).each(function (key, product) {
                    var attributes = {};
                    $(foodcoopshop.SyncProductData.implementedSyncAttributes).each(function () {
                        var syncAttribute = $('table.list tr.horizontal-checkboxes td:nth-of-type('+$(this)[0].column+') input[type="checkbox"]:checked').length > 0;
                        if (syncAttribute) {
                            var tableRow = $('table tr[data-product-id=\'' + product.localProductId + '\']');
                            var syncProduct = tableRow.find('td.sync-checkbox input[type="checkbox"]:checked').length > 0;
                            if (syncProduct) {
                                var attributeName = $(this)[0].name;
                                checkedAttributeLabels.push($(this)[0].label);
                                for (syncProduct of foodcoopshop.SyncProductData.products) {
                                    if (product.localProductId == syncProduct.id_product) {
                                        var dataIndex = $(this)[0].data;
                                        var newValue;
                                        if ($.type(dataIndex) == 'string') {
                                            newValue = foodcoopshop.Helper.resolveIndex(dataIndex, syncProduct);
                                            if ($.inArray(attributeName, ['active', 'is_stock_product']) !== -1) {
                                                newValue = newValue ? 1 : 0; // transform true or false to 1 or 0
                                            }
                                            if (attributeName == 'deposit') {
                                                newValue = newValue === null ? 0 : newValue; // transform null to 0
                                            }
                                            if (attributeName == 'image') {
                                                newValue = newValue === undefined ? 'no-image' : newValue; // transform null to 0
                                            }
                                            attributes[attributeName] = newValue;
                                        }
                                        // if data is an object (eg. quantity)
                                        if ($.type(dataIndex) == 'object') {
                                            attributes[attributeName] = [];
                                            var dataIndexKeys = Object.keys(dataIndex);
                                            newValue = [];
                                            $(dataIndexKeys).each(function (index, dataI) {
                                                var newIndex = dataIndexKeys[index];
                                                newValue[newIndex] = foodcoopshop.Helper.resolveIndex(dataIndex[dataI], syncProduct);

                                                if ($.type(newValue[newIndex] == 'string')) {
                                                    // converting delivery_rhythm_first_delivery_day and delivery_rhythm_order_possible_until to 'YYYY-mm-dd'
                                                    var regex = new RegExp(/00:00:00+00:00/);
                                                    if (regex.test(newValue[newIndex])) {
                                                        newValue[newIndex] = newValue[newIndex].substr(0, 10);
                                                    }
                                                    if ($.inArray(dataI, ['stock_available_always_available']) !== -1) {
                                                        newValue[newIndex] = newValue[newIndex] ? 1 : 0; // transform true or false to 1 or 0
                                                    }
                                                }

                                            });
                                            attributes[attributeName] = $.extend({}, newValue); // to post data correctly convert array to object
                                        }
                                    }
                                }
                                attributes.remoteProductId = product.remoteProductId;
                            }
                        }
                    });

                    if (Object.keys(attributes).length !== 0) {
                        postData[domain][i] = attributes;
                        i++;
                    }

                });

            });

            var checkedProducts = verticalCheckboxes.find('input[type="checkbox"]:checked');

            var checkedProductsCount = 0;
            var checkedAttributesCount = 0;
            $(checkedProducts).each(function (i, product) {
                var isAttribute = $(product).closest('tr').attr('class').match(/sub-row/);
                if (isAttribute) {
                    checkedAttributesCount++;
                } else {
                    checkedProductsCount++;
                }
            });

            if (checkedProductsCount == 0 && checkedAttributesCount == 0) {
                foodcoopshop.Helper.showOrAppendErrorMessage(foodcoopshop.LocalizedJs.syncProductData.NoProductsOrAttributesSelected);
                verticalCheckboxes.addClass('error');
                return;
            }

            checkedAttributeLabels = foodcoopshop.Helper.unique(checkedAttributeLabels);
            if (checkedAttributeLabels.length == 0) {
                foodcoopshop.Helper.showOrAppendErrorMessage(foodcoopshop.LocalizedJs.syncProductData.NoProductDataSelected);
                horizontalCheckboxes.addClass('error');
                return;
            }

            var loginForms = foodcoopshop.SyncBase.getLoginForms();
            var nonEmptyLoginForms = foodcoopshop.SyncBase.getNonEmptyLoginForms(loginForms);
            var domains2sync = [];
            nonEmptyLoginForms.each(function () {
                domains2sync.push($(this).data('sync-domain'));
            });

            if (domains2sync.length == 0) {
                foodcoopshop.Helper.showOrAppendErrorMessage(foodcoopshop.LocalizedJs.syncProductData.PleaseEnterYourCredentials);
                return;
            }

            foodcoopshop.ModalSyncProductData.init(
                postData,
                checkedAttributeLabels,
                checkedProductsCount,
                checkedAttributesCount,
                domains2sync
            );

        });

    },

    onProductDataUpdated : function (response) {
        foodcoopshop.Modal.destroy($('#modal-sync-product-data'));
        var flashMessage = '<b>' + response.app.name + '</b>: ' + response.msg;
        foodcoopshop.Helper.showOrAppendSuccessMessage(flashMessage);
        foodcoopshop.SyncProductData.tmpFlashMessage = flashMessage;
        // the following ajax request always removes all flash messages
        foodcoopshop.SyncProductData.showPreviewButton.trigger('click');
    }

};

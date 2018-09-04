/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
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
            name: 'name',
            label: 'Name',
            additionalInfo: 'Name, Einheit, kurze Beschreibung und Beschreibung <i class="fa fa-file-text-o"></i>',
            data: {
                name: 'unchanged_name',
                unity: 'unity',
                description: 'description',
                description_short: 'description_short'
            },
            column: 2
        },
        {
            name: 'quantity',
            label: 'Anzahl',
            data: 'stock_available.quantity',
            column: 3
        },
        {
            name: 'price',
            label: 'Preis',
            data: 'gross_price',
            column: 4
        },
        {
            name: 'deposit',
            label: 'Pfand',
            data: 'deposit',
            column: 5
        },
        {
            name: 'active',
            label: 'Status',
            data: 'active',
            column: 6
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
        return  ['<input type="checkbox" id="row-marker-all" />', '<span class="name">Name</span><span class="toggle-clean-rows">nur Produkte mit Abweichungen anzeigen</span><input type="checkbox" checked="checked" id="toggle-clean-rows" />', 'Anzahl', 'Preis', 'Pfand', 'Status'];
    },

    showEverythingAllrightMessage : function () {
        this.everythingAllrightContainer.show();
    },

    hideEverythingAllrightMessage : function () {
        this.everythingAllrightContainer.hide();
    },

    cleanDescriptionField : function (field) {
        field = $('<div/>').html(field).text(); // replace html special chars
        field = field.replace(/\u00a0/g, ' '); // replace &nbsp; with ' '
        field = field.replace(/(?:\r\n|\r|\n)/g, '<br />'); // replace all linefeeds (for edge)
        field = field.replace(/"/g, '\''); // replace double quotes
        return field;
    },

    getDescriptionsAsString : function (description_short, description) {
        var result = '';
        if (description_short != '') {
            result += '<b>Kurze Beschreibung</b><br />';
            result += '<p>' + this.cleanDescriptionField(description_short) + '</p>';
        }
        if (description != '') {
            result += '<b>Beschreibung</b><br />';
            result += '<p>' + this.cleanDescriptionField(description) + '</p>';
        }
        return result;
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

        var i = 0;
        var productRows = [];

        for (var product of products) {
            if (product.prepared_sync_products) {
                product.row_class += ' ok';
                var isAttribute = foodcoopshop.SyncProduct.isAttribute(product);
                var hasAttributes = foodcoopshop.SyncProduct.hasAttributes(product);
                var tableData = '<tr class="' + [product.row_class].join(' ') + '" data-product-id="' + product.id_product + '">';
                tableData += '<td class="sync-checkbox"><input type="checkbox" class="row-marker" disabled="disabled" /></td>';
                tableData += '<td class="name">' + foodcoopshop.SyncProduct.getProductNameWithUnity(product, isAttribute, hasAttributes);
                var descriptionAsTitle = foodcoopshop.SyncProductData.getDescriptionsAsString(product.description_short, product.description);
                if (!isAttribute && descriptionAsTitle != '') {
                    tableData += '<i title="' + descriptionAsTitle + '" class="fa fa-file-text-o description"></i>';
                }
                tableData += '</td>';
                tableData += '<td class="quantity">' + (isAttribute || !hasAttributes ? product.stock_available.quantity : '') + '</td>';
                tableData += '<td class="price">' + (isAttribute || !hasAttributes ? foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(product.gross_price)) : '') + '</td>';
                tableData += '<td class="deposit">' + (product.deposit > 0 ? foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(product.deposit)) : '') + '</td>';
                tableData += '<td class="active">' + (!isAttribute ? (product.active ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>') : '') + '</td>';
                tableData += '</tr>';

                if (product.prepared_sync_products) {
                    for (var syncProduct of product.prepared_sync_products) {
                        tableData += foodcoopshop.SyncProductData.getHtmlForAssignedProduct(syncProduct.domain, syncProduct.name, syncProduct.remoteProductId);
                    }
                }

                productRows.push(tableData);
            }

            i++;
        }

        return productRows;

    },

    getHtmlForAssignedProduct : function (syncServer, productName, remoteProductId) {

        var html = '<tr class="assigned-products" data-domain="' + syncServer + '" data-remote-product-id="' + remoteProductId + '">';
        html += '<td></td>'; // empty for checkboxes
        html += '<td class="name">';
        html += '<span class="app-name"></span>';
        html += '<span class="product-name">' + productName + '</span>';
        html += '</td>';
        html += '<td class="quantity"></td>';
        html += '<td class="price"></td>';
        html += '<td class="deposit"></td>';
        html += '<td class="active"></td>';
        html += '</td>';
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

        foodcoopshop.SyncBase.initTooltip('tr.main-product i.description');

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
                    var localProduct = $(this).prevAll('.ok').first();

                    var isAttribute = foodcoopshop.SyncProduct.isAttribute(product);
                    var hasAttributes = foodcoopshop.SyncProduct.hasAttributes(product);

                    // name
                    var remoteProductName = foodcoopshop.SyncProduct.getProductNameWithUnity(product, isAttribute, hasAttributes);

                    var descriptionAsTitle = foodcoopshop.SyncProductData.getDescriptionsAsString(
                        product.description_short,
                        product.description
                    );
                    if (!isAttribute && descriptionAsTitle != '') {
                        remoteProductName += '<i title="' + descriptionAsTitle + '" class="fa fa-file-text-o description"></i>';
                    }

                    if (!isAttribute) {
                        // different attribute names would cause a difference that does not make the record dirty
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.name', remoteProductName, localProduct, $(this));
                    } else {
                        $(this).find('td.name').html(remoteProductName);
                    }
                    $(this).find('td.name').prepend('<span class="app-name">' + response.app.name + ': </span>');

                    // quantity
                    if (!hasAttributes) {
                        var quantity = product.stock_available.quantity;
                        foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.quantity', quantity, localProduct, $(this));
                    }

                    // price
                    if (!hasAttributes) {
                        if (response.app.variableMemberFee > 0) {
                            // if remote manufacturer has variable member fee, compare local price and add remote variable member fee
                            var localPriceAsFloat = foodcoopshop.Helper.getCurrencyAsFloat(localProduct.find('td.price').html());
                            var localPriceIncludingRemoteVariableMemberFee = foodcoopshop.SyncProductData.roundToTwo(localPriceAsFloat + (localPriceAsFloat * response.app.variableMemberFee / 100));
                            var remoteGrossPriceAsFloat = parseFloat(product.gross_price);
                            if (remoteGrossPriceAsFloat != localPriceIncludingRemoteVariableMemberFee) {
                                localProduct.addClass('dirty');
                                $(this).find('td.price').addClass('dirty');
                            }
                            var variableMemberFeeInfo = ' (' + response.app.variableMemberFee + '%)';
                            $(this).find('td.price').html(foodcoopshop.Helper.formatFloatAsCurrency(remoteGrossPriceAsFloat) + variableMemberFeeInfo);
                        } else {
                            var price = foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(product.gross_price));
                            foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.price', price, localProduct, $(this));
                        }
                    }

                    // deposit
                    var deposit = (product.deposit > 0 ? foodcoopshop.Helper.formatFloatAsCurrency(parseFloat(product.deposit)) : '');
                    foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.deposit', deposit, localProduct, $(this));

                    // active
                    var active = (!isAttribute ? (product.active ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>') : '');
                    foodcoopshop.SyncProductData.doIsAttributeDirtyActions('td.active', active, localProduct, $(this));

                    var checkbox = localProduct.find('.sync-checkbox input');
                    if (localProduct.hasClass('dirty')) {
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

        foodcoopshop.SyncBase.initTooltip('tr.assigned-products i.description');
        foodcoopshop.Helper.enableButton(foodcoopshop.SyncProductData.syncProductsButton);

        if ($(foodcoopshop.SyncProductData.toggleCleanRowsSelector + ':checked').length > 0) {
            foodcoopshop.SyncProductData.hideDirtyProducts(productList);
        }
        foodcoopshop.SyncBase.reformatProductListRows(productList, true);

    },

    doIsAttributeDirtyActions : function (attributeCellSelector, remoteValue, localProductRow, remoteProductRow) {
        remoteProductRow.find(attributeCellSelector).html(remoteValue);
        if (remoteValue != localProductRow.find(attributeCellSelector).html()) {
            localProductRow.addClass('dirty');
            remoteProductRow.find(attributeCellSelector).addClass('dirty');
            //          console.log(localProductRow.find(attributeCellSelector).html());
            //          console.log(remoteValue);
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
                                            if (attributeName == 'active') {
                                                newValue = newValue ? 1 : 0; // transform true or false to 1 or 0
                                            }
                                            if (attributeName == 'deposit') {
                                                newValue = newValue === null ? 0 : newValue; // transform null to 0
                                            }
                                            attributes[attributeName] = newValue;
                                        }
                                        if ($.type(dataIndex) == 'object') {
                                            attributes[attributeName] = [];
                                            var dataIndexKeys = Object.keys(dataIndex);
                                            newValue = [];
                                            $(dataIndexKeys).each(function (index, dataI) {
                                                var newIndex = dataIndexKeys[index];
                                                newValue[newIndex] = foodcoopshop.Helper.resolveIndex(dataIndex[dataI], syncProduct);
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
                foodcoopshop.Helper.showOrAppendErrorMessage('Es sind keine Produkte oder Varianten ausgewählt.');
                verticalCheckboxes.addClass('error');
                return;
            }

            checkedAttributeLabels = $.unique(checkedAttributeLabels);
            if (checkedAttributeLabels.length == 0) {
                foodcoopshop.Helper.showOrAppendErrorMessage('Es sind keine Produktdaten ausgewählt.');
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
                foodcoopshop.Helper.showOrAppendErrorMessage('Bitte gib deine Login-Daten ein.');
                return;
            }

            var htmlCode = '<p>Möchtest du die Daten <b>' + checkedAttributeLabels.join(', ');
            htmlCode += '</b> von <b>';
            htmlCode += checkedProductsCount + ' ' + (checkedProductsCount == 1 ? 'Produkt' : 'Produkten');
            if (checkedAttributesCount > 0) {
                htmlCode += ' und ';
                htmlCode += checkedAttributesCount + ' ' + (checkedAttributesCount == 1 ? 'Variante' : 'Varianten');
            }
            htmlCode += '</b> wirklich auf folgende FoodCoops übertragen?</p><p>';
            htmlCode += '<p>' + domains2sync.join('<br />') + '</p>';
            htmlCode += '<b class="negative">Diese Aktion kann nicht rückgängig gemacht werden!</b></p>';
            htmlCode += '<img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';

            $('<div></div>').appendTo('body')
                .html(htmlCode)
                .dialog({
                    modal: true,
                    title: 'Wirklich synchronisieren?',
                    autoOpen: true,
                    width: 450,
                    resizable: false,
                    buttons: {
                        'Abbrechen': function (button) {
                            $(this).dialog('close');
                        },
                        'Ja': function () {
                            $('.ui-dialog .ajax-loader').show();
                            $('.ui-dialog button').attr('disabled', 'disabled');
                            foodcoopshop.SyncBase.doApiCall('/api/updateProducts.json', 'POST', postData, foodcoopshop.SyncProductData.onProductDataUpdated);
                        }
                    },
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });

        });

    },

    onProductDataUpdated : function (response) {
        $('.ui-dialog-content').dialog('close');
        var flashMessage = '<b>' + response.app.name + '</b>: ' + response.msg;
        foodcoopshop.Helper.showOrAppendSuccessMessage(flashMessage);
        foodcoopshop.SyncProductData.tmpFlashMessage = flashMessage;
        // the following ajax request always removes all flash messages
        foodcoopshop.SyncProductData.showPreviewButton.trigger('click');
    }

};

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
foodcoopshop.SyncProducts = {

    init : function () {
        var localStorageCredentialsCount = foodcoopshop.SyncBase.loadCredentialsFromLocalStorage();
        this.bindLoadRemoteProductsButton();
        this.changeProductListWidth(foodcoopshop.SyncBase.getLoginForms().length);
        if (localStorageCredentialsCount > 0) {
            $('.sync-button-wrapper a.btn-success').trigger('click');
        }
    },

    addLoaderToProductList : function (productList) {
        productList.addClass('loader');
    },

    removeLoaderFromProductList : function (productList) {
        productList.removeClass('loader');
    },

    bindLoadRemoteProductsButton : function () {

        $('.sync-button-wrapper a.btn-success').on('click', function () {

            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-refresh');
            foodcoopshop.Helper.disableButton($(this));

            // add loader
            var nonEmptyLoginForms = foodcoopshop.SyncBase.getNonEmptyLoginForms(foodcoopshop.SyncBase.getLoginForms());
            var postData = {};
            nonEmptyLoginForms.each(function () {
                var productList = $('div.product-list.remote[data-sync-domain=\'' + $(this).data('sync-domain') +'\']');
                foodcoopshop.SyncProducts.addLoaderToProductList(productList);
                productList.html('');
                postData[$(this).data('sync-domain')] = {};
            });
            foodcoopshop.SyncBase.doApiCall('/api/getProducts.json', 'POST', postData, foodcoopshop.SyncProducts.renderProductList);
        });

    },

    showLocalProductList : function (products) {
        products = $.parseJSON(products);
        this.renderProductList(products, 'local');
    },

    getProductTableHeadElements : function () {
        return [
            foodcoopshop.LocalizedJs.syncProducts.Id,
            foodcoopshop.LocalizedJs.syncProducts.Product
        ];
    },

    getProductTableRows : function (products) {

        var i = 0;
        var productRows = [];

        for (var product of products) {
            var isAttribute = foodcoopshop.SyncProduct.isAttribute(product);
            var hasAttributes = foodcoopshop.SyncProduct.hasAttributes(product);
            if (product.prepared_sync_products) {
                product.row_class += ' ok';
            }
            var tableData = '<tr class="' + [product.row_class].join(' ') + '" data-product-id="' + product.id_product + '">';
            tableData += '<td>' + product.id_product + '</td>';
            tableData += '<td>' + foodcoopshop.SyncProduct.getProductNameWithUnity(product, isAttribute, hasAttributes);
            if (product.prepared_sync_products) {
                for (var syncProduct of product.prepared_sync_products) {
                    tableData += foodcoopshop.SyncProducts.getHtmlForAssignedProduct(syncProduct.domain, syncProduct.name, syncProduct.remoteProductId, syncProduct.appName);
                }
            }
            tableData +=  '</td>';
            tableData += '</tr>';
            productRows.push(tableData);
        }
        i++;

        return productRows;

    },

    renderProductList : function (response, server) {

        server = server || 'remote';

        foodcoopshop.SyncBase.resetForm();

        var productRows = foodcoopshop.SyncProducts.getProductTableRows(response.products);
        var productListHtml = '<b data-sync-domain="' + response.app.domain + '" class="app-name">' + response.app.name + '</b>';
        productListHtml += '<table class="sync-list list" style="display: table;">';
        productListHtml += '<tr><th>' + foodcoopshop.SyncProducts.getProductTableHeadElements().join('</th><th>');
        productListHtml += '</th></tr>' + productRows.join('');
        productListHtml += '</table>';

        var productList = $('div.product-list.' + server + '[data-sync-domain=\'' + response.app.domain +'\']');
        productList.html(productListHtml);
        var productListMainProducts = productList.find('tr.main-product');
        var productListAttributes = productList.find('tr.sub-row');

        if (server == 'remote') {
            for (var product of response.products) {
                var isAttribute = foodcoopshop.SyncProduct.isAttribute(product);
                var hasAttributes = foodcoopshop.SyncProduct.hasAttributes(product);
                var productNameWithUnity = foodcoopshop.SyncProduct.getProductNameWithUnity(product, isAttribute, hasAttributes);
                foodcoopshop.SyncProducts.updateAssignedProductNameAndHideItem(response.app.domain, product.id_product, productNameWithUnity, response.app.name);
            }
            foodcoopshop.SyncProducts.bindAddDragHandler(productListMainProducts.find('> td:nth-child(2)'));
            foodcoopshop.SyncProducts.bindAddDragHandler(productListAttributes.find('td:nth-child(2)'));
        }

        if (server == 'local') {
            foodcoopshop.SyncProducts.bindAddDropHandlerForMainProducts(productListMainProducts.find('> td:nth-child(2)'));
            foodcoopshop.SyncProducts.bindAddDropHandlerForAttributes(productListAttributes.find('td:nth-child(2)'));
            foodcoopshop.SyncProducts.addDeleteProductButtons();
        }

        foodcoopshop.SyncProducts.removeLoaderFromProductList(productList);
        foodcoopshop.SyncBase.reformatProductListRows(productList);

    },

    updateAssignedProductNameAndHideItem : function (syncDomain, remoteProductId, remoteProductName, remoteAppName) {
        var assignedProductRow = $('div.product-list.local table.assigned-products td[data-domain=\'' + syncDomain +'\'][data-remote-product-id=\'' + remoteProductId +'\']');
        var productIsAssigned = assignedProductRow.length;
        if (productIsAssigned) {
            assignedProductRow.find('span.product-name').html(remoteProductName);
            assignedProductRow.find('span.app-name').html(remoteAppName);
            var remoteList = $('div.product-list.remote[data-sync-domain=\'' + syncDomain +'\']');
            remoteList.find('tr[data-product-id=\'' + remoteProductId +'\']').hide();
            remoteList.find('b.app-name').html(remoteAppName);
        }
    },

    getHtmlForAssignedProduct : function (syncServer, productName, remoteProductId, remoteAppName) {
        return '<table class="assigned-products"><tr><td data-domain="' + syncServer + '" data-remote-product-id="' + remoteProductId + '"><span class="product-name">' + productName + '</span><span class="app-name">' + (remoteAppName ? remoteAppName : '') + '</span></td></tr></table>';
    },

    addDeleteProductButtons : function () {

        $('.delete-product-button').closest('td').remove();

        var object = $('div.product-list.local table.assigned-products td');

        object.before(
            $('<td/>').html(
                $('<a/>').
                    attr('href', 'javascript:void(0);').
                    html($('<i/>').addClass('fas fa-minus-circle fa-lg')).
                    addClass('delete-product-button').on('click', function () {
                        var button = $(this);
                        var remoteProduct = $(this).parent().next();
                        foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-minus-circle');
                        foodcoopshop.Helper.disableButton($(this));
                        foodcoopshop.Helper.ajaxCall(
                            '/network/syncs/ajaxDeleteProductRelation',
                            {
                                product: {
                                    remoteProductId: remoteProduct.data('remote-product-id'),
                                    localProductId: $(this).closest('tr.ok').data('product-id'),
                                    domain: remoteProduct.data('domain'),
                                    productName: remoteProduct.find('span.product-name').html()
                                }
                            },
                            {
                                onOk: function (data) {
                                    foodcoopshop.Helper.showSuccessMessage(data.msg);
                                    foodcoopshop.SyncProducts.onDeleteProduct(remoteProduct);
                                },
                                onError: function (data) {
                                    foodcoopshop.Helper.showErrorMessage(data.msg);
                                    foodcoopshop.SyncBase.resetButton(button, 'fa-minus-circle');
                                }
                            }
                        );
                    })
            )
        );

    },

    onDeleteProduct : function (remoteProduct) {

        // update local list
        var productCountBeforeDelete = remoteProduct.closest('tr.ok').find('table.assigned-products').length;
        if (productCountBeforeDelete == 1) {
            remoteProduct.closest('tr.ok').removeClass('ok');
        }
        remoteProduct.closest('table.assigned-products').remove(); // do not remove before removing parent elements!
        foodcoopshop.SyncBase.reformatProductListRows($('div.product-list.local'));

        // show item in remote list
        var remoteList = $('div.product-list.remote[data-sync-domain=\'' + remoteProduct.data('domain') +'\']');
        remoteList.find('tr[data-product-id=\'' + remoteProduct.data('remote-product-id') + '\']').removeAttr('style'); // reset display to table-cell
        foodcoopshop.SyncBase.reformatProductListRows(remoteList);
    },

    /**
     * dragging table columns: clone workaround
     */
    bindAddDragHandler : function (object) {

        var c = {};
        object.draggable({

            helper: 'clone',
            start: function (event, ui) {
                c.tr = this;
                c.helper = ui.helper;
            },
            revert: 'invalid',
            refreshPositions: true,
            drag: function (event, ui) {
                c.helper.addClass('draggable');
            },
            stop: function (event, ui) {
                c.helper.removeClass('draggable');
            }
        });

    },

    bindAddDropHandlerForMainProducts : function (object) {
        object.droppable({
            drop: function (event, ui) {
                // using the accept option has a very poor performance
                if (ui.draggable.parent().hasClass('main-product')) {
                    foodcoopshop.SyncProducts.onDropRemoteProduct($(this), ui);
                } else {
                    foodcoopshop.Helper.showErrorMessage(foodcoopshop.LocalizedJs.syncProducts.AnAttributeCannotBeAssignedToAProduct);
                }
            }
        });
    },

    bindAddDropHandlerForAttributes : function (object) {
        object.droppable({
            drop: function (event, ui) {
                // using the accept option has a very poor performance
                if (ui.draggable.parent().hasClass('sub-row')) {
                    foodcoopshop.SyncProducts.onDropRemoteProduct($(this), ui);
                } else {
                    foodcoopshop.Helper.showErrorMessage(foodcoopshop.LocalizedJs.syncProducts.AProductCannotBeAssignedToAnAttribute);
                }
            }
        });
    },

    onDropRemoteProduct : function (droppedElement, ui) {

        droppedElement.closest('tr').addClass('ok');

        var product = ui.draggable.parent();
        var localProductId = droppedElement.parent().data('product-id');
        var remoteProductId = product.find('td:nth-of-type(1)').html().trim();
        var productName = product.find('td:nth-of-type(2)').html().trim();
        var remoteList = ui.draggable.closest('div.product-list').find('b');
        var domain = remoteList.data('sync-domain');
        var appName = remoteList.html();

        var addedHtml = foodcoopshop.SyncProducts.getHtmlForAssignedProduct(domain, productName, remoteProductId, appName);
        var parsedHtml = $($.parseHTML(addedHtml));
        parsedHtml.css('opacity', .5);

        var spinner = $('<span/>').addClass('spinner').html($('<i/>').addClass('fas fa-check fa-lg'));
        foodcoopshop.Helper.addSpinnerToButton(spinner, 'fa-check');
        parsedHtml.find('.app-name').append(spinner);

        droppedElement.append(parsedHtml);

        foodcoopshop.SyncProducts.addDeleteProductButtons();
        var syncListTable = ui.draggable.closest('.sync-list');

        foodcoopshop.Helper.ajaxCall(
            '/network/syncs/ajaxSaveProductRelation/',
            {
                product: {
                    localProductId: localProductId,
                    remoteProductId: remoteProductId,
                    domain: domain,
                    productName: productName
                }
            },
            {
                onOk: function (data) {
                    foodcoopshop.Helper.showSuccessMessage(data.msg);
                    foodcoopshop.Helper.removeSpinnerFromButton(spinner, 'fa-check');
                    parsedHtml.css('opacity', 1);
                    ui.draggable.parent().hide();
                    foodcoopshop.SyncBase.reformatProductListRows(syncListTable);
                },
                onError: function (data) {
                    foodcoopshop.Helper.showErrorMessage(data.msg);
                    droppedElement.find('td[data-domain=\'' + domain +'\'][data-remote-product-id=\'' + remoteProductId +'\']').closest('table.assigned-products').remove();
                    if (droppedElement.find('table.assigned-products').length === 0) {
                        droppedElement.closest('tr').removeClass('ok');
                    }
                    foodcoopshop.SyncBase.reformatProductListRows(syncListTable);
                }
            }
        );
    },

    changeProductListWidth : function (count) {
        var newWidth = Math.round(100 / (count + 1)) - 1;
        $('div.product-list').css('width', newWidth + '%');
    }

};

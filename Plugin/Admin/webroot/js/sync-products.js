/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.SyncProducts = {

    init : function () {
        var localStorageCredentialsCount = foodcoopshop.SyncBase.loadCredentialsFromLocalStorage();
        this.bindLoadRemoteProductsButton();
        this.changeProductListWidth(foodcoopshop.SyncBase.getLoginForms().length);
        if (foodcoopshop.SyncBase.getLoginForms().length == localStorageCredentialsCount) {
            $('#sync-button-wrapper a').trigger('click');
        }
    },

    bindLoadRemoteProductsButton : function () {
        $('#sync-button-wrapper a').on('click', function () {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-refresh');
            foodcoopshop.Helper.disableButton($(this));
            $('div.product-list.remote').html('');
            foodcoopshop.SyncBase.doApiCall('/api/getProducts', null, foodcoopshop.SyncProducts.renderProductList);
        });
    },

    showLocalProductList : function (products) {
        var products = $.parseJSON(products);
        this.renderProductList(products, 'local');
    },

    getProductTableHeadElements : function () {
        return  ['Id', 'Name', 'Kategorien', 'Anzahl', 'Preis', 'Pfand', 'Neu', 'Aktiv'];
    },

    getProductTableRows : function (products) {

        var i = 0;
        var productRows = [];

        for (var product of products) {
            // do not render product attributes in step 1
            if ((!product.ProductAttributes || product.ProductAttributes.length === 0) && !product.ProductAttributeShop) {
                if (product.sync) {
                    product.Product.rowClass += ' ok';
                }
                var tableData = '<tr class="' + [product.Product.rowClass].join(' ') + '" data-product-id="' + product.Product.id_product + '">';
                    tableData += '<td>' + product.Product.id_product + '</td>';
                    tableData += '<td>' + product.ProductLang.name;
                if (product.PreparedSyncProducts) {
                    for (var syncProduct of product.PreparedSyncProducts) {
                        tableData += foodcoopshop.SyncProducts.getHtmlForAssignedProduct(syncProduct.domain, syncProduct.name, syncProduct.remoteProductId, syncProduct.appName);
                    }
                }
                    tableData +=  '</td>';
                    tableData += '<td>' + product.Categories.names.join(', ') + '</td>';
                    tableData += '<td>' + product.StockAvailable.quantity + '</td>';
                    tableData += '<td>' + (product.Product.gross_price > 0 ? foodcoopshop.Helper.formatFloatAsEuro(parseFloat(product.Product.gross_price)) : '') + '</td>';
                    tableData += '<td>' + (product.Tax ? product.Tax.rate : '') + '</td>';
                    tableData += '<td>' + (product.Deposit > 0 ? foodcoopshop.Helper.formatFloatAsEuro(parseFloat(product.Deposit)) : '') + '</td>';
                    tableData += '<td>' + (product.Product.active > -1 ? product.Product.active : '') + '</td>';
                tableData += '</tr>';
                productRows.push(tableData);
            }
            i++;
        }

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

        var productList = $("div.product-list." + server + "[data-sync-domain='" + response.app.domain +"']");
        productList.html(productListHtml);
        var productListProducts = productList.find('table tr.main-product');

        // TODO improve performance: only call if remote product is assigned!
        if (server == 'remote') {
            for (var product of response.products) {
                foodcoopshop.SyncProducts.updateAssignedProductNameAndHideItem(response.app.domain, product.Product.id_product, product.ProductLang.name, response.app.name);
            }
            foodcoopshop.SyncBase.reformatProductListRows(productListProducts);
            foodcoopshop.SyncProducts.bindAddDragHandler(productListProducts.find('td:nth-child(2)'));
        }

        if (server == 'local') {
            foodcoopshop.SyncProducts.bindAddDropHandler(productListProducts.find('td:nth-child(2)'));
            foodcoopshop.SyncProducts.addDeleteProductButtons();
        }

        foodcoopshop.SyncBase.reformatProductListRows(productListProducts);

    },

    updateAssignedProductNameAndHideItem : function (syncDomain, remoteProductId, remoteProductName, remoteAppName) {
        var assignedProductRow = $("div.product-list.local table.assigned-products td[data-domain='" + syncDomain +"'][data-remote-product-id='" + remoteProductId +"']");
        var productIsAssigned = assignedProductRow.length;
        if (productIsAssigned) {
            assignedProductRow.find('span.product-name').html(remoteProductName);
            assignedProductRow.find('span.app-name').html(remoteAppName);
            var remoteList = $("div.product-list.remote[data-sync-domain='" + syncDomain +"']");
            remoteList.find("tr[data-product-id='" + remoteProductId +"']").hide();
            remoteList.find('b.app-name').html(remoteAppName);
        }
    },

    getHtmlForAssignedProduct : function (syncServer, productName, remoteProductId, remoteAppName) {
        return '<table class="assigned-products"><tr><td data-domain="' + syncServer + '" data-remote-product-id="' + remoteProductId + '"><span class="product-name">' + productName + '</span><span class="app-name">' + (remoteAppName ? remoteAppName : '') + '</span></a></td></tr></table>';
    },

    addDeleteProductButtons : function () {

        $('.delete-product-button').closest('td').remove();

        var object = $('div.product-list.local table.assigned-products td')

        object.before(
            $('<td/>').html(
                $('<a/>').
                    attr('href', 'javascript:void(0);').
                    html($('<i/>').addClass('fa fa-minus-circle fa-lg')).
                    addClass('delete-product-button').on('click', function () {
                        var button = $(this);
                        var remoteProduct = $(this).parent().next();
                        foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-minus-circle');
                        foodcoopshop.Helper.disableButton($(this));
                        foodcoopshop.Helper.ajaxCall(
                            '/admin/syncs/ajaxDeleteProduct',
                            {
                                remoteProductId: remoteProduct.data('remote-product-id'),
                                localProductId: $(this).closest('.main-product').data('product-id'),
                                domain: remoteProduct.data('domain'),
                                productName: remoteProduct.find('span.product-name').html()
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
        var productCountBeforeDelete = remoteProduct.closest('tr.main-product').find('table.assigned-products').length;
        if (productCountBeforeDelete == 1) {
            remoteProduct.closest('tr.ok').removeClass('ok');
        }
        remoteProduct.closest('table.assigned-products').remove(); // do not remove before removing parent elements!
        foodcoopshop.SyncBase.reformatProductListRows($('div.product-list.local tr.main-product'));

        // show item in remote list
        var remoteList = $("div.product-list.remote[data-sync-domain='" + remoteProduct.data('domain') +"']");
        remoteList.find("tr[data-product-id='" + remoteProduct.data('remote-product-id') + "']").removeAttr('style'); // reset display to table-cell
        foodcoopshop.SyncBase.reformatProductListRows(remoteList.find('tr.main-product'));
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

    bindAddDropHandler : function (object) {
        object.droppable({
            drop: function (event, ui) {
                foodcoopshop.SyncProducts.onDropRemoteProduct($(this), ui);
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

        var spinner = $('<span/>').addClass('spinner').html($('<i/>').addClass('fa fa-check fa-lg'));
        foodcoopshop.Helper.addSpinnerToButton(spinner, 'fa-check');
        parsedHtml.find('.app-name').append(spinner);

        droppedElement.append(parsedHtml);

        foodcoopshop.SyncProducts.addDeleteProductButtons();
        var syncListTable = ui.draggable.closest('.sync-list');

        foodcoopshop.Helper.ajaxCall(
            '/admin/syncs/ajaxSaveProduct/',
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
                    foodcoopshop.SyncBase.reformatProductListRows(syncListTable.find('tr.main-product'));
                },
                onError: function (data) {
                    foodcoopshop.Helper.showErrorMessage(data.msg);
                    droppedElement.find("td[data-domain='" + domain +"'][data-remote-product-id='" + remoteProductId +"']").closest('table.assigned-products').remove();
                    if (droppedElement.find('table.assigned-products').length === 0) {
                        droppedElement.closest('tr').removeClass('ok');
                    }
                    foodcoopshop.SyncBase.reformatProductListRows(syncListTable.find('tr.main-product'));
                }
            }
        );
    },

    changeProductListWidth : function (count) {
        var newWidth = Math.round(100 / (count + 1)) - 1;
        $('div.product-list').css('width', newWidth + '%');
    }

}


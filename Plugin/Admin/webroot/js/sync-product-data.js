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
foodcoopshop.SyncProductData = {

    syncProducts : [],

    implementedSyncAttributes : [
        {
            name: 'name',
            data: {name: 'ProductLang.name', unity: 'ProductLang.unity', description: 'ProductLang.description', description_short: 'ProductLang.description_short'},
            column: 2
    },
        {
            name: 'quantity',
            data: 'StockAvailable.quantity',
            column: 4
    },
        {
            name: 'price',
            data: 'Product.gross_price',
            column: 5
    },
        {
            name: 'active',
            data: 'Product.active',
            column: 6
    }
    ],

    init : function (syncProducts, products) {
        foodcoopshop.SyncBase.loadCredentialsFromLocalStorage();
        foodcoopshop.SyncBase.showSyncForm();
        foodcoopshop.SyncBase.reformatProductListRows($('table.list tr.main-product'));
        this.syncProducts = $.parseJSON(syncProducts);
        this.products = $.parseJSON(products);
        this.bindSyncProductDataButton();
    },

    addLoaderToSyncProductDataButton : function (button) {
        button.on('click', function () {
             foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-refresh');
             foodcoopshop.Helper.disableButton($(this));
        });
    },

    getProductTableHeadElements : function () {
        return  ['<input type="checkbox" id="row-marker-all" />', 'Name', 'Kategorien', 'Anzahl', 'Preis', /* 'Pfand', 'Neu', */ 'Aktiv'];
    },

    getProductTableRows : function (products) {

        var i = 0;
        var productRows = [];

        for (var product of products) {
            // do not render product attributes in step 1
            if ((!product.ProductAttributes || product.ProductAttributes.length === 0) && !product.ProductAttributeShop) {
                if (product.sync) {
                    product.Product.rowClass += ' ok';
                    var tableData = '<tr class="' + [product.Product.rowClass].join(' ') + '" data-product-id="' + product.Product.id_product + '">';
                        tableData += '<td class="sync-checkbox"><input type="checkbox" class="row-marker" /></td>';
                        tableData += '<td class="name">(' + product.PreparedSyncProducts.length + ') '+ product.ProductLang.name + ' <span class="small">' + product.ProductLang.unity + '</span>';
                        tableData +=  '</td>';
                        tableData += '<td>' + product.Categories.names.join(', ') + '</td>';
                        tableData += '<td class="quantity">' + product.StockAvailable.quantity + '</td>';
                        tableData += '<td class="price">' + (product.Product.gross_price > 0 ? foodcoopshop.Helper.formatFloatAsEuro(parseFloat(product.Product.gross_price)) : '') + '</td>';
//	                    tableData += '<td>' + (product.Deposit > 0 ? foodcoopshop.Helper.formatFloatAsEuro(parseFloat(product.Deposit)) : '') + '</td>';
//	                    tableData += '<td>' + product.Product.is_new + '</td>';
                        tableData += '<td class="active">' + (product.Product.active ? 1 : 0) + '</td>';
                    tableData += '</tr>';
                    productRows.push(tableData);
                }
            }

            i++;
        }

        return productRows;

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
            productList.find('tr.horizontal-checkboxes').find('td:nth-of-type(' + $(this)[0].column + ')').append($('<input type="checkbox" checked="checked" data-name="' + $(this)[0].column + '"/>'));
        });

        foodcoopshop.Admin.initRowMarkerAll().trigger('click');

        var productListProducts = productList.find('table tr.main-product');
        foodcoopshop.SyncBase.reformatProductListRows(productListProducts);

    },

    bindSyncProductDataButton : function () {

        $('.sync-button-wrapper a').on('click', function () {

            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-refresh');
            foodcoopshop.Helper.disableButton($(this));

            var preparedData = $.extend(true, {}, foodcoopshop.SyncProductData.syncProducts); // $.extend: no reference
            var domains = Object.keys(preparedData);

            var postData = {};
            $(domains).each(function (key, domain) {
                postData[domain] = [];
                var i = 0;
                $(preparedData[domain]).each(function (key, product) {
                    var attributes = {};
                    $(foodcoopshop.SyncProductData.implementedSyncAttributes).each(function () {
                        var syncAttribute = $('table.list tr.horizontal-checkboxes td:nth-of-type('+$(this)[0].column+') input[type="checkbox"]:checked').length > 0;
                        if (syncAttribute) {
                            var tableRow = $("table tr.main-product[data-product-id='" + product.localProductId + "']");
                            var syncProduct = tableRow.find('td.sync-checkbox input[type="checkbox"]:checked').length > 0;
                            if (syncProduct) {
                                var attributeName = $(this)[0].name;
                                var tableCell = tableRow.find('td.' + attributeName);
                                for (var syncProduct of foodcoopshop.SyncProductData.products) {
                                    if (product.localProductId == syncProduct.Product.id_product) {
                                        var dataIndex = $(this)[0].data;
                                        if ($.type(dataIndex) == 'string') {
                                            var newValue = foodcoopshop.Helper.resolveIndex(dataIndex, syncProduct);
                                            if (attributeName == 'active') {
                                                var newValue = newValue ? 1 : 0; // transform true or false to 1 or 1
                                            }
                                            attributes[attributeName] = newValue;
                                        }
                                        if ($.type(dataIndex) == 'object') {
                                            attributes[attributeName] = [];
                                            var dataIndexKeys = Object.keys(dataIndex);
                                            var newValue = [];
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
                        postData[domain]['baseDomain'] = foodcoopshop.SyncBase.getHostnameFromUrl();
                        i++;
                    }

                });
            });

            foodcoopshop.SyncBase.doApiCall('/api/updateProducts', postData, foodcoopshop.SyncProductData.onProductDataUpdated);

        });

    },

    onProductDataUpdated : function (response) {
        foodcoopshop.Helper.showOrAppendSuccessMessage('<b>' + response.app.name + '</b>: ' + response.msg);
    }

}

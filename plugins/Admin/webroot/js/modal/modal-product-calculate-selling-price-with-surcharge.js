/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalProductCalculateSellingPriceWithSurcharge = {

    init : function() {

        var modalSelector = '#modal-product-calculate-selling-price-with-surcharge';

        var button = $('#calculateSellingPriceWithSurchargForSelectedProducts');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"]').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {

            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            var title = 'Verkaufspreis berechnen';
            //var title = productIds.length == 1 ? foodcoopshop.LocalizedJs.admin.Product : foodcoopshop.LocalizedJs.admin.Products;

            var products = [];
            for (var i in productIds) {
                products.push($('tr#product-' + productIds[i] + ' span.product-name').html());
            }
            var html = '<ul><li>' + products.join('</li><li>') + '</li></ul>';

            html += '<label for="dialogProductSurcharge">Aufschlag: </label><br />';
            html += '<input type="number" step="0.01" min="0.01" name="dialogProductSurcharge" id="dialogProductSurcharge" value="" />';
            html += ' % vom Netto-Einkaufspreis';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                title,
                html
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductCalculateSellingPriceWithSurcharge.getSuccessHandler(modalSelector, productIds);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductCalculateSellingPriceWithSurcharge.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductCalculateSellingPriceWithSurcharge.getOpenHandler(modalSelector);

        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, productIds) {
        foodcoopshop.Helper.ajaxCall(
            '/admin/products/calculate-selling-price-with-surcharge',
            {
                productIds: productIds,
                surcharge: $('#dialogProductSurcharge').val()
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    var message = '<p>';
                    if (productIds.length == 1) {
                        message += foodcoopshop.LocalizedJs.admin.ErrorsOccurredWhileProductWasDeleted;
                    } else {
                        message += foodcoopshop.LocalizedJs.admin.ErrorsOccurredWhileProductsWereDeleted;
                    }
                    message += ':</p>';
                    message = message + data.msg;
                    foodcoopshop.Modal.appendFlashMessage(modalSelector, message);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {
        $(modalSelector).modal();
        $(modalSelector + ' #dialogProductSurcharge').focus();

    }

};
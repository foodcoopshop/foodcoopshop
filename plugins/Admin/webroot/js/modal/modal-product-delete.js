/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalProductDelete = {

    init : function() {

        var modalSelector = '#modal-product-delete';

        var button = $('#deleteSelectedProducts');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {

            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            var title = productIds.length == 1 ? foodcoopshop.LocalizedJs.admin.DeleteProduct : foodcoopshop.LocalizedJs.admin.DeleteProducts;

            var html = '<p style="margin-top: 10px;">';
            if (productIds.length == 1) {
                html += foodcoopshop.LocalizedJs.admin.ReallyDeleteOneProduct;
            } else {
                html += foodcoopshop.LocalizedJs.admin.ReallyDelete0Products.replace(/\{0\}/, '<b>' + productIds.length + '</b>');
            }
            html += '</p><p>' + foodcoopshop.LocalizedJs.admin.BeCarefulNoWayBack + '</p>';

            var products = [];
            for (var i in productIds) {
                products.push($('tr#product-' + productIds[i] + ' span.product-name').html());
            }
            html += '<ul><li>' + products.join('</li><li>') + '</li></ul>';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                title,
                html
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductDelete.getSuccessHandler(modalSelector, productIds);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductDelete.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductDelete.getOpenHandler(modalSelector);

        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, productIds) {
        foodcoopshop.Helper.ajaxCall(
            '/admin/products/delete/',
            {
                productIds: productIds
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
                    foodcoopshop.appendFlashMessageError(modalSelector, message);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
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
foodcoopshop.ModalProductCalculateSellingPriceWithSurcharge = {

    init : function() {

        var modalSelector = '#modal-product-calculate-selling-price-with-surcharge';

        var button = $('#calculateSellingPriceWithSurchargForSelectedProducts');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {

            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            var title = foodcoopshop.LocalizedJs.admin.CalculateSellingPrice;

            var products = [];
            for (var i in productIds) {
                products.push($('tr#product-' + productIds[i] + ' span.product-name').html());
            }
            var html = '<ul><li>' + products.join('</li><li>') + '</li></ul>';

            html += '<div class="field-wrapper">';
            html += '<label for="dialogProductSurcharge">' + foodcoopshop.LocalizedJs.admin.SurchargeInPercentFromPurchasePriceNet + ':<br />';
            html += '<br /><span class="small">' + foodcoopshop.LocalizedJs.admin.CalculateSellingPriceExplanationText + '</span>';
            html += '</label>';
            html += '<input type="number" step="0.01" min="0.01" name="dialogProductSurcharge" id="dialogProductSurcharge" value="" />';
            html += '</div>';
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
                    message += foodcoopshop.LocalizedJs.admin.ErrorsOccurredWhileCalculatingSellingPrice;
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
        $(modalSelector + ' #dialogProductSurcharge').focus();

    }

};
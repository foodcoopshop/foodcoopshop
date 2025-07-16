/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Martin Hatlauf <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalProductDuplicate = {

    init : function() {

        var modalSelector = '#modal-product-duplicate';

        var button = $('#duplicateSelectedProduct');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Helper.disableButton(button);
            if ($('table.list').find('input.row-marker[type="checkbox"]:checked').length === 1) {
                foodcoopshop.Helper.enableButton(button);
            }
        });

        button.on('click', function () {

            var productId = foodcoopshop.Admin.getSelectedProductIds().pop();
            var title = foodcoopshop.LocalizedJs.admin.CopyProduct;
            var maxAmount = 10;

            var html = '<p style="margin-top: 10px;">';
            html += foodcoopshop.LocalizedJs.admin.ReallyCopyProduct;
            html += '</p>';

            var product = $('tr#product-' + productId + ' span.product-name').html();

            html += '<ul><li>' + product + '</li></ul>';

            html += '<div class="field-wrapper">';
            html += '<label class="dynamic-element default" for="copy-amount">'+ foodcoopshop.LocalizedJs.admin.AmountOfCopies +'</label><br>';
            html += '<select id="copy-amount" name="copy-amount" style="margin-top: 5px;">';
            for (var i = 1; i <= maxAmount; i++) {
                html += '<option value= "'+i+'" >' + i + '</option>';
            }
            html += '</select>';
            html += '</div>';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                title,
                html
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                var amountValue = parseInt($(modalSelector + ' #copy-amount').val());
                console.log(amountValue);
                foodcoopshop.ModalProductDuplicate.getSuccessHandler(modalSelector, productId, amountValue);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductDuplicate.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductDuplicate.getOpenHandler(modalSelector);

        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, productId, amount) {
        foodcoopshop.Helper.ajaxCall(
            '/admin/products/duplicate/',
            {
                productId: productId,
                copyAmount: amount,
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    var message = '<p>';

                    message += foodcoopshop.LocalizedJs.admin.ErrorsOccurredWhileProductWasCopied;

                    message += ':</p>';
                    message = message + data.msg;
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, message);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};

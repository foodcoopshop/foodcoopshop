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

    init: function () {

        var modalSelector = '#modal-product-duplicate';

        var button = $('#duplicateSelectedProduct');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {

            let productIds = foodcoopshop.Admin.getSelectedProductIds();
            let productNames = [];
            let productNamesWithAttributes = [];

            for (const productId of productIds) {
                var productRow = $('tr#product-' + productId);
                var hasAttributes = foodcoopshop.Admin.hasProductAttributes(productRow);

                if (hasAttributes) {
                    productNamesWithAttributes.push($('tr#product-' + productId + ' span.product-name').html());
                } else {
                    productNames.push($('tr#product-' + productId + ' span.product-name').html())
                }
            }
            var title = foodcoopshop.LocalizedJs.admin.CopyProduct;

            var html='';
            if (productNamesWithAttributes.length > 0) {
                html += '<p style="margin-bottom:0px;"><b>';
                html += foodcoopshop.LocalizedJs.admin.AttributeInfo;
                html += '</b></p>';

                html += '<ul style="margin-bottom:15px;">';
                for (const name in productNamesWithAttributes) {
                    html += '<li><b>' + productNamesWithAttributes[name] + '</b></li>';
                }
                html += '</ul>';
            }

            if (productNames.length > 0) {
                html += '<p style="margin-bottom:0px;">';
                if (productNames.length > 1) {
                    html += foodcoopshop.LocalizedJs.admin.ReallyCopyProductX;
                } else {
                    html += foodcoopshop.LocalizedJs.admin.ReallyCopyProduct1;
                }
                html += '</p>';

                html += '<ul style="margin-bottom:0px;">';
                for (const name in productNames) {
                    html += '<li><b>' + productNames[name] + '</b></li>';
                }
                html += '</ul>';
            }


            html += '<p style="margin-top:15px; margin-bottom:0;">' + foodcoopshop.LocalizedJs.admin.DataCopyInfo + '</p>';
            html += '<ul>';
            html += '<li>' + foodcoopshop.LocalizedJs.admin.CopiedData + '</li>';
            html += '</ul>';

            html += '<p style="margin-top:15px;margin-bottom:0;">' + foodcoopshop.LocalizedJs.admin.DataNotCopyInfo + '</p>';
            html += '<ul>';
            html += '<li>' + foodcoopshop.LocalizedJs.admin.NotCopiedData + '</li>';
            html += '</ul>';

            html += '<p style="margin-top:15px;">';
            html += foodcoopshop.LocalizedJs.admin.CopyStatus;
            html += '</p>';

            html += '<div class="field-wrapper">';
            html += '<label class="dynamic-element default" style="width: 140px;" for="copy-amount">' + foodcoopshop.LocalizedJs.admin.AmountOfCopies + '</label>';
            html += '<select id="copy-amount" name="copy-amount" style="margin-top: 5px;">';

            const maxAmount = 10;
            for (let i = 1; i <= maxAmount; i++) {
                html += '<option value= "' + i + '">' + i + '</option>';
            }
            html += '</select>';
            html += '</div>';

            var buttons = [
                foodcoopshop.Modal.createButton(
                    productNames.length === 0 ? ['btn-success', 'disabled'] : ['btn-success'],
                    foodcoopshop.LocalizedJs.admin.Copy,
                    'fas fa-check'
                ),
                foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
            ];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                title,
                html,
                buttons,
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function () {
                var amountValue = parseInt($(modalSelector + ' #copy-amount').val());
                foodcoopshop.ModalProductDuplicate.getSuccessHandler(modalSelector, productIds, amountValue);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductDuplicate.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductDuplicate.getOpenHandler(modalSelector);

        });

    },

    getCloseHandler: function (modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler: function (modalSelector, productIds, amount) {
        foodcoopshop.Helper.ajaxCall(
            '/admin/products/duplicate/',
            {
                productIds: productIds,
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

    getOpenHandler: function (modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};

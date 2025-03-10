/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalProductAttributeEdit = {

    init : function() {

        $('.edit-product-attribute-button').on('click', function () {

            var modalSelector = '#modal-product-attribute-edit';

            var row = $(this).closest('tr');
            var splittedProductId = row.attr('id').replace(/product-/, '').split('-');
            var productId = splittedProductId[0];
            var productAttributeId = splittedProductId[1];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.EditAttribute,
                foodcoopshop.ModalProductAttributeEdit.getHtml(row)
            );

            if (foodcoopshop.Helper.isSelfServiceModeEnabled) {
                var barcode = row.find('td.cell-name .barcode-for-dialog').text();
                $(modalSelector + ' #dialogBarcode').val(barcode);
            }

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductAttributeEdit.getSuccessHandler(modalSelector, productId, productAttributeId);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductAttributeEdit.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalProductAttributeEdit.getOpenHandler(modalSelector);

        });

    },

    getHtml : function(row) {

        var html = '<label for="dialogProductAttributeEdit"><b></b></label>';
        html += '<div class="field-wrapper">';
        html += '<label class="checkbox">';
        html += '<input type="checkbox" name="dialogProductAttributeEditDelete" id="dialogProductAttributeEditDelete" />';
        html += ' ' + '<p>' + foodcoopshop.LocalizedJs.admin.DeleteAttribute0.replaceI18n(0, '<b>' + row.find('td.cell-name span.name-for-dialog').html() + '</b>');
        html += '</label>';
        html += '<p style="margin-top:-10px;float:left;" class="small">' + foodcoopshop.LocalizedJs.admin.DeleteExplanation + '</p>';
        html += '</div>';

        if (foodcoopshop.Helper.isSelfServiceModeEnabled) {
            html += '<hr />';
            html += '<div class="field-wrapper">';
            html += '<div class="dialog-barcode-wrapper">';
            html += '<label id="dialogLabelBarcode" for="dialogBarcode"><b>' + foodcoopshop.LocalizedJs.dialogProduct.BarcodeDescription + '</b></label>';
            html += '<input type="text" name="dialogBarcode" id="dialogBarcode" value="" /><br />';
            html += '</div>';
        }

        html += '<input type="hidden" name="ModalProductAttributeEditAttributeId" id="dialogfoodcoopshop.ModalProductAttributeEditAttributeId" value="" />';
        return html;

    },


    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, productId, productAttributeId) {
        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editProductAttribute/',
            {
                productId: productId,
                productAttributeId: productAttributeId,
                deleteProductAttribute: $('#dialogProductAttributeEditDelete:checked').length > 0 ? 1 : 0,
                barcode: $('#dialogBarcode').length > 0 ? $('#dialogBarcode').val() : '',
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    foodcoopshop.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
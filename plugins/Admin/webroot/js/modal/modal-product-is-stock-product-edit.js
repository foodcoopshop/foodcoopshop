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
foodcoopshop.ModalProductIsStockProductEdit = {

    init : function() {

        $('.product-is-stock-product-edit-button').on('click', function () {

            var modalSelector = '#modal-product-is-stock-product-edit';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.dialogProduct.StockProduct,
                foodcoopshop.ModalProductIsStockProductEdit.getHtml()
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductIsStockProductEdit.getSuccessHandler(modalSelector);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductIsStockProductEdit.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalProductIsStockProductEdit.getOpenHandler($(this), modalSelector);


        });

    },

    getHtml : function() {
        var html = '<label for="dialogIsStockProductIsStockProduct"><b></b></label>';
        html += '<div class="field-wrapper">';
        html += '<label class="checkbox">';
        html += '<input type="checkbox" name="dialogIsStockProductIsStockProduct" id="dialogIsStockProductIsStockProduct" />';
        html += ' ' + foodcoopshop.LocalizedJs.dialogProduct.IsProductStockProduct;
        html += '</label>';
        html += '</div>';
        html += '<p style="margin-top:20px;float:left;" class="small">' + foodcoopshop.LocalizedJs.dialogProduct.TheDeliveryRhythmOfStockProductsIsAlwaysWeekly + '</p>';
        html += '<input type="hidden" name="dialogIsStockProductProductId" id="dialogIsStockProductProductId" value="" />';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {

        var data = {
            productId: $('#dialogIsStockProductProductId').val(),
            isStockProduct: $('#dialogIsStockProductIsStockProduct:checked').length > 0 ? 1 : 0
        };

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editIsStockProduct/',
            data,
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );

    },

    getOpenHandler : function(button, modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
        var dataRow = button.closest('tr');
        $(modalSelector + ' #dialogIsStockProductIsStockProduct').prop('checked', dataRow.find('td.is-stock-product').html().match('fa-check'));
        $(modalSelector + ' #dialogIsStockProductProductId').val(dataRow.find('td.cell-id').html());
        $(modalSelector + ' label[for="dialogIsStockProductIsStockProduct"] b').html(foodcoopshop.Admin.getProductNameForDialog(dataRow));
    }

};
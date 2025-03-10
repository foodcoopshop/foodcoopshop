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
foodcoopshop.ModalProductDepositEdit = {

    init : function() {

        var modalSelector = '#modal-product-deposit-edit';

        $('a.product-deposit-edit-button').on('click', function () {

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.dialogProduct.EnterDeposit,
                foodcoopshop.ModalProductDepositEdit.getHtml()
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductDepositEdit.getSuccessHandler(modalSelector);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductDepositEdit.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductDepositEdit.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var html = '<label for="dialogDepositDeposit"><b></b></label><br />';
        html += '<input type="number" step="0.01" name="dialogDepositDeposit" id="dialogDepositDeposit" value="" />';
        html += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b>';
        html += '<div class="small" style="margin-top:10px;">' + foodcoopshop.LocalizedJs.dialogProduct.EnterZeroForDelete + '</div>';
        html += '<input type="hidden" name="dialogDepositProductId" id="dialogDepositProductId" value="" />';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editDeposit/',
            {
                productId: $('#dialogDepositProductId').val(),
                deposit: $('#dialogDepositDeposit').val(),
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

    getOpenHandler : function(button, modalSelector) {

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        var row = button.closest('tr');
        var depositContainer = row.find('span.deposit-for-dialog');
        var deposit;
        if (depositContainer.length > 0) {
            deposit = foodcoopshop.Helper.getCurrencyAsFloat(row.find('span.deposit-for-dialog').html()).toFixed(2);
        }
        $(modalSelector + ' #dialogDepositDeposit').val(deposit);
        $(modalSelector + ' #dialogDepositProductId').val(row.find('td.cell-id').html());
        var label = foodcoopshop.Admin.getProductNameForDialog(row);
        $(modalSelector + ' label[for="dialogDepositDeposit"] b').html(label);

        $('#dialogDepositDeposit').focus();

    }

};
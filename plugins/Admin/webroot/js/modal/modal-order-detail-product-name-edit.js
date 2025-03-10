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
foodcoopshop.ModalOrderDetailProductNameEdit = {

    init : function() {

        var modalSelector = '#order-detail-product-name-edit-form';

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.ChangeProductName,
            foodcoopshop.ModalOrderDetailProductNameEdit.getHtml()
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderDetailProductNameEdit.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalOrderDetailProductNameEdit.getCloseHandler();
        });

        $('.order-detail-product-name-edit-button').on('click', function() {
            foodcoopshop.ModalOrderDetailProductNameEdit.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var html = '<label for="dialogName"><b>' + foodcoopshop.LocalizedJs.dialogProduct.Name + '</b></label><br />';
        html += '<input type="text" name="dialogOrderDetailProductNameName" id="dialogOrderDetailProductNameName" value="" /><br />';
        html += '<input type="hidden" name="dialogOrderDetailProductNameOrderDetailId" id="dialogOrderDetailProductNameOrderDetailId" value="" />';
        html += '<hr />';
        return html;
    },

    getCloseHandler : function() {
        $('#dialogOrderDetailProductNameName').val('');
        $('#dialogOrderDetailProductNameOrderDetailId').val('');
        $('#flashMessage').remove();
    },

    getSuccessHandler : function(modalSelector) {

        var productName = $('#dialogOrderDetailProductNameName').val();
        if ($('#dialogOrderDetailProductNameOrderDetailId').val() == '') {
            return false;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editProductName/',
            {
                orderDetailId: $('#dialogOrderDetailProductNameOrderDetailId').val(),
                productName: productName,
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

        var row = button.closest('tr');
        var orderDetailId = row.find('td:nth-child(2)').html();
        var productName = row.find('td:nth-child(4) a.name-for-dialog').text().trim();

        $(modalSelector + ' #dialogOrderDetailProductNameOrderDetailId').val(orderDetailId);
        $(modalSelector + ' #dialogOrderDetailProductNameName').val(productName);
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        $('#dialogOrderDetailProductNameName').focus();

    }

};
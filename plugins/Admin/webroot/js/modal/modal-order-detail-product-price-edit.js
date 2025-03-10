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
foodcoopshop.ModalOrderDetailProductPriceEdit = {

    init : function() {

        var modalSelector = '#order-detail-product-price-edit-form';

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.AdaptPrice,
            foodcoopshop.ModalOrderDetailProductPriceEdit.getHtml()
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderDetailProductPriceEdit.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalOrderDetailProductPriceEdit.getCloseHandler();
        });

        $('.order-detail-product-price-edit-button').on('click', function() {
            foodcoopshop.ModalOrderDetailProductPriceEdit.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var html = '<label for="dialogOrderDetailProductPricePrice"></label><br />';
        html += '<input type="number" step="0.01" min="0.01" name="dialogOrderDetailProductPricePrice" id="dialogOrderDetailProductPricePrice" value="" />';
        html += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b>';
        html += '<div class="textarea-wrapper" style="margin-top: 10px;">';
        html += '<label for="dialogEditPriceReason">' + foodcoopshop.LocalizedJs.admin.WhyIsPriceAdapted + '</label>';
        html += '<textarea name="dialogEditPriceReason" id="dialogEditPriceReason"></textarea>';
        html += '</div>';
        html += '<label class="checkbox">';
        html += '<input type="checkbox" name="dialogEditPriceSendEmailToCustomer" id="dialogEditPriceSendEmailToCustomer" checked="checked" />';
        html += '<span style="font-weight:normal;">' + foodcoopshop.LocalizedJs.admin.SendEmailToMember + '</span>';
        html += '</label>';
        html += '<input type="hidden" name="dialogOrderDetailProductPriceOrderDetailId" id="dialogOrderDetailProductPriceOrderDetailId" value="" />';
        return html;

    },

    getCloseHandler : function() {
        $('#dialogOrderDetailProductPrice').val('');
        $('#dialogOrderDetailProductPriceOrderDetailId').val('');
        $('#flashMessage').remove();
    },

    getSuccessHandler : function(modalSelector) {

        if ($('#dialogOrderDetailProductPriceOrderDetailId').val() == '') {
            return;
        }

        var editPriceReason = $('#dialogEditPriceReason').val();
        var productPrice = $('#dialogOrderDetailProductPricePrice').val();

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editProductPrice/',
            {
                orderDetailId: $('#dialogOrderDetailProductPriceOrderDetailId').val(),
                productPrice: productPrice,
                editPriceReason: editPriceReason,
                sendEmailToCustomer: $('#dialogEditPriceSendEmailToCustomer:checked').length > 0 ? 1 : 0,
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
        foodcoopshop.Editor.initSmall('dialogEditPriceReason', true);

        var row = button.closest('tr');
        var orderDetailId = row.find('td:nth-child(2)').html();
        var price = foodcoopshop.Helper.getCurrencyAsFloat(row.find('td:nth-child(6) span.product-price-for-dialog').html()).toFixed(2);
        var productPriceField = $(modalSelector + ' #dialogOrderDetailProductPricePrice');

        $(modalSelector + ' #dialogOrderDetailProductPriceOrderDetailId').val(orderDetailId);
        $(modalSelector + ' label[for="dialogOrderDetailProductPricePrice"]').html('<b>' + row.find('td:nth-child(4) a.name-for-dialog').text() + '</b> <span style="font-weight:normal;">(' + foodcoopshop.LocalizedJs.admin.orderedBy + ' ' + row.find('td.customer-field span.customer-name-for-dialog').text() + ')');

        $(modalSelector + ' .price-per-unit-info-text').remove();
        productPriceField.val(price);

        $('#dialogOrderDetailProductQuantityPrice').focus();

    }

};
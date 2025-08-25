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
foodcoopshop.ModalOrderDetailProductAmountEdit = {

    init : function() {

        var modalSelector = '#order-detail-product-amount-edit-form';

        $('.order-detail-product-amount-edit-button').on('click', function() {

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.DecreaseAmount,
                foodcoopshop.ModalOrderDetailProductAmountEdit.getHtml()
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalOrderDetailProductAmountEdit.getSuccessHandler(modalSelector);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalOrderDetailProductAmountEdit.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalOrderDetailProductAmountEdit.getOpenHandler($(this), modalSelector);

        });

    },

    getHtml : function() {
        var html = '<div class="field-wrapper">';
        html += '<label for="dialogOrderDetailProductAmountAmount">' + foodcoopshop.LocalizedJs.admin.NewAmount + ' </label>';
        html += '<select name="dialogOrderDetailProductAmountAmount" id="dialogOrderDetailProductAmountAmount" /></select>';
        html += '</div>';
        html += '<div class="textarea-wrapper">';
        html += '<label for="dialogEditAmountReason">' + foodcoopshop.LocalizedJs.admin.WhyIsAmountDecreased + '</label>';
        html += '<textarea name="dialogEditAmountReason" id="dialogEditAmountReason"></textarea>';
        html += '</div>';
        html += '<input type="hidden" name="dialogOrderDetailProductAmountOrderDetailId" id="dialogOrderDetailProductAmountOrderDetailId" value="" />';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {

        var editorData = $('#dialogEditAmountReason').val();
        if (editorData == '') {
            foodcoopshop.Modal.appendFlashMessageError(modalSelector, foodcoopshop.LocalizedJs.admin.AdaptAmountReasonIsMandatory);
            foodcoopshop.Modal.resetButtons(modalSelector);
            return false;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editProductAmount/',
            {
                orderDetailId: $('#dialogOrderDetailProductAmountOrderDetailId').val(),
                productAmount: $('#dialogOrderDetailProductAmountAmount').val(),
                editAmountReason: editorData,
            },
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

        foodcoopshop.Editor.initSmall('dialogEditAmountReason', true);

        var row = button.closest('tr');
        var currentAmount = row.find('td:nth-child(3) span.product-amount-for-dialog').html();
        var select = $(modalSelector + ' #dialogOrderDetailProductAmountAmount');
        select.find('option').remove();
        select.append($('<option>', {
            value: '',
            text: foodcoopshop.LocalizedJs.admin.PleaseSelect
        }));
        for (var i = currentAmount - 1; i >= 1; i--) {
            select.append($('<option>', {
                value: i,
                text: i
            }));
        }

        $(modalSelector + ' #dialogOrderDetailProductAmountOrderDetailId').val(row.find('td:nth-child(2)').html());
        var infoTextForEditProductAmount = '<p><b>' + row.find('td:nth-child(4) a.name-for-dialog').text() + '</b>';
        infoTextForEditProductAmount += ' (' + foodcoopshop.LocalizedJs.admin.orderedBy + ' ';
        infoTextForEditProductAmount += row.find('td.customer-field span.customer-name-for-dialog').html() + ')</p>';
        $(modalSelector + ' .modal-body').prepend(infoTextForEditProductAmount);

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

    }

};
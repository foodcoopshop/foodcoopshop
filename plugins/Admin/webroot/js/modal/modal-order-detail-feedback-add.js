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
foodcoopshop.ModalOrderDetailFeedbackAdd = {

    init : function() {

        $('a.product-feedback-button').on('click', function () {

            var modalSelector = '#modal-order-detail-feedback-add';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.AddProductFeedback,
                foodcoopshop.ModalOrderDetailFeedbackAdd.getHtml()
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalOrderDetailFeedbackAdd.getSuccessHandler(modalSelector);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalOrderDetailFeedbackAdd.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalOrderDetailFeedbackAdd.getOpenHandler($(this), modalSelector);

        });

    },

    getHtml : function() {
        var html = '<label></label>';
        html += '<p class="small add-product-feedback-explanation-text"></p>';
        html += '<div class="textarea-wrapper">';
        html += '<textarea name="dialogOrderDetailFeedback" id="dialogOrderDetailFeedback"></textarea>';
        html += '</div>';
        html += '<input type="hidden" name="dialogOrderDetailId" id="dialogOrderDetailId" value="" />';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {

        if ($('#dialogOrderDetailId').val() == '') {
            return false;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/addFeedback/',
            {
                orderDetailId: $('#dialogOrderDetailId').val(),
                orderDetailFeedback: $('#dialogOrderDetailFeedback').val(),
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

        foodcoopshop.Modal.removeTooltipster();

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        var row = button.closest('tr');
        var orderDetailId = row.find('td:nth-child(2)').html();
        var productName = row.find('.name-for-dialog').text();
        var customerName = row.find('.customer-name-for-dialog').text();
        var manufacturerName = row.find('td:nth-child(5) a').text();

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/setElFinderUploadPath/' + orderDetailId,
            {},
            {
                onOk: function (data) {
                    foodcoopshop.Editor.initSmallWithUpload('dialogOrderDetailFeedback', true);
                },
                onError: function (data) {
                    foodcoopshop.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );

        $(modalSelector + ' #dialogOrderDetailId').val(orderDetailId);
        $(modalSelector + ' label').html('<b>' + productName + '</b>' + ' (' + foodcoopshop.LocalizedJs.admin.orderedBy + ' ' + customerName + ')');
        $(modalSelector + ' .add-product-feedback-explanation-text').html(
            foodcoopshop.LocalizedJs.admin.AddProductFeedbackExplanationText0.replaceI18n(0, '<b>' + manufacturerName + '</b>')
        );

    }

};
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
foodcoopshop.ModalPaymentDelete = {

    init : function() {

        $('.delete-payment-button').on('click',function () {

            var modalSelector = '#modal-payment-delete';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.DeletePayment,
                ''
            );

            var dataRow = $(this).closest('tr');

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalPaymentDelete.getSuccessHandler(modalSelector, dataRow);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalPaymentDelete.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalPaymentDelete.getOpenHandler(modalSelector, dataRow);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, dataRow) {

        var paymentId = dataRow.find('td:nth-child(1)').text();

        foodcoopshop.Helper.ajaxCall(
            '/admin/payments/changeStatus/',
            {
                paymentId: paymentId
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

    getOpenHandler : function(modalSelector, dataRow) {

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        var html = '<p>' + foodcoopshop.LocalizedJs.admin.ReallyDeletePayment + '</p>';
        html += '<p>' + foodcoopshop.LocalizedJs.admin.Date + ': <b>' + dataRow.find('td:nth-child(2)').html() + '</b> <br />';
        html += foodcoopshop.LocalizedJs.admin.AmountMoney + ': <b>' + dataRow.find('td:nth-child(4)').html();
        if (dataRow.find('td:nth-child(6)').length > 0) {
            html += dataRow.find('td:nth-child(6)').html();
        }
        html += '</b></p>';

        $(modalSelector + ' .modal-body').append(html);

    }

};
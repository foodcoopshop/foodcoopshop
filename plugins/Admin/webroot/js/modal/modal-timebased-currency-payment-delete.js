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
foodcoopshop.ModalTimebasedCurrencyPaymentDelete = {

    init : function() {

        $('.delete-payment-button').on('click',function () {

            var modalSelector = '#modal-timebased-currency-payment-delete';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.DeletePayment,
                ''
            );

            var dataRow = $(this).closest('tr');

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalTimebasedCurrencyPaymentDelete.getSuccessHandler(modalSelector, dataRow);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalTimebasedCurrencyPaymentDelete.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalTimebasedCurrencyPaymentDelete.getOpenHandler(modalSelector, dataRow);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, dataRow) {

        var paymentId = dataRow.data('payment-id');

        foodcoopshop.Helper.ajaxCall(
            '/admin/timebased-currency-payments/delete/',
            {
                paymentId: paymentId
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessage(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector, dataRow) {

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        var dialogHtml = '<p>Willst du die Eintragung wirklich löschen?<br />';
        var dateElement = dataRow.find('td:nth-child(3)');
        var dateHtml = 'Kein Datum eingetragen';
        if (dateElement.html() != '') {
            dateHtml = dateElement.html();
        }
        dialogHtml += 'Arbeitstag: <b>' + dateHtml + '</b> <br />';
        dialogHtml += 'Stunden: <b>' + dataRow.find('td:nth-child(6)').html();
        dialogHtml += '</b>';

        $(modalSelector + ' .modal-body').append(dialogHtml);

    }

};
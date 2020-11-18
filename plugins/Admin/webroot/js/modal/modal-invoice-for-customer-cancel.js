/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalInvoiceForCustomerCancel = {

    init : function() {
        var modalSelector = '#modal-invoice-for-customer-cancel';
        $('a.invoice-for-customer-cancel-button').on('click', function () {
            foodcoopshop.ModalInvoiceForCustomerCancel.getOpenHandler($(this), modalSelector);
        });
    },

    getHtml : function(customerName, invoiceNumber) {
        var html = '<p>' + foodcoopshop.LocalizedJs.admin.ReallyCancelInvoiceNumber0OfCustomer1.replaceI18n(0, '<b>' + invoiceNumber + '</b>').replaceI18n(1, '<b>' + customerName + '</b>') + '</p>';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(invoiceId) {

        foodcoopshop.Helper.ajaxCall(
            '/admin/invoices/cancel',
            {
                invoiceId: invoiceId
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    document.location.reload();
                }
            }
        );
    },

    getOpenHandler : function(button, modalSelector) {

        var row = button.closest('tr');

        var customerName = row.find('td:nth-child(3)').text();
        var invoiceId = row.data('invoice-id');
        var invoiceNumber = row.find('td:nth-child(1)').text();

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.CancelInvoice,
            foodcoopshop.ModalInvoiceForCustomerCancel.getHtml(customerName, invoiceNumber)
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalInvoiceForCustomerCancel.getSuccessHandler(invoiceId);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalInvoiceForCustomerCancel.getCloseHandler(modalSelector);
        });

        $(modalSelector).modal();

    }

};
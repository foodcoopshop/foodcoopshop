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
foodcoopshop.ModalInvoiceForCustomer = {

    init : function() {
        var modalSelector = '#modal-invoice-for-customer';
        $('a.invoice-for-customer-button:not(.disabled)').on('click', function () {
            foodcoopshop.ModalInvoiceForCustomer.getOpenHandler($(this), modalSelector);
        });
    },

    getHtml : function(customerName) {
        var html = '<p>' + foodcoopshop.LocalizedJs.admin.ReallyGenerateInvoiceFor0.replaceI18n(0, '<b>' + customerName + '</b>') + '</p>';
        html += '<div class="field-wrapper">';
        html += '<label class="checkbox">';
        html += '<input type="checkbox" name="dialogInvoiceForCustomerPaidInCash" id="dialogInvoiceForCustomerPaidInCash" />';
        html += ' ' + foodcoopshop.LocalizedJs.admin.PaidInCash + '?';
        html += '</label>';
        html += '</div>';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, customerId) {
        var paidInCash = foodcoopshop.ModalInvoiceForCustomer.getPaidInCashValue();
        window.open('/admin/customers/previewInvoice.pdf?customerId=' + customerId + '&paidInCash=' + paidInCash);
        var successButton = foodcoopshop.Modal.getSuccessButton(modalSelector);
        foodcoopshop.Helper.removeSpinnerFromButton(successButton, 'fa-check');
        foodcoopshop.Helper.enableButton(successButton);
    },

    getPaidInCashValue: function() {
        var paidInCash = $('#dialogInvoiceForCustomerPaidInCash:checked').length > 0 ? 1 : 0;
        return paidInCash;
    },

    getOpenHandler : function(button, modalSelector) {

        var row = button.closest('tr');

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.admin.ShowPreview, 'fa fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light generate-invoice-button'], foodcoopshop.LocalizedJs.admin.GenerateInvoice, 'fas fa-file-invoice'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
        ];

        var customerName = row.find('td:nth-child(3)').text();
        var customerId = row.find('td:nth-child(2)').text();

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.GenerateInvoice,
            foodcoopshop.ModalInvoiceForCustomer.getHtml(customerName),
            buttons
        );

        $(modalSelector + ' .generate-invoice-button').on('click', function() {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-file-invoice');
            foodcoopshop.Helper.disableButton($(this));
            var paidInCash = foodcoopshop.ModalInvoiceForCustomer.getPaidInCashValue();
            document.location.href = '/admin/customers/generateInvoice.pdf?customerId=' + customerId + '&paidInCash=' + paidInCash;
        });

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalInvoiceForCustomer.getSuccessHandler(modalSelector, customerId);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalInvoiceForCustomer.getCloseHandler(modalSelector);
        });

        $(modalSelector).modal();

    }

};
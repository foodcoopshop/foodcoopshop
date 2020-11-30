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
foodcoopshop.ModalInvoiceForCustomerAdd = {

    init : function() {
        var modalSelector = '#modal-invoice-for-customer-add';
        $('a.invoice-for-customer-add-button:not(.disabled)').on('click', function () {
            foodcoopshop.ModalInvoiceForCustomerAdd.getOpenHandler($(this), modalSelector);
        });
    },

    getHtml : function(customerName) {
        var html = '<p>' + foodcoopshop.LocalizedJs.admin.ReallyGenerateInvoiceFor0.replaceI18n(0, '<b>' + customerName + '</b>') + '</p>';
        html += '<div class="field-wrapper">';
        html += '<label class="checkbox">';
        html += '<input type="checkbox" checked="checked" name="dialogInvoiceForCustomerPaidInCash" id="dialogInvoiceForCustomerPaidInCash" />';
        html += ' ' + foodcoopshop.LocalizedJs.admin.PaidInCash + '?';
        html += '</label>';
        html += '</div>';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, customerId) {
        var paidInCash = foodcoopshop.ModalInvoiceForCustomerAdd.getPaidInCashValue();
        window.open('/admin/invoices/preview.pdf?customerId=' + customerId + '&paidInCash=' + paidInCash);
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
            foodcoopshop.Modal.createButton(['btn-outline-light generate-invoice-button'], foodcoopshop.LocalizedJs.admin.GenerateInvoice, 'fas fa-exclamation-triangle not-ok'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
        ];

        var customerName = row.find('td:nth-child(3)').text();
        var customerId = row.find('td:nth-child(2)').text();

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.GenerateInvoice,
            foodcoopshop.ModalInvoiceForCustomerAdd.getHtml(customerName),
            buttons
        );

        $(modalSelector + ' .generate-invoice-button').on('click', function() {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-exclamation-triangle');
            foodcoopshop.Helper.disableButton($(this));
            var paidInCash = foodcoopshop.ModalInvoiceForCustomerAdd.getPaidInCashValue();
            document.location.href = '/admin/invoices/generate.pdf?customerId=' + customerId + '&paidInCash=' + paidInCash;
        });

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalInvoiceForCustomerAdd.getSuccessHandler(modalSelector, customerId);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalInvoiceForCustomerAdd.getCloseHandler(modalSelector);
        });

        $(modalSelector).modal();

    }

};
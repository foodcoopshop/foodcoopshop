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

    init : function(paymentIsCashless) {
        var modalSelector = '#modal-invoice-for-customer-add';
        $('a.invoice-for-customer-add-button:not(.disabled)').on('click', function () {
            foodcoopshop.ModalInvoiceForCustomerAdd.getOpenHandler($(this), modalSelector, paymentIsCashless);
        });
    },

    getHtml : function(customerName, invoiceAmount, paymentIsCashless) {
        var html = '<p>' + foodcoopshop.LocalizedJs.admin.ReallyGenerateInvoiceFor0.replaceI18n(0, '<b>' + customerName + '</b>') + '</p>';
        html += '<h3 style="text-align:center;font-weight:bold;" id="invoiceAmount">' + invoiceAmount + '</h3>';
        html += '<div class="field-wrapper">';
        html += '<input type="number" id="givenAmount" style="text-align:left;margin-bottom:15px;" />';
        html += ' ' + foodcoopshop.LocalizedJs.admin.GivenAmount;
        html += '</div>';
        html += '<div class="field-wrapper">';
        html += '<h3 style="text-align:center;font-weight:bold;color:red;" id="changeAmount">&nbsp;</h3>';
        html += '</div>';
        html += '<div class="field-wrapper">';
        if (paymentIsCashless) {
            html += '<label class="checkbox">';
            html += '<input type="checkbox" checked="checked" name="dialogInvoiceForCustomerPaidInCash" id="dialogInvoiceForCustomerPaidInCash" />';
            html += ' ' + foodcoopshop.LocalizedJs.admin.PaidInCash + '?';
            html += '</label>';
        }
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

    getOpenHandler : function(button, modalSelector, paymentIsCashless) {

        var row = button.closest('tr');

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-outline-light preview-invoice-button'], foodcoopshop.LocalizedJs.admin.ShowPreview, 'fa fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light generate-invoice-button'], foodcoopshop.LocalizedJs.admin.GenerateInvoice, 'fas fa-exclamation-triangle not-ok'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
        ];

        var customerName = row.find('td:nth-child(3)').text();
        var customerId = row.find('td:nth-child(2)').text();
        var invoiceAmount = row.find('td.invoice .invoice-amount').text();

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.GenerateInvoice,
            foodcoopshop.ModalInvoiceForCustomerAdd.getHtml(customerName, invoiceAmount, paymentIsCashless),
            buttons
        );

        $(modalSelector + ' .generate-invoice-button').on('click', function() {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-exclamation-triangle');
            foodcoopshop.Helper.disableButton($(this));
            var paidInCash = foodcoopshop.ModalInvoiceForCustomerAdd.getPaidInCashValue();
            document.location.href = '/admin/invoices/generate.pdf?customerId=' + customerId + '&paidInCash=' + paidInCash;
        });

        $(modalSelector + ' #givenAmount').keyup(function (e) {
            var changeAmount = parseFloat($(this).val()) - foodcoopshop.Helper.getCurrencyAsFloat($(modalSelector + ' #invoiceAmount').text());
            var newValue = '&nbsp;';
            if (changeAmount > 0) {
                newValue = foodcoopshop.Helper.formatFloatAsCurrency(changeAmount) + ' ' + foodcoopshop.LocalizedJs.admin.back;
            }
            $(modalSelector + ' #changeAmount').html(newValue);

        });

        $(modalSelector + ' .preview-invoice-button').on('click', function() {
            foodcoopshop.ModalInvoiceForCustomerAdd.getSuccessHandler(modalSelector, customerId);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalInvoiceForCustomerAdd.getCloseHandler(modalSelector);
        });

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        $(modalSelector + ' #givenAmount').focus();
    }

};
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
        $('a.invoice-for-customer-button').on('click', function () {
            foodcoopshop.ModalInvoiceForCustomer.getOpenHandler($(this), modalSelector);
        });
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, customerId) {
        window.open('/admin/customers/getInvoice.pdf?customerId=' + customerId);
        var successButton = foodcoopshop.Modal.getSuccessButton(modalSelector);
        foodcoopshop.Helper.removeSpinnerFromButton(successButton, 'fa-check');
        foodcoopshop.Helper.enableButton(successButton);
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
            '<p>' + foodcoopshop.LocalizedJs.admin.ReallyGenerateInvoiceFor0.replaceI18n(0, '<b>' + customerName + '</b>') + '</p>',
            buttons
        );

        $(modalSelector + ' .generate-invoice-button').on('click', function() {
            //foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-file-invoice');
            //foodcoopshop.Helper.disableButton($(this));
            alert('not yet implemented');
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
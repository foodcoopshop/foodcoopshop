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
foodcoopshop.ModalPaymentAdd = {

    initDepositInList: function() {

        var button = '.add-payment-deposit-button';

        $(button).each(function () {

            var buttonClass = button.replace(/\./, '');
            buttonClass = buttonClass.replace(/-button/, '');
            var form = $('#' + buttonClass + '-form-' + $(this).data('objectId'));
            var heading = form.find('h3');
            form.find('h3').addClass('hide');

            $(this).on('click', function () {
                foodcoopshop.ModalPaymentAdd.initDeposit(heading, form);
            });

        });

    },

    initDepositSingle: function(button, form) {

        var heading = form.find('h3');
        form.find('h3').addClass('hide');

        $(button).on('click', function () {
            foodcoopshop.ModalPaymentAdd.initDeposit(heading, form);
        });

    },

    initDeposit : function(heading, form) {

        var modalSelector = '#payment-deposit-add';

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            heading.html(),
            ''
        );

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalPaymentAdd.getCloseHandler(modalSelector);
        });

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalPaymentAdd.getSuccessHandler(modalSelector);
        });

        foodcoopshop.ModalPaymentAdd.getOpenHandler(modalSelector, form);

        var customerDropdownSelector = modalSelector + ' #payments-customerid';
        if ($(customerDropdownSelector).length > 0) {
            $(customerDropdownSelector).find('option[value=""]').remove();
            $(customerDropdownSelector).selectpicker({
                liveSearch: true,
                size: 7,
                title: foodcoopshop.LocalizedJs.admin.PleaseSelectMember,
            });
            foodcoopshop.Admin.initCustomerDropdown(0, 0, 0, customerDropdownSelector);
        }
    },

    init : function() {

        $('#add-payment-button-wrapper .btn-add-payment').on('click', function () {

            var form = $('.add-payment-form');
            var heading = form.find('h3');
            form.find('h3').addClass('hide');

            var modalSelector = '#payment-product-add';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                heading.html(),
                ''
            );

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalPaymentAdd.getCloseHandler(modalSelector);
            });

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalPaymentAdd.getSuccessHandler(modalSelector);
            });

            foodcoopshop.ModalPaymentAdd.getOpenHandler(modalSelector, form);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {

        var amount = $(modalSelector + ' #payments-amount').val();
        var type = $(modalSelector + ' input[name="Payments[type]"]').val();
        var dateAddDomElement = $(modalSelector + ' input[name="Payments[date_add]"]');
        var customerIdDomElement = $(modalSelector + ' [name="Payments[customerId]"]');
        var manufacturerIdDomElement = $(modalSelector + ' input[name="Payments[manufacturerId]"]');

        var text = '';
        if ($(modalSelector + ' input[name="Payments[text]"]').length > 0) {
            text = $(modalSelector + ' input[name="Payments[text]"]').val().trim();
        }

        if (customerIdDomElement.length > 0 && customerIdDomElement.val() === null) {
            foodcoopshop.Modal.appendFlashMessage(modalSelector, foodcoopshop.LocalizedJs.admin.PleaseSelectAMember);
            foodcoopshop.Modal.resetButtons(modalSelector);
            return;
        }

        // radio buttons only if deposit is added to manufacurers
        if ($(modalSelector + ' input[type="radio"]').length > 0) {
            var selectedRadioButton = $(modalSelector + ' input[type="radio"]:checked');

            // check if radio buttons are in deposit form or product form
            var message;
            var isDepositForm;
            if (modalSelector == '#payment-deposit-add') {
                message = foodcoopshop.LocalizedJs.admin.PleaseChoseTypeOfPayment;
                isDepositForm = true;
            } else {
                message = foodcoopshop.LocalizedJs.admin.PleaseChoseIfPaybackOrCreditUpload;
                isDepositForm = false;
            }

            if (selectedRadioButton.length == 0) {
                foodcoopshop.Modal.appendFlashMessage(modalSelector, message);
                foodcoopshop.Modal.resetButtons(modalSelector);
                return;
            }

            var selectedRadioButtonValue = $(modalSelector + ' input[type="radio"]:checked').val();
            if (isDepositForm) {
                text = selectedRadioButtonValue;
            } else {
                type = selectedRadioButtonValue;
            }
        }

        const customerId = customerIdDomElement.length > 0 ? customerIdDomElement.val() : 0;
        const manufacturerId = manufacturerIdDomElement.length > 0 ? manufacturerIdDomElement.val() : 0;
        const dateAdd = dateAddDomElement.length > 0 ? dateAddDomElement.val() : 0;

        let postUrl = '';
        if (customerId > 0) {
            postUrl += '/admin/payments/addCustomerPayment/' + customerId;
        }
        if (manufacturerId > 0) {
            postUrl += '/admin/payments/addManufacturerPayment/' + manufacturerId;
        }

        if (postUrl === '') {
            return;
        }
        
        foodcoopshop.Helper.ajaxCall(postUrl, {
            amount: amount,
            type: type,
            text: text,
            dateAdd: dateAdd,
        }, {
            onOk: function (data) {
                document.location.reload();
            },
            onError: function (data) {
                foodcoopshop.Modal.appendFlashMessage(modalSelector, data.msg);
                foodcoopshop.Modal.resetButtons(modalSelector);
            }
        });

    },

    getOpenHandler : function(modalSelector, form) {

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
        $(modalSelector).addClass('add-payment-form');
        $(modalSelector + ' .modal-body').append(form.html());

        // avoid double id in dom
        form.remove();

        $(modalSelector + ' input[type="number"]').focus();

        foodcoopshop.Helper.initDatepicker();
        $(modalSelector).find('input.datepicker').datepicker({  maxDate: '0'});

    }

};
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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
            form.find('h3').remove();
            
            $(this).on('click', function () {
                foodcoopshop.ModalPaymentAdd.initDeposit(heading, form);
            });

        });
        
    },
    
    initDepositSingle: function(button, form) {
        
        var heading = form.find('h3');
        form.find('h3').remove();

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
        
    },
        
    init : function() {
        
        $('#add-payment-button-wrapper .btn-success').on('click', function () {
            
            var formHtml = $('.add-payment-form');
            var heading = formHtml.find('h3');
            formHtml.find('h3').remove();
            
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
            
            foodcoopshop.ModalPaymentAdd.getOpenHandler(modalSelector, formHtml);
        });
        
    },
        
    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },
    
    getSuccessHandler : function(modalSelector) {
        
        var amount = $(modalSelector + ' #payments-amount').val();
        var type = $(modalSelector + ' input[name="Payments[type]"]').val();
        var customerIdDomElement = $(modalSelector + ' input[name="Payments[customerId]"]');
        var manufacturerIdDomElement = $(modalSelector + ' input[name="Payments[manufacturerId]"]');

        var text = '';
        if ($(modalSelector + ' input[name="Payments[text]"]').length > 0) {
            text = $(modalSelector + ' input[name="Payments[text]"]').val().trim();
        }

        // radio buttons only if deposit is added to manufacurers
        if ($(modalSelector + ' input[type="radio"]').length > 0) {
            var selectedRadioButton = $(modalSelector + ' input[type="radio"]:checked');

            // check if radio buttons are in deposit form or product form
            var message;
            var isDepositForm;
            if ($(modalSelector + ' .add-payment-form').hasClass('add-payment-deposit-form')) {
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

        var months_range = [];
        if ($(modalSelector + ' input[type="checkbox"]').length > 0) {
            $(modalSelector + ' input[type="checkbox"]:checked').each(
                function () {
                    months_range.push($(this).val());
                }
            );
            if (months_range.length == 0) {
                foodcoopshop.Modal.appendFlashMessage(modalSelector, foodcoopshop.LocalizedJs.admin.PleaseChoseAtLeastOneMonth);
                foodcoopshop.Modal.resetButtons(modalSelector);
                return;
            }
        }

        foodcoopshop.Helper.ajaxCall('/admin/payments/add/', {
            amount: amount,
            type: type,
            text: text,
            months_range: months_range,
            customerId: customerIdDomElement.length > 0 ? customerIdDomElement.val() : 0,
            manufacturerId: manufacturerIdDomElement.length > 0 ? manufacturerIdDomElement.val() : 0
        }, {
            onOk: function (data) {
                document.location.reload();
            },
            onError: function (data) {
                var container = $('.featherlight-content');
                foodcoopshop.Modal.appendFlashMessage(modalSelector, data.msg);
                foodcoopshop.Modal.resetButtons(modalSelector);
            }
        });
        
    },
    
    getOpenHandler : function(modalSelector, form) {
        $(modalSelector).modal();
        $(modalSelector).addClass('add-payment-form');
        $(modalSelector + ' .modal-body').append(form.html());
        foodcoopshop.Helper.changeInputNumberToTextForEdge();
    }

};
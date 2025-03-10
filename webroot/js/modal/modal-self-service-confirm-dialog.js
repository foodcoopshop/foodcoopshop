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
foodcoopshop.ModalSelfServiceConfirmDialog = {
   
    init : function(title, html, dialogbuttons) {

        var modalSelector = '#self-service-confirm-dialog';
        var buttons = [];
        dialogbuttons = $.parseJSON(dialogbuttons);

        if (dialogbuttons.length == 0) {
            return;
        }

        for(var i=0;i<dialogbuttons.length;i++) {
           buttons[i] = [foodcoopshop.Modal.createButton([dialogbuttons[i].classes], dialogbuttons[i].title, dialogbuttons[i].faIcon, dialogbuttons[i].isCloseButton, [dialogbuttons[i].value])];
        }

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            title,
            html,
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalSelfServiceConfirmDialog.getSuccessHandler();
        });

        $('button.btn-order-self-service').on('click', function () {
            foodcoopshop.ModalSelfServiceConfirmDialog.getOpenHandler(modalSelector);
        });

        $(modalSelector + ' button.btn-success.no-auto-bind').on('click', function () {
            foodcoopshop.ModalSelfServicePaymenttypeDetailsDialog.getOpenHandler('#self-service-confirm-dialog-paymenttype-details', $(this).text(), $(this).attr('value'));
        });

        $(modalSelector + ' button.btn-outline-light').on('click', function () {
            foodcoopshop.ModalSelfServiceConfirmDialog.getCloseHandler(modalSelector);
        });

        $(modalSelector + ' button.btn-close').on('click', function () {
            foodcoopshop.ModalSelfServiceConfirmDialog.getCloseHandler(modalSelector);
        }); 
    },

    getSuccessHandler : function() {
       var selfSForm = $('#SelfServiceForm');
       selfSForm.submit();
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    },

    getCloseHandler : function(modalSelector) {
        foodcoopshop.SelfService.setFocusToSearchInputField();
    },

};
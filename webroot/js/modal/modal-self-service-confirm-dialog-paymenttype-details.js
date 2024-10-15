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
foodcoopshop.ModalSelfServicePaymenttypeDetailsDialog = {

    getSuccessHandler : function() {
       var selfSForm = $('#SelfServiceForm');
       selfSForm.submit();
    },

    getOpenHandler : function(modalSelector, paymentName, paymentText) {
        var modalSelector = '#self-service-confirm-dialog-paymenttype-details';
        var title = paymentName;
        var html='<p>' + paymentText + '</p>';

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.cart.selfServiceConfirmPurchaseButton, 'fa-fw fas fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.cart.selfServiceDenyPurchaseButton, null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            title,
            html,
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalSelfServicePaymenttypeDetailsDialog.getSuccessHandler();
        });

        $('btn-outline-light').on('click', function () {
            foodcoopshop.ModalSelfServicePaymenttypeDetailsDialog.getCloseHandler(modalSelector);
        }); 

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    },

    getCloseHandler : function(modalSelector) {
        foodcoopshop.Modal.destroy($('#self-service-confirm-dialog-paymenttype-details'));
    },
    
};
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
   
    init : function() {
        var modalSelector = '#self-service-confirm-dialog_paymenttype-details';
        var title = '';
        var html='';
        title = foodcoopshop.LocalizedJs.cart.selfServiceConfirmPurchaseDialog + '?';  //todo
        html = '<p>' + foodcoopshop.LocalizedJs.cart.selfServiceConfirmPurchase + '</p>';   //todo
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

        $('button.btn-order-self-service').on('click', function () {
            foodcoopshop.ModalSelfServicePaymenttypeDetailsDialog.getOpenHandler(modalSelector);
        });
    }
};
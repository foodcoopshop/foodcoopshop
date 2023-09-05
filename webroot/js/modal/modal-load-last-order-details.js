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
foodcoopshop.ModalLoadLastOrderDetails = {

    init : function() {

        $('#load-last-order-details').on('change', function() {

            var modalSelector = '#modal-load-last-order-details';

            var selectedValue = $(this).val();
            if (selectedValue != '') {
                var title = '';
                var html = '';
                var redirectUrl = '';
                if (selectedValue == 'remove-all-products-from-cart') {
                    title = foodcoopshop.LocalizedJs.cart.emptyCart + '?';
                    html = '<p>' + foodcoopshop.LocalizedJs.cart.reallyEmptyCart + '</p>';
                    redirectUrl = '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/emptyCart/';
                } else {
                    title = foodcoopshop.LocalizedJs.cart.loadPastOrder;
                    html = foodcoopshop.LocalizedJs.cart.loadPastOrderDescriptionHtml;
                    redirectUrl = '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/addOrderToCart?deliveryDate=' + selectedValue;
                }
            } else {
                return false;
            }

            var buttons = [
                foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.helper.yes, 'fa-fw fas fa-check'),
                foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
            ];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                title,
                html,
                buttons
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalLoadLastOrderDetails.getSuccessHandler(redirectUrl);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalLoadLastOrderDetails.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalLoadLastOrderDetails.getOpenHandler(modalSelector);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(redirectUrl) {
        document.location.href = redirectUrl;
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
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

        $('.load-last-order-details').on('change', function() {

            var modalSelector = '#modal-load-last-order-details';

            var selectedValue = $(this).val();
            if (selectedValue != '') {
                var title = '';
                var html = '';
                var redirectUrl = '';
                if (selectedValue == 'remove-all-products-from-cart') {
                    title = __('Empty_cart') + '?';
                    html = '<p>' + __('Really_empty_cart?') + '</p>';
                    redirectUrl = '/' + __('route_cart') + '/emptyCart/';
                } else {
                    title = __('Load_past_order');
                    html = __('Load_past_order_dialog_description_html');
                    redirectUrl = '/' + __('route_cart') + '/addOrderToCart?deliveryDate=' + selectedValue;
                }
            } else {
                return false;
            }

            var buttons = [
                foodcoopshop.Modal.createButton(['btn-success'], __('Yes'), 'fa-fw fas fa-check'),
                foodcoopshop.Modal.createButton(['btn-outline-light'], __('Cancel'), null, true)
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
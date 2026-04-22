/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalCart = {

    init : function(button) {

        var modalSelector = '#modal-cart';

        $(button).on('click', function () {
            foodcoopshop.ModalCart.getOpenHandler(modalSelector, $(this));
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getOpenHandler : function(modalSelector, button) {

        const elementSelector = button.data('element-selector');
        let buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.cart.ContinueToFinishCart, 'fa-fw fas fa-shopping-cart', false),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.Close, null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            '',
            '',
            buttons,
        );

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalCart.getCloseHandler(modalSelector);
        });

        var contentWrapper = $(elementSelector).clone();
        let contentHtml = contentWrapper.html();

        $(modalSelector + ' .modal-body').append(contentHtml);

        headingHtml = foodcoopshop.LocalizedJs.cart.YourCart;
        foodcoopshop.Cart.initRemoveFromCartLinks();
        foodcoopshop.ModalLoadLastOrderDetails.init();
        foodcoopshop.ModalOrderForDifferentCustomerCancel.init();

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            document.location.href = foodcoopshop.LocalizedJs.admin.routeCartShow;
        });

        $(modalSelector + ' .modal-title').html(headingHtml);

    }

};
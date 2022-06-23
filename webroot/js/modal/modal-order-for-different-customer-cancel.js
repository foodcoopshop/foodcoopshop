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
foodcoopshop.ModalOrderForDifferentCustomerCancel = {

    init : function() {

        var modalSelector = '#order-for-different-customer-cancel';

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.helper.yes, 'fa fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.no, null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.helper.CancelOrder,
            this.getHtml(),
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderForDifferentCustomerCancel.getSuccessHandler();
        });

        $('#cart .order-for-different-customer-info a.btn').on('click', function () {
            foodcoopshop.ModalOrderForDifferentCustomerCancel.getOpenHandler(modalSelector);
        });

    },

    getHtml : function() {
        return '<p>' + foodcoopshop.LocalizedJs.helper.ReallyCancelOrder + '</p>';
    },

    getSuccessHandler : function() {
        foodcoopshop.Helper.ajaxCall(
            '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/ajaxDeleteOrderForDifferentCustomer',
            {},
            {
                onOk: function (data) {
                    foodcoopshop.Modal.destroy('#order-for-different-customer-add', window.parent.document);
                    document.location.reload();
                },
                onError: function (data) {
                    document.location.reload();
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
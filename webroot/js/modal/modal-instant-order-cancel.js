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
foodcoopshop.ModalInstantOrderCancel = {

    init : function() {

        var modalSelector = '#instant-order-cancel';

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.helper.yes, 'fa fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.no, null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.helper.CancelInstantOrder,
            this.getHtml(),
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalInstantOrderCancel.getSuccessHandler();
        });

        $('#cart .instant-order-customer-info a.btn').on('click', function () {
            foodcoopshop.ModalInstantOrderCancel.getOpenHandler(modalSelector);
        });

    },

    getHtml : function() {
        return '<p>' + foodcoopshop.LocalizedJs.helper.ReallyCancelInstantOrder + '</p>';
    },

    getSuccessHandler : function() {
        foodcoopshop.Helper.ajaxCall(
            '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/ajaxDeleteInstantOrderCustomer',
            {},
            {
                onOk: function (data) {
                    foodcoopshop.Modal.destroy('#instant-order-add', window.parent.document);
                    document.location.reload();
                },
                onError: function (data) {
                    document.location.reload();
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {
        $(modalSelector).modal();
    }

};
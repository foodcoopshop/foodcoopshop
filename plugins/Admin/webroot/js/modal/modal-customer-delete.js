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
foodcoopshop.ModalCustomerDelete = {

    init : function(customerId) {

        var modalSelector = '#modal-customer-delete';

        $('.delete-customer-button').on('click', function() {
            foodcoopshop.ModalCustomerDelete.getOpenHandler(modalSelector, customerId);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, customerId) {

        foodcoopshop.Helper.ajaxCall(
            '/admin/customers/delete/' + customerId,
            {
                referer: $('input[name="referer"]').val()
            },
            {
                onOk: function (data) {
                    document.location.href = data.redirectUrl;
                },
                onError: function (data) {
                    var message = '<p><b>' + __('Errors_occurred_while_member_was_deleted') + ':</b> </p>';
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, message + data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector, customerId) {

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], __('Yes'), 'fa-fw fas fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], __('No'), null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            __('Delete_member?'),
            '',
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalCustomerDelete.getSuccessHandler(modalSelector, customerId);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalCustomerDelete.getCloseHandler(modalSelector);
        });

        var html = '<p style="margin-top: 10px;">' + __('Really_delete_member?') + '</p>';
        html += '<p>' + __('Be_careful_there_is_no_way_back!') + '</p>';
        $(modalSelector).find('.modal-body').html(html);
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
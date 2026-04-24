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
foodcoopshop.ModalOrderDetailAllProductsPickedUp = {

    initNotGroupedBy: function() {
        $('.change-products-picked-up-button').on('click', function () {
            var selectedCustomer = $('.filter-container #customerid option:selected');
            if (selectedCustomer.length == 0) {
                return false;
            }
            var customerIds = [selectedCustomer.val()];
            var customerName = selectedCustomer.text();
            var title = __('Were_the_products_picked_up?');
            foodcoopshop.ModalOrderDetailAllProductsPickedUp.getOpenHandler(title, customerIds, customerName);
        });
    },

    initPickedUpGroupedByCustomer: function() {
        $('.change-products-picked-up-button').on('click', function () {
            var customerIds = [$(this).closest('tr').find('td:nth-child(2)').html()];
            var customerName = $(this).closest('tr').find('td:nth-child(3)').text();
            var title = __('Were_the_products_picked_up?');
            foodcoopshop.ModalOrderDetailAllProductsPickedUp.getOpenHandler(title, customerIds, customerName);
        });
    },

    initPickedUpForAllCustomers: function() {
        $('.change-products-picked-up-all-customers-button').on('click', function () {
            var customerIds = [];
            $('table.list tr.data').each(function() {
                customerIds.push($(this).find('td:nth-child(2)').html());
            });
            var title = __('All_products_picked_up?');
            foodcoopshop.ModalOrderDetailAllProductsPickedUp.getOpenHandler(title, customerIds, '');
        });
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getHtml : function() {
        var html = '<p></p>';
        html += '<input type="hidden" id="customerId"></input>';
        return html;
    },

    getOpenHandler : function(title, customerIds, customerName) {

        var modalSelector = '#modal-order-detail-pickup-day-edit';

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], __('Yes'), 'fa-fw fas fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light no-button'], __('No')),
            foodcoopshop.Modal.createButton(['btn-outline-light'], __('Cancel'), null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            title,
            foodcoopshop.ModalOrderDetailAllProductsPickedUp.getHtml(),
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderDetailAllProductsPickedUp.getSuccessHandler(modalSelector, 1);
        });

        $(modalSelector + ' .no-button').on('click', function() {
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.ModalOrderDetailAllProductsPickedUp.getSuccessHandler(modalSelector, 0);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalOrderDetailAllProductsPickedUp.getCloseHandler(modalSelector);
        });

        $(modalSelector + ' #customerId').val(customerIds.join(','));

        var infoMessage = $(modalSelector + ' p').html('');
        if (customerName != '') {
            infoMessage.html(__('Member') + ': <b>' + customerName + '</b>');
        }
        if (customerName == '') {
            infoMessage.html(__('Were_the_products_of_all_members_picked_up?'));
        }

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

    },

    getSuccessHandler : function(modalSelector, state) {

        var customerIds = $(modalSelector + ' #customerId').val().split(',');
        var pickupDay = $('input[name="pickupDay[]"]').val();

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editProductsPickedUp',
            {
                state: state,
                customerIds: customerIds,
                pickupDay: pickupDay
            },
            {
                onOk: function (data) {
                    if (data.redirectUrl != '') {
                        document.location.href = data.redirectUrl;
                    } else {
                        document.location.reload();
                    }
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );

    }

};
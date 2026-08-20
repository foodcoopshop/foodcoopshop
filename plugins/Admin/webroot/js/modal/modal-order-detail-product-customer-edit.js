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
foodcoopshop.ModalOrderDetailProductCustomerEdit = {

    init : function() {

        var modalSelector = '#order-detail-customer-edit-form';

        $('.order-detail-customer-edit-button').on('click', function() {

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                __('Change_member'),
                foodcoopshop.ModalOrderDetailProductCustomerEdit.getHtml()
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalOrderDetailProductCustomerEdit.getSuccessHandler(modalSelector);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalOrderDetailProductCustomerEdit.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalOrderDetailProductCustomerEdit.getOpenHandler($(this), modalSelector);

        });

    },

    getHtml : function() {
        var html = '<label for="dialogOrderDetailEditCustomerId" style="margin-bottom:10px;"></label><br />';
        html += '<select id="dialogOrderDetailEditCustomerId">' + $('#customerid').html() + '</select>';
        html += '<label for="dialogOrderDetailEditCustomerAmount" style="margin-top:10px;width:100%;">' + __('Amount_that_should_be_changed_to_member?') + '</label><br />';
        html += '<select style="width:200px;" id="dialogOrderDetailEditCustomerAmount"></select>';
        html += '<div class="textarea-wrapper" style="margin-top:10px;">';
        html += '<label for="dialogEditCustomerReason">' + __('Why_is_the_member_edited?') + '</label>';
        html += '<textarea name="dialogEditCustomerReason" id="dialogEditCustomerReason"></textarea>';
        html += '</div>';
        html += '<label class="checkbox">';
        html += '<input type="checkbox" name="dialogEditCustomerSendEmailToCustomers" id="dialogEditCustomerSendEmailToCustomers" />';
        html += '<span style="font-weight:normal;">' + __('Send_email_to_both_members') + '</span>';
        html += '</label>';
        html += '<input type="hidden" name="dialogOrderDetailEditCustomerOrderDetailId" id="dialogOrderDetailEditCustomerOrderDetailId" value="" />';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editCustomer/',
            {
                orderDetailId: $('#dialogOrderDetailEditCustomerOrderDetailId').val(),
                customerId: $('#dialogOrderDetailEditCustomerId').val(),
                editCustomerReason: $('#dialogEditCustomerReason').val(),
                amount: $('#dialogOrderDetailEditCustomerAmount').val(),
                sendEmailToCustomers: $('#dialogEditCustomerSendEmailToCustomers:checked').length > 0 ? 1 : 0,
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );

    },

    getOpenHandler : function(button, modalSelector) {

        foodcoopshop.Editor.initSmall('dialogEditCustomerReason', true);

        var customerDropdownSelector = '#dialogOrderDetailEditCustomerId';
        $(customerDropdownSelector).find('option[value=""]').remove();

        foodcoopshop.Admin.initCustomerDropdown(0, 0, 0, customerDropdownSelector, null, {
            placeholder: __('Please_select_new_member.')
        });

        var row = button.closest('tr');
        var orderDetailId = row.find('td:nth-child(2)').html();
        $(modalSelector + ' #dialogOrderDetailEditCustomerOrderDetailId').val(orderDetailId);

        var infoText = __('To_which_member_should_the_ordered_product_{0}_of_{1}_be_assigned_to?', '<b>' + row.find('td:nth-child(4) a.name-for-dialog').text() + '</b>');
        infoText = infoText.replace(/\{1\}/, '<b>' + row.find('td.customer-field span.customer-name-for-dialog').html() + '</b>');
        $(modalSelector + ' label[for="dialogOrderDetailEditCustomerId"]').html('<span style="font-weight:normal;">' + infoText + '</span>');

        var amount = row.find('.product-amount-for-dialog').html();
        var select = $(modalSelector + ' #dialogOrderDetailEditCustomerAmount');
        var selectLabel = $(modalSelector + ' label[for="dialogOrderDetailEditCustomerAmount"]');

        select.hide();
        selectLabel.hide();
        select.find('option').remove();
        for (var i = 1; i <= amount; i++) {
            var text = i;
            if (i == amount) {
                text += ' (' + __('all') + ')';
            }
            select.append($('<option>', {
                value: i,
                text: text
            }));
        }

        if (amount > 1) {
            select.prepend($('<option>', {
                value: '',
                text: __('Please_select...')
            }));
            select.show();
            selectLabel.show();
            select.val('');
        }

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

    }

};
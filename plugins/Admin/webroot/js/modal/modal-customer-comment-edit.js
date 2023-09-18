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
foodcoopshop.ModalCustomerCommentEdit = {

    init : function() {

        var modalSelector = '#customer-comment-edit-form';

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.modalCustomer.ChangeMemberComment,
            foodcoopshop.ModalCustomerCommentEdit.getHtml()
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalCustomerCommentEdit.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalCustomerCommentEdit.getCloseHandler();
        });

        $('.customer-comment-edit-button').on('click', function () {
            foodcoopshop.ModalCustomerCommentEdit.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var html = '<label>' + foodcoopshop.LocalizedJs.admin.Member + ': <b></b></label>';
        html += '<div class="textarea-wrapper">';
        html += '<textarea name="dialogCustomerComment" id="dialogCustomerComment"></textarea>';
        html += '</div>';
        html += '<input type="hidden" name="dialogCustomerId" id="dialogCustomerId" value="" />';
        return html;
    },

    getCloseHandler : function() {
        $('#cke_dialogCustomerComment').val('');
        $('#dialogCustomerId').val('');
    },

    getSuccessHandler : function() {

        if ($('#dialogCustomerId').val() == '') {
            return false;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/customers/editComment/',
            {
                customerId: $('#dialogCustomerId').val(),
                customerComment: $('#dialogCustomerComment').val(),
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    console.log(data);
                }
            }
        );
    },

    getOpenHandler : function(button, modalSelector) {

        foodcoopshop.Modal.removeTooltipster();

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        var editor = foodcoopshop.Editor.initSmall('dialogCustomerComment', true);

        var text = button.attr('originalTitle');
        if (text == foodcoopshop.LocalizedJs.admin.AddComment) {
            text = '';
        }

        var customerId = button.closest('tr').find('td:nth-child(2)').html();
        var customerName = button.closest('tr').find('td.name').text();

        editor.setEditorValue(text);
        $(modalSelector + ' #dialogCustomerId').val(customerId);

        $(modalSelector + ' #dialogCustomerId').val(customerId);
        $(modalSelector + ' label b').html(customerName);

    }

};
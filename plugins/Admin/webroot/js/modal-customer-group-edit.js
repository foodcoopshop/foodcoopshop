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
foodcoopshop.ModalCustomerGroupEdit = {

    init : function() {
        
        var modalSelector = '#customer-group-edit-form';
        
        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.modalCustomer.ChangeGroup,
            foodcoopshop.ModalCustomerGroupEdit.getHtml()
        );
        
        foodcoopshop.Modal.bindSaveButton(modalSelector, function() {
            foodcoopshop.ModalCustomerGroupEdit.getSaveHandler(modalSelector);
        });
        
        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalCustomerGroupEdit.getCloseHandler();
        });

        $('.customer-group-edit-button').on('click', function() {
            foodcoopshop.ModalCustomerGroupEdit.getOpenHandler($(this), modalSelector);
        });

    },
    
    getHtml : function() {
        return `
            <label for="dialogCustomerGroupEditText" id="dialogCustomerGroupEditText"></label><br />
            <select name="dialogCustomerGroupEditGroup" id="dialogCustomerGroupEditGroup" /></select>
            <input type="hidden" name="dialogCustomerGroupEditCustomerId" id="dialogCustomerGroupEditCustomerId" value="" />
        `;
    },
    
    getCloseHandler : function() {
        $('#dialogCustomerGroupEditGroupId').val('');
        $('#dialogCustomerGroupEditCustomerId').val('');
    },
    
    getSaveHandler : function(modalSelector) {
        
        if ($('#dialogCustomerGroupEditGroupId').val() == '' || $('#dialogCustomerGroupEditCustomerId').val() == '') {
            return false;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/customers/ajaxEditGroup/',
            {
                customerId: $('#dialogCustomerGroupEditCustomerId').val(),
                groupId: $('#dialogCustomerGroupEditGroup').val(),
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    $(modalSelector).hide();
                    alert(data.msg);
                }
            }
        );
    },
    
    getOpenHandler : function(button, modalSelector) {
        var selectedGroupId = button.closest('tr').find('td:nth-child(4) span.group-for-dialog').html();
        var select = $(modalSelector + ' #dialogCustomerGroupEditGroup');
        select.find('option').remove();
        select.append($('#selectgroupid').html());
        select.val(selectedGroupId);
        var html = foodcoopshop.LocalizedJs.admin.ChangeGroupFor + ': ' + button.closest('tr').find('td:nth-child(3) a').text();
        html += '<p style="font-weight: normal;"><br />' + foodcoopshop.LocalizedJs.admin.TheUserNeedsToSignInAgain + '</p>';
        $(modalSelector + ' #dialogCustomerGroupEditText').html(html);
        $(modalSelector + ' #dialogCustomerGroupEditCustomerId').val(button.closest('tr').find('td:nth-child(2)').html());
        $(modalSelector).modal();
    }

};
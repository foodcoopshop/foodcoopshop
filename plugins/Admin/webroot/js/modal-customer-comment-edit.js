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
foodcoopshop.ModalCustomerCommentEdit = {

    init : function() {
        
        var modalSelector = '#customer-comment-edit-form';
        
        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.modalCustomer.ChangeMemberComment,
            foodcoopshop.ModalCustomerCommentEdit.getHtml()
        );
        
        foodcoopshop.Modal.bindSaveButton(modalSelector, function() {
            foodcoopshop.ModalCustomerCommentEdit.getSaveHandler(modalSelector);
        });
        
        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalCustomerCommentEdit.getCloseHandler();
        });

        $('.customer-comment-edit-button').on('click', function () {
            foodcoopshop.ModalCustomerCommentEdit.getOpenHandler($(this), modalSelector);
        });
        
    },
        
    getHtml : function() {
        return `
            <div class="textarea-wrapper">
                <textarea class="ckeditor" name="dialogCustomerComment" id="dialogCustomerComment"></textarea>
            </div>
            <input type="hidden" name="dialogCustomerId" id="dialogCustomerId" value="" />
        `;
    },
    
    getCloseHandler : function() {
        $('#cke_dialogCustomerComment').val('');
        $('#dialogCustomerId').val('');
    },

    getSaveHandler : function() {
        
        if ($('#dialogCustomerId').val() == '') {
            return false;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/customers/editComment/',
            {
                customerId: $('#dialogCustomerId').val(),
                customerComment: CKEDITOR.instances['dialogCustomerComment'].getData()
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
        
        $(modalSelector).modal();

        foodcoopshop.Helper.initCkeditor('dialogCustomerComment');

        var text = button.attr('originalTitle');
        if (text == foodcoopshop.LocalizedJs.admin.AddComment) {
            text = '';
        }
        
        CKEDITOR.instances['dialogCustomerComment'].setData(text); // attr title is deleted after toolbar init
        $('#customer-comment-edit-form #dialogCustomerId').val(button.closest('tr').find('td:nth-child(2)').html());
        
        
    }

};
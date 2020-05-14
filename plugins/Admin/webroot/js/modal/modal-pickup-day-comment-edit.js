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
foodcoopshop.ModalPickupDayCommentEdit = {

    init : function() {
        
        var modalSelector = '#pickup-day-comment-edit-form';
        
        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.pickupDay.ChangePickupDayComment,
            foodcoopshop.ModalPickupDayCommentEdit.getHtml()
        );
        
        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalPickupDayCommentEdit.getSuccessHandler(modalSelector);
        });
        
        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalPickupDayCommentEdit.getCloseHandler();
        });

        $('.pickup-day-comment-edit-button').on('click', function () {
            foodcoopshop.ModalPickupDayCommentEdit.getOpenHandler($(this), modalSelector);
        });
        
    },
        
    getHtml : function() {
        var html = '<label><b>' + foodcoopshop.LocalizedJs.admin.Member + ': </b></label>';
            html += '<div class="textarea-wrapper">';
                html += '<textarea class="ckeditor" name="dialogPickupDayComment" id="dialogPickupDayComment"></textarea>';
            html += '</div>';
            html += '<input type="hidden" name="dialogCustomerId" id="dialogCustomerId" value="" />';
        return html;
    },
    
    getCloseHandler : function() {
        $('#cke_dialogPickupDayComment').val('');
        $('#dialogCustomerId').val('');
    },

    getSuccessHandler : function() {

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editPickupDayComment/',
            {
                customerId: $('#dialogCustomerId').val(),  
                pickupDay: $('input[name="pickupDay[]"]').val(), // filter-dropdown!
                pickupDayComment: CKEDITOR.instances['dialogPickupDayComment'].getData()
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
        
        $('.tooltipster-base ').remove(); // on mobile tooltipster is triggered on click - interferes with ckeditor
        
        $(modalSelector).modal();

        foodcoopshop.Helper.initCkeditor('dialogPickupDayComment', true);

        var text = button.attr('originalTitle');
        if (text == foodcoopshop.LocalizedJs.admin.AddComment) {
            text = '';
        }
        CKEDITOR.instances['dialogPickupDayComment'].setData(text);
        var customerId = button.closest('tr').find('td:nth-child(2)').html();
        var customerName = button.closest('tr').find('td:nth-child(3)').text();
        $(modalSelector + ' #dialogCustomerId').val(customerId);
        $(modalSelector + ' label b').html(customerName);
        
    }

};
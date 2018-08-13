/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.PickupDay = {

    changeProductsPickedUpCallbackYes : function() {
        foodcoopshop.PickupDay.changeProductsPickedUpCallback(1);
    },

    changeProductsPickedUpCallbackNo : function() {
        foodcoopshop.PickupDay.changeProductsPickedUpCallback(0);
    },
    
    changeProductsPickedUpCallback : function(state) {
        
        var dialogId = 'change-products-picked-up-form';
        var customerIds = $('#' + dialogId + ' #customerId').val().split(',');
        var pickupDay = $('input[name="pickupDay[]"]').val(); // filter-dropdown!
        
        $('#' + dialogId + ' .ajax-loader').show();
        $('.ui-dialog button').attr('disabled', 'disabled');

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/changeProductsPickedUp',
            {
                state: state,
                customerIds: customerIds,
                pickupDay: pickupDay
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    var form = $('#' + dialogId);
                    form.find('.ajax-loader').hide();
                    foodcoopshop.Admin.appendFlashMessageToDialog(form, data.msg);
                }
            }
        );
        
    },
    
    initChangeProductsPickedUpDialog : function(container, title, customerIds, customerName) {
        
        var dialogId = 'change-products-picked-up-form';
        var dialogHtml = foodcoopshop.DialogOrderDetail.getHtmlForOrderDetailProductsPickupDayEdit(dialogId, title);
        $(container).append(dialogHtml);

        var buttons = {};
        buttons['no'] = {
            text: foodcoopshop.LocalizedJs.helper.no,
            click: foodcoopshop.PickupDay.changeProductsPickedUpCallbackNo
        };
        buttons['yes'] = {
            text: foodcoopshop.LocalizedJs.helper.yes,
            click: foodcoopshop.PickupDay.changeProductsPickedUpCallbackYes
        };
        
        var dialog = $('#' + dialogId).dialog({
            autoOpen: false,
            height: 230,
            width: 500,
            modal: true,
            close: function () {
                $('#customerId').val('');
            },
            buttons: buttons
        });
        
        $('#' + dialogId + ' #customerId').val(customerIds.join(','));
        
        var infoMessage = $('#' + dialogId + ' p').html('');
        dialogHtml = '<p>' + foodcoopshop.LocalizedJs.pickupDay.ThisInformationServesThePickupTeamToSeeWhoWasAlreadyHere + '</p>';
        if (customerName != '') {
            dialogHtml += '<p>' + foodcoopshop.LocalizedJs.admin.Member + ': <b>' + customerName + '</b></p>';
        }
        infoMessage.html(dialogHtml);
        
        dialog.dialog('open');        
    },
    
    initChangeProductsPickedUpForAllCustomers: function(container) {
        $('.change-products-picked-up-all-customers-button').on('click', function () {
            var customerIds = [];
            $(container).find('table.list tr.data').each(function() {
                customerIds.push($(this).find('td:nth-child(2)').html());
            });
            var customerName = '';
            var title = foodcoopshop.LocalizedJs.pickupDay.WereTheProductsOfAllMembersPickedUp;
            foodcoopshop.PickupDay.initChangeProductsPickedUpDialog(container, title, customerIds, customerName);
        });
    },
    
    initChangeProductsPickedUpByCustomer: function(container) {
        $('.change-products-picked-up-button').on('click', function () {
            var customerIds = [$(this).closest('tr').find('td:nth-child(2)').html()];
            var customerName = $(this).closest('tr').find('td:nth-child(3)').text();
            var title = foodcoopshop.LocalizedJs.pickupDay.WereTheProductsPickedUp;
            foodcoopshop.PickupDay.initChangeProductsPickedUpDialog(container, title, customerIds, customerName);
        });
    },
    
    initPickupDayCommentEditDialog: function (container) {

        $('.pickup-day-comment-edit-button').on('click', function () {

            foodcoopshop.Helper.destroyCkeditor('dialogPickupDayComment');
            $('#pickup-day-comment-edit-form').remove();

            var dialog = foodcoopshop.PickupDay.createPickupDayCommentEditDialog(container);
            foodcoopshop.Helper.initCkeditor('dialogPickupDayComment');

            var text = $(this).attr('originalTitle');
            if (text == foodcoopshop.LocalizedJs.admin.AddComment) {
                text = '';
            }
            CKEDITOR.instances['dialogPickupDayComment'].setData(text);
            var customerId = $(this).closest('tr').find('td:nth-child(2)').html();
            $('#pickup-day-comment-edit-form #dialogCustomerId').val(customerId);
            dialog.dialog('open');

        });

    },
    
    createPickupDayCommentEditDialog: function (container) {

        var dialogId = 'pickup-day-comment-edit-form';
        var dialogHtml = foodcoopshop.DialogOrderDetail.getHtmlForOrderDetailPickupDayCommentEdit(dialogId);
        $(container).append(dialogHtml);

        var buttons = {};
        buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
        buttons['save'] = {
            text: foodcoopshop.LocalizedJs.helper.save,
            click: function() {
                $('#pickup-day-comment-edit-form .ajax-loader').show();
                $('.ui-dialog button').attr('disabled', 'disabled');
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
            }
        };

        var dialog = $('#' + dialogId).dialog({
            autoOpen: false,
            height: 460,
            width: 350,
            modal: true,
            close: function () {
                $('#cke_dialogPickupDayComment').val('');
                $('#dialogCustomerId').val('');
            },
            buttons: buttons
        });

        return dialog;
    }

};
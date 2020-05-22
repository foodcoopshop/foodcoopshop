/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
                    if (data.redirectUrl != '') {
                        document.location.href = data.redirectUrl;
                    } else {
                        document.location.reload();
                    }
                },
                onError: function (data) {
                    var form = $('#' + dialogId);
                    form.find('.ajax-loader').hide();
                    foodcoopshop.Helper.appendFlashMessageToDialog(form, data.msg);
                }
            }
        );

    },

    initChangeProductsPickedUpDialogNotGroupedBy : function(container) {
        $('.change-products-picked-up-button').on('click', function () {
            var selectedCustomer = $('.filter-container #customerid option:selected');
            if (selectedCustomer.length == 0) {
                return false;
            }
            var customerIds = [selectedCustomer.val()];
            var customerName = selectedCustomer.text();
            var title = foodcoopshop.LocalizedJs.pickupDay.WereTheProductsPickedUp;
            foodcoopshop.PickupDay.initChangeProductsPickedUpDialogGroupedByCustomer(container, title, customerIds, customerName, true);
        });
    },

    initChangeProductsPickedUpDialogGroupedByCustomer : function(container, title, customerIds, customerName, allowStatusFalse) {

        var dialogId = 'change-products-picked-up-form';
        var dialogHtml = foodcoopshop.DialogOrderDetail.getHtmlForOrderDetailProductsPickupDayEdit(dialogId, title);
        $(container).append(dialogHtml);

        var buttons = {};
        buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
        if (allowStatusFalse) {
            buttons['no'] = {
                text: foodcoopshop.LocalizedJs.helper.no,
                click: foodcoopshop.PickupDay.changeProductsPickedUpCallbackNo
            };
        }
        buttons['yes'] = {
            text: foodcoopshop.LocalizedJs.helper.yes,
            click: foodcoopshop.PickupDay.changeProductsPickedUpCallbackYes,
            style: 'float:left;margin-left:50px;'
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
        if (customerName != '') {
            infoMessage.html('<p><br />' + foodcoopshop.LocalizedJs.admin.Member + ': <b>' + customerName + '</b></p>');
        }
        if (customerName == '') {
            infoMessage.html('<p><br />' + foodcoopshop.LocalizedJs.pickupDay.WereTheProductsOfAllMembersPickedUp) + '</p>';
        }

        dialog.dialog('open');
    },

    initChangeProductsPickedUpForAllCustomers: function(container) {
        $('.change-products-picked-up-all-customers-button').on('click', function () {
            var customerIds = [];
            $(container).find('table.list tr.data').each(function() {
                customerIds.push($(this).find('td:nth-child(2)').html());
            });
            var title = foodcoopshop.LocalizedJs.pickupDay.AllProductsPickedUp;
            foodcoopshop.PickupDay.initChangeProductsPickedUpDialogGroupedByCustomer(container, title, customerIds, '', false);
        });
    },

    initChangeProductsPickedUpByCustomer: function(container) {
        $('.change-products-picked-up-button').on('click', function () {
            var customerIds = [$(this).closest('tr').find('td:nth-child(2)').html()];
            var customerName = $(this).closest('tr').find('td:nth-child(3)').text();
            var title = foodcoopshop.LocalizedJs.pickupDay.WereTheProductsPickedUp;
            foodcoopshop.PickupDay.initChangeProductsPickedUpDialogGroupedByCustomer(container, title, customerIds, customerName, true);
        });
    }

};
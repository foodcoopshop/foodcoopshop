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
foodcoopshop.ModalOrderDetailPickupDayEdit = {

    init : function () {

        var button = $('#changePickupDayOfSelectedProductsButton');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {

            var modalSelector = '#order-detail-pickup-day-edit';
            var orderDetailIds = foodcoopshop.Admin.getSelectedOrderDetailIds();

            var title = foodcoopshop.LocalizedJs.admin.ChangePickupDay + ': ' + orderDetailIds.length + ' ' + (
                orderDetailIds.length == 1 ? foodcoopshop.LocalizedJs.admin.product : foodcoopshop.LocalizedJs.admin.products
            );

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                title,
                foodcoopshop.ModalOrderDetailPickupDayEdit.getHtml()
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalOrderDetailPickupDayEdit.getSuccessHandler(modalSelector, orderDetailIds);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalOrderDetailPickupDayEdit.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalOrderDetailPickupDayEdit.getOpenHandler(modalSelector);

        });

    },

    getHtml : function() {
        var html = '';
        html += '<div class="field-wrapper">';
        html += '<label>' + foodcoopshop.LocalizedJs.admin.NewPickupDay + '</label>';
        html += '<input class="datepicker" type="text" name="dialogChangePickupDay" id="dialogChangePickupDay" /><br />';
        html += '</div>';
        html += '<p class="small">' + foodcoopshop.LocalizedJs.admin.ChangePickupDayInvoicesInfoText + '</p>';
        html += '<div class="textarea-wrapper">';
        html += '<label for="dialogEditPickupDayReason">' + foodcoopshop.LocalizedJs.admin.WhyIsPickupDayChanged +'</label>';
        html += '<textarea name="dialogEditPickupDayReason" id="dialogEditPickupDayReason"></textarea>';
        html += '</div>';
        html += '<label class="checkbox" style="margin-bottom:10px;">';
        html += '<input type="checkbox" name="dialogEditPickupdaySendEmail" id="dialogEditPickupdaySendEmail" value="" />';
        html += '<span style="font-weight:normal;">' + foodcoopshop.LocalizedJs.admin.SendEmailToMember + '</span>';
        html += '</label>';
        html += '<label class="checkbox">';
        html += '<input type="checkbox" name="dialogEditPickupdayResetOrderState" id="dialogEditPickupdayResetOrderState" value="" checked="checked" />';
        html += '<span style="font-weight:normal;">' + foodcoopshop.LocalizedJs.admin.ChangePickupDayResetOrderStateInfoText + '</span>';
        html += '</label>';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, orderDetailIds) {

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editPickupDay',
            {
                orderDetailIds: orderDetailIds,
                pickupDay: $('#dialogChangePickupDay').val(),
                editPickupDayReason: $('#dialogEditPickupDayReason').val(),
                sendEmail: $('#dialogEditPickupdaySendEmail:checked').length > 0 ? 1 : 0,
                resetOrderState: $('#dialogEditPickupdayResetOrderState:checked').length > 0 ? 1 : 0,
            },
            {
                onOk: function (data) {

                    var cookieName = 'SelectedOrderDetailIds';
                    var preselectedOrderDetailIds = Cookies.get(cookieName);
                    if (preselectedOrderDetailIds) {
                        preselectedOrderDetailIds = preselectedOrderDetailIds.split(',');
                        var selectedOrderDetailIds = preselectedOrderDetailIds;
                        var unselectedOrderDetailIds = orderDetailIds;
                        for (var index in unselectedOrderDetailIds) {
                            var removeId = unselectedOrderDetailIds[index];
                            selectedOrderDetailIds = $.grep(selectedOrderDetailIds, function(value) {
                                return value != removeId;
                            });
                        }
                        Cookies.set(cookieName, selectedOrderDetailIds, { expires: 1 });
                    }

                    document.location.reload();

                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );

    },

    getOpenHandler : function(modalSelector) {

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        var datepickerInput = $('#dialogChangePickupDay');
        datepickerInput.val($('.filter-container input[name="pickupDay[]"]').val());
        datepickerInput.datepicker();

        foodcoopshop.Editor.initSmall('dialogEditPickupDayReason', true);

    }

};
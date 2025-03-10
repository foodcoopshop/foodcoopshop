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
foodcoopshop.ModalProductDeliveryRhythmEdit = {

    initBulk : function() {

        var button = $('#editDeliveryRhythmForSelectedProducts');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {

            var unfilteredProductIds = foodcoopshop.Admin.getSelectedProductIds();

            var productIds = [];
            for(var i=0; i < unfilteredProductIds.length; i++) {
                var isStockProductElement = $('tr#product-' + unfilteredProductIds[i] + ' td.is-stock-product');
                if (!(isStockProductElement.length == 1 && isStockProductElement.find('i.fa-check').length == 1)) {
                    productIds.push(unfilteredProductIds[i]);
                }
            }

            var modalSelector = '#modal-product-delivery-rhythm-edit';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.dialogProduct.ChangeDeliveryRhythm,
                foodcoopshop.ModalProductDeliveryRhythmEdit.getHtml(productIds)
            );

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductDeliveryRhythmEdit.getCloseHandler(modalSelector);
            });

            var infoText = '';
            if (productIds.length == 1) {
                infoText = foodcoopshop.LocalizedJs.admin.YouSelectedOneProduct;
            } else {
                infoText = foodcoopshop.LocalizedJs.admin.YouSelected0Products.replace(/\{0\}/, '<b>' + productIds.length + '</b>');
            }
            infoText += '<br />';

            var selectedDeliveryRhythmType = foodcoopshop.Helper.getUniqueHtmlValueOfDomElements('tr.selected .delivery-rhythm-for-dialog .dropdown', '1-week');
            var selectedFirstDeliveryDay = foodcoopshop.Helper.getUniqueHtmlValueOfDomElements('tr.selected .delivery-rhythm-for-dialog .first-delivery-day', '');
            var selectedOrderPossibleUntil = foodcoopshop.Helper.getUniqueHtmlValueOfDomElements('tr.selected .delivery-rhythm-for-dialog .order-possible-until', '');
            var selectedSendOrderListWeekday = foodcoopshop.Helper.getUniqueHtmlValueOfDomElements('tr.selected .delivery-rhythm-for-dialog .send-order-list-weekday', '');
            var selectedSendOrderListDay = foodcoopshop.Helper.getUniqueHtmlValueOfDomElements('tr.selected .delivery-rhythm-for-dialog .send-order-list-day', '');

            foodcoopshop.ModalProductDeliveryRhythmEdit.getOpenHandler($(this), modalSelector, productIds, infoText, selectedDeliveryRhythmType, selectedFirstDeliveryDay, selectedOrderPossibleUntil, selectedSendOrderListWeekday, selectedSendOrderListDay);

        });

    },

    init : function() {

        $('.product-delivery-rhythm-edit-button').on('click', function() {

            var row = $(this).closest('tr');
            var productId = row.find('td.cell-id').html();
            var infoText = '<b>' + foodcoopshop.Admin.getProductNameForDialog(row) + '</b>';
            var selectedDeliveryRhythmType = row.find('td span.delivery-rhythm-for-dialog span.dropdown').html();
            var selectedFirstDeliveryDay = row.find('td span.delivery-rhythm-for-dialog span.first-delivery-day').html();
            var selectedOrderPossibleUntil = '';
            var selectedOrderPossibleUntilDataElement = row.find('td span.delivery-rhythm-for-dialog span.order-possible-until');
            if (selectedOrderPossibleUntilDataElement && selectedOrderPossibleUntilDataElement.length > 0) {
                selectedOrderPossibleUntil = selectedOrderPossibleUntilDataElement.html();
            }
            var selectedSendOrderListWeekday = row.find('td span.delivery-rhythm-for-dialog span.send-order-list-weekday').html();
            var selectedSendOrderListDay = '';
            var selectedSendOrderListDayDataElement = row.find('td span.delivery-rhythm-for-dialog span.send-order-list-day');
            if (selectedSendOrderListDayDataElement && selectedSendOrderListDayDataElement.length > 0) {
                selectedSendOrderListDay = selectedSendOrderListDayDataElement.html();
            }

            var modalSelector = '#modal-product-delivery-rhythm-edit';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.dialogProduct.ChangeDeliveryRhythm,
                foodcoopshop.ModalProductDeliveryRhythmEdit.getHtml([productId])
            );

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductDeliveryRhythmEdit.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalProductDeliveryRhythmEdit.getOpenHandler($(this), modalSelector, [productId], infoText, selectedDeliveryRhythmType, selectedFirstDeliveryDay, selectedOrderPossibleUntil, selectedSendOrderListWeekday, selectedSendOrderListDay);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getOpenHandler : function(button, modalSelector, productIds, infoText, selectedDeliveryRhythmType, selectedFirstDeliveryDay, selectedOrderPossibleUntil, selectedSendOrderListWeekday, selectedSendOrderListDay) {

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        $(modalSelector + ' label[for="dialogDeliveryRhythm"]').html(infoText);

        var select = $(modalSelector + ' #dialogDeliveryRhythmType');
        select.find('option').remove();
        select.append($('#rhythmtypes').html());
        select.on('change', function() {
            var elementToShow = 'default';
            if ($(this).val() !== null && $(this).val().match('individual')) {
                elementToShow = 'individual';
            }
            $(modalSelector + ' .dynamic-element').hide();
            $(modalSelector + ' .dynamic-element.' + elementToShow).show();
        });
        select.val(selectedDeliveryRhythmType);
        select.trigger('change');

        var select2 = $(modalSelector + ' #dialogDeliveryRhythmSendOrderListWeekday');
        select2.find('option').remove();
        select2.append($('#weekdays').html());
        select2.val(selectedSendOrderListWeekday);
        select2.trigger('change');

        foodcoopshop.Helper.initDatepicker();

        var firstDeliveryDayInput = $(modalSelector + ' #dialogDeliveryRhythmFirstDeliveryDay');
        firstDeliveryDayInput.val(selectedFirstDeliveryDay);
        firstDeliveryDayInput.datepicker();

        var orderPossibleUntilInput = $(modalSelector + ' #dialogDeliveryRhythmOrderPossibleUntil');
        orderPossibleUntilInput.val(selectedOrderPossibleUntil);
        orderPossibleUntilInput.datepicker();

        var sendOrderListDayInput = $(modalSelector + ' #dialogDeliveryRhythmSendOrderListDay');
        sendOrderListDayInput.val(selectedSendOrderListDay);
        sendOrderListDayInput.datepicker();

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalProductDeliveryRhythmEdit.getSuccessHandler(modalSelector, productIds);
        });

    },

    getHtml: function(productIds) {

        var html = '<label for="dialogDeliveryRhythm"></label>';
        html += '<div class="field-wrapper">';
        html += '<label>' + foodcoopshop.LocalizedJs.dialogProduct.DeliveryRhythm + '</label>';

        html += '<select name="dialogDeliveryRhythmType" id="dialogDeliveryRhythmType" /></select>';

        html += '<label style="margin-top:10px;" class="dynamic-element individual">' + foodcoopshop.LocalizedJs.dialogProduct.OrderPossibleUntil + '</label>';
        html += '<input style="margin-top:10px;" autocomplete="off" class="dynamic-element individual datepicker" type="text" name="dialogDeliveryRhythmOrderPossibleUntil" id="dialogDeliveryRhythmOrderPossibleUntil" /><br />';

        html += '<label class="dynamic-element default">' + foodcoopshop.LocalizedJs.dialogProduct.LastOrderWeekday + '</label>';
        html += '<select class="dynamic-element default" name="dialogDeliveryRhythmSendOrderListWeekday" id="dialogDeliveryRhythmSendOrderListWeekday" /></select><br />';
        html += '<label class="dynamic-element individual">' + foodcoopshop.LocalizedJs.dialogProduct.SendOrderListsDay + '</label>';
        html += '<input autocomplete="off" class="datepicker dynamic-element individual" type="text" name="dialogDeliveryRhythmSendOrderListDay" id="dialogDeliveryRhythmSendOrderListDay" /><br />';
        html += '<div style="float:left;margin-bottom:15px;line-height:14px;">';
        html += '<span class="small dynamic-element default">';
        html += foodcoopshop.LocalizedJs.dialogProduct.OrderListsAreSentAutomaticallyNextDayInTheMorning;
        html += '</span>';
        html += '<span class="small dynamic-element individual">';
        html += foodcoopshop.LocalizedJs.dialogProduct.OrderListsAreSentAutomaticallyOnThisDay;
        html += '</span>';
        html += '<br /></div>';

        html += '<label class="dynamic-element default">' + foodcoopshop.LocalizedJs.dialogProduct.FirstDeliveryDay + '</label>';
        html += '<label class="dynamic-element individual">' + foodcoopshop.LocalizedJs.dialogProduct.DeliveryDay + '</label>';

        html += '<input autocomplete="off" class="datepicker" type="text" name="dialogDeliveryRhythmFirstDeliveryDay" id="dialogDeliveryRhythmFirstDeliveryDay" /><br />';
        html += '<div style="float:right;line-height:14px;"><span class="small">';
        if (productIds.length == 1) {
            html += foodcoopshop.LocalizedJs.dialogProduct.FirstDeliveryDayInfoOneProduct;
        } else {
            html += foodcoopshop.LocalizedJs.dialogProduct.FirstDeliveryDayInfoMultipleProducts;
        }
        html += '</span><br /></div>';

        html += '<div style="float:left;line-height:14px;margin-top:10px;"><span class="small">';
        html += foodcoopshop.LocalizedJs.admin.ChangeDeliveryRhythmForMultipleProductsTip;
        html += '</span><br /></div>';

        html += '</div>';
        html += '<p style="margin-top:10px;float:right;margin-bottom:0;"><a target="_blank" href="' + foodcoopshop.LocalizedJs.dialogProduct.DocsUrlOrderHandling + '">' + foodcoopshop.LocalizedJs.dialogProduct.InfoPageForDeliveryRhythm + '</a></p>';
        html += '<input type="hidden" name="dialogDeliveryRhythmProductId" id="dialogDeliveryRhythmProductId" value="" />';
        return html;
    },

    getSuccessHandler : function(modalSelector, productIds) {

        if (productIds.length == 0) {
            foodcoopshop.Modal.appendFlashMessageError(modalSelector, foodcoopshop.LocalizedJs.helper.anErrorOccurred);
            foodcoopshop.Modal.resetButtons(modalSelector);
            return;
        }

        var data = {
            productIds: productIds,
            deliveryRhythmType: $('#dialogDeliveryRhythmType').val(),
            deliveryRhythmFirstDeliveryDay: $('#dialogDeliveryRhythmFirstDeliveryDay').val(),
            deliveryRhythmOrderPossibleUntil: $('#dialogDeliveryRhythmOrderPossibleUntil').val(),
            deliveryRhythmSendOrderListWeekday: $('#dialogDeliveryRhythmSendOrderListWeekday').val(),
            deliveryRhythmSendOrderListDay: $('#dialogDeliveryRhythmSendOrderListDay').val()
        };

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editDeliveryRhythm/',
            data,
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

    }

};
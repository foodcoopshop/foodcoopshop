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
foodcoopshop.ModalProductQuantityEdit = {

    init : function() {

        var modalSelector = '#modal-product-quantity-edit';

        $('a.product-quantity-edit-button').on('click', function () {
            foodcoopshop.ModalProductQuantityEdit.getOpenHandler($(this), modalSelector);
            foodcoopshop.Calculator.init(modalSelector);
        });

    },

    getStep : function(isPricePerUnitEnabled) {
        return Boolean(isPricePerUnitEnabled) ? '0.001' : '1';
    },

    getHtmlForProductQuantityEdit : function() {
        var html = '<label for="dialogQuantityQuantity"></label><br />';
        html += '<div class="field-wrapper">';
        html += '<label class="checkbox">';
        html += '<input type="checkbox" name="dialogQuantityAlwaysAvailable" id="dialogQuantityAlwaysAvailable" />';
        html += ' ' + __('Is_the_product_always_available?');
        html += '</label>';
        html += '</div>';
        html += '<div class="field-wrapper quantity-wrapper">';
        html += '<hr />';
        html += '<label>' + __('Available_amount') + '</label>';
        html += '<input type="number" step="1" name="dialogQuantityQuantity" id="dialogQuantityQuantity" class="calculator-output"/><a class="calculator-toggle-button" href="javascript:void(0);"><i class="fas fa-calculator"></i></a><br />';
        html += '<input id="dialogQuantityCalculator" class="calculator-input" placeholder="' + __('Example_given_abbr') + ' 167+142" type="text" /><br />';
        html += '<input type="text" name="dialogQuantityChangeReason" id="dialogQuantityChangeReason" maxlength="200" placeholder="' + __('Reason_for_change') + '" /><br />';
        html += '<hr />';
        html += '</div>';
        html += '<div class="field-wrapper quantity-wrapper">';
        html += '<label>' + __('Default_quantity_after_sending_order_lists') + '</label>';
        html += '<input type="number" step="1" name="dialogQuantityDefaultQuantityAfterSendingOrderLists" id="dialogQuantityDefaultQuantityAfterSendingOrderLists" />';
        html += '<span style="float:left;" class="small">' + __('After_the_order_lists_are_sent_available_amount_is_set_to_this_value.') + '</span>';
        html += '</div>';
        html += '<input type="hidden" name="dialogQuantityProductId" id="dialogQuantityProductId" value="" />';
        return html;
    },

    getHtmlForProductQuantityIsStockProductEdit : function(isPricePerUnitEnabled, unitName, useWeightAsAmount) {
        let unitNameString = isPricePerUnitEnabled && useWeightAsAmount ? ' - in ' + unitName : '';
        var html = '<label for="dialogQuantityQuantity"></label><br />';
        html += '<div class="field-wrapper">';
        html += '<label>' + __('Current_stock') + unitNameString + '</label>';
        html += '<input type="number" step="' + this.getStep(isPricePerUnitEnabled) + '" name="dialogQuantityQuantity" id="dialogQuantityQuantity" class="calculator-output" /><a class="calculator-toggle-button" href="javascript:void(0);"><i class="fas fa-calculator"></i></a><br />';
        html += '<input id="dialogQuantityCalculator" class="calculator-input" placeholder="' + __('Example_given_abbr') + ' 167+142" type="text" /><br />';
        html += '<input type="text" name="dialogQuantityChangeReason" id="dialogQuantityChangeReason" maxlength="200" placeholder="' + __('Reason_for_change') + '" /><br />';
        html += '<hr />';
        html += '</div>';
        html += '<div class="field-wrapper">';
        html += '<label>' + __('Orders_possible_until_amount_of') + unitNameString + '<br /><span class="small">' + __('zero_or_smaller_zero') + '.</span></label>';
        html += '<input max="0" type="number" step="' + this.getStep(isPricePerUnitEnabled) + '" name="dialogQuantityQuantityLimit" id="dialogQuantityQuantityLimit" /><br />';
        html += '<hr />';
        html += '</div>';
        html += '<div class="field-wrapper">';
        html += '<label>' + __('Notification_if_amount_lower_than') + unitNameString + ' (' + __('Minimal_stock_amount') + ')<br /><span class="small" style="float:left;">' + __('For_manufacturers_and_contact_persons._Can_be_changed_in_manufacturer_settings.') + '</span></label>';
        html += '<input type="number" step="' + this.getStep(isPricePerUnitEnabled) + '" name="dialogQuantitySoldOutLimit" id="dialogQuantitySoldOutLimit" /><br />';
        html += '</div>';
        html += '<input type="hidden" name="dialogQuantityProductId" id="dialogQuantityProductId" value="" />';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, row) {

        if ($('#dialogQuantityProductId').val() == '') {
            foodcoopshop.Modal.appendFlashMessageError(modalSelector, __('An_error_occurred'));
            foodcoopshop.Modal.resetButtons(modalSelector);
            return;
        }

        var data = {
            productId: $('#dialogQuantityProductId').val(),
            quantity: $('#dialogQuantityQuantity').val(),
            alwaysAvailable: $('#dialogQuantityAlwaysAvailable:checked').length > 0 ? 1 : 0,
            defaultQuantityAfterSendingOrderLists: $('#dialogQuantityDefaultQuantityAfterSendingOrderLists').val() == '' ? null : $('#dialogQuantityDefaultQuantityAfterSendingOrderLists').val(),
            changeReason: $('#dialogQuantityChangeReason').val(),
        };

        if (foodcoopshop.Admin.isAdvancedStockManagementEnabled(row)) {
            data.quantityLimit = $('#dialogQuantityQuantityLimit').val();
            data.soldOutLimit = $('#dialogQuantitySoldOutLimit').val();
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editQuantity/',
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

    },

    getOpenHandler : function(button, modalSelector) {

        var row = button.closest('tr');

        var html;
        if (foodcoopshop.Admin.isAdvancedStockManagementEnabled(row)) {
            var productId = row.find('td.cell-id').html();
            var unitObject = $('#product-unit-object-' + productId);
            let pricePerUnitEnabled = false;
            let useWeightAsAmount = false;
            let unitName = '';
            if (unitObject.length > 0) {
                unitData = unitObject.data('product-unit-object');
                pricePerUnitEnabled = unitData.price_per_unit_enabled;
                unitName = unitData.name;
                useWeightAsAmount = unitData.use_weight_as_amount;
            }
            html = foodcoopshop.ModalProductQuantityEdit.getHtmlForProductQuantityIsStockProductEdit(pricePerUnitEnabled, unitName, useWeightAsAmount);
        } else {
            html = foodcoopshop.ModalProductQuantityEdit.getHtmlForProductQuantityEdit();
        }

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            __('Change_amount'),
            html,
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalProductQuantityEdit.getSuccessHandler(modalSelector, row);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalProductQuantityEdit.getCloseHandler(modalSelector);
        });

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        if (foodcoopshop.Admin.isAdvancedStockManagementEnabled(row)) {
            if (row.find('i.quantity-limit-for-dialog').length > 0) {
                let quantityLimitValForOverlay = row.find('i.quantity-limit-for-dialog').html();
                quantityLimitValForOverlay = foodcoopshop.Helper.getStringAsFloat(quantityLimitValForOverlay);
                $(modalSelector + ' #dialogQuantityQuantityLimit').val(quantityLimitValForOverlay);
            } else {
                $(modalSelector + ' #dialogQuantityQuantityLimit').val(0);
            }
            if (row.find('i.sold-out-limit-for-dialog').length > 0) {
                if (row.find('i.sold-out-limit-for-dialog').html().match('fa-times')) {
                    $(modalSelector + ' #dialogQuantitySoldOutLimit').val('');
                } else {
                    let quantitySoldOutLimitValForOverlay = row.find('i.sold-out-limit-for-dialog').html();
                    quantitySoldOutLimitValForOverlay = foodcoopshop.Helper.getStringAsFloat(quantitySoldOutLimitValForOverlay);
                    $(modalSelector + ' #dialogQuantitySoldOutLimit').val(quantitySoldOutLimitValForOverlay);
                }
            } else {
                $(modalSelector + ' #dialogQuantitySoldOutLimit').val(0);
            }
        }

        foodcoopshop.Admin.bindToggleQuantityQuantity(modalSelector);

        let quantityValForOverlay = row.find('span.quantity-for-dialog').html();
        quantityValForOverlay = foodcoopshop.Helper.getStringAsFloat(quantityValForOverlay);
        $(modalSelector + ' #dialogQuantityQuantity').val(quantityValForOverlay);
        $(modalSelector + ' #dialogQuantityCalculator').val(quantityValForOverlay.toString().replace('.', ','));
        if (row.find('.amount').html().match('fa-infinity')) {
            $(modalSelector + ' #dialogQuantityAlwaysAvailable').trigger('click');
        }
        $(modalSelector + ' #dialogQuantityDefaultQuantityAfterSendingOrderLists').val(row.find('span.default-quantity-after-sending-order-lists-for-dialog').html());
        $(modalSelector + ' #dialogQuantityProductId').val(row.find('td.cell-id').html());
        var label = foodcoopshop.Admin.getProductNameForDialog(row);
        $(modalSelector + ' label[for="dialogQuantityQuantity"]').html('<b>' + label + '</b>');

    }

};
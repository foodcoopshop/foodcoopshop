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
foodcoopshop.ModalProductPriceEdit = {

    changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled: false,
    openOrderDetailPriceOnProductPriceChangeEnabled: false,

    init : function(changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled, openOrderDetailPriceOnProductPriceChangeEnabled) {

        var modalSelector = '#modal-product-price-edit';

        this.changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled = changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled;
        this.openOrderDetailPriceOnProductPriceChangeEnabled = openOrderDetailPriceOnProductPriceChangeEnabled;

        $('a.product-price-edit-button').on('click', function () {
            foodcoopshop.ModalProductPriceEdit.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function(isStockProduct) {
        var html = '<label for="dialogPricePrice"></label><br />';
        html += '<label class="radio">';
        html += '<input type="radio" name="dialogPricePricePerUnitEnabled" value="price" checked="checked" class="price" />';
        html += foodcoopshop.LocalizedJs.dialogProduct.PricePerUnit;
        html += '</label>';
        html += '<div class="price-wrapper">';
        html += '<input type="number" step="0.01" name="dialogPricePrice" id="dialogPricePrice" value="" />';
        html += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b> (' + foodcoopshop.LocalizedJs.dialogProduct.gross + ')<br />';
        html += '</div>';
        html += '<hr />';
        html += '<label class="radio">';
        html += '<input type="radio" name="dialogPricePricePerUnitEnabled" value="price-per-unit" class="price-per-unit"/>';
        html += foodcoopshop.LocalizedJs.dialogProduct.PricePerWeightForAdaptionAfterDelivery;
        html += '</label>';
        html += '<div class="price-per-unit-wrapper deactivated">';
        html += '<input type="number" step="0.01" name="dialogPricePriceInclPerUnit" id="dialogPricePriceInclPerUnit" value="" />';
        html += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b> (' + foodcoopshop.LocalizedJs.dialogProduct.gross + ') ' + foodcoopshop.LocalizedJs.dialogProduct.for;
        html += '<select name="dialogPriceUnitAmount" id="dialogPriceUnitAmount">';
        html += '<option value="1" selected>1</option>';
        html += '<option value="10">10</option>';
        html += '<option value="20">20</option>';
        html += '<option value="50">50</option>';
        html += '<option value="100">100</option>';
        html += '<option value="200">200</option>';
        html += '<option value="500">500</option>';
        html += '<option value="1000">1.000</option>';
        html += '</select> ';
        html += '<select name="dialogPriceUnitName" id="dialogPriceUnitName">';
        html += '<option value="kg" selected>kg</option>';
        html += '<option value="g">g</option>';
        html += '<option value="l">l</option>';
        html += '</select><br />';
        html += '<input type="number" name="dialogPriceQuantityInUnits" id="dialogPriceQuantityInUnits" value="" /> ' + foodcoopshop.LocalizedJs.dialogProduct.approximateDeliveryWeightIn0PerUnit.replaceI18n(0, '<span class="unit-name-placeholder">kg</span>');
        if (isStockProduct) {
            html += '<label class="checkbox" style="margin-top:10px ! important;">';
            html += '<input type="checkbox" name="dialogPriceUseWeightAsAmount" id="dialogPriceUseWeightAsAmount" />';
            html += '<span style="font-weight:normal;">' + foodcoopshop.LocalizedJs.dialogProduct.EditPriceUseWeightAsAmount + '</span>';
            html += '</label>';
        }
        html += '</div>';
        if (this.openOrderDetailPriceOnProductPriceChangeEnabled) {
            html += '<hr />';
            html += '<label class="checkbox" style="margin-top:10px ! important;">';
            html += '<input type="checkbox" name="dialogPriceChangeOpenOrderDetails" id="dialogPriceChangeOpenOrderDetails" value="" ' + (this.changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled ? 'checked="checked"' : '') + '/>';
            html += '<span style="font-weight:normal;">' + foodcoopshop.LocalizedJs.dialogProduct.EditPriceChangeOpenOrderDetailsInfoText + '</span>';
            html += '</label>';
        }
        html += '<input type="hidden" name="dialogPriceProductId" id="dialogPriceProductId" value="" />';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {

        var pricePerUnitEnabled = $('input[name="dialogPricePricePerUnitEnabled"]:checked').val() == 'price-per-unit' ? 1 : 0;

        var priceInclPerUnit = $('#dialogPricePriceInclPerUnit').val();
        var quantityInUnits = $('#dialogPriceQuantityInUnits').val();

        if ($('#dialogPriceProductId').val() == '') {
            foodcoopshop.Modal.appendFlashMessage(modalSelector, foodcoopshop.LocalizedJs.helper.anErrorOccurred);
            foodcoopshop.Modal.resetButtons(modalSelector);
            return;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editPrice/',
            {
                productId: $('#dialogPriceProductId').val(),
                price: $('#dialogPricePrice').val(),
                priceInclPerUnit: priceInclPerUnit,
                pricePerUnitEnabled: pricePerUnitEnabled,
                priceUnitName: $('#dialogPriceUnitName').val(),
                priceUnitAmount: $('#dialogPriceUnitAmount').val(),
                priceQuantityInUnits : quantityInUnits,
                priceUseWeightAsAmount: $('#dialogPriceUseWeightAsAmount:checked').length > 0 ? 1 : 0,
                priceChangeOpenOrderDetails: $('#dialogPriceChangeOpenOrderDetails:checked').length > 0 ? 1 : 0,
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessage(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );

    },

    getOpenHandler : function(button, modalSelector) {

        var row = button.closest('tr');
        var productId = row.find('td.cell-id').html();

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.dialogProduct.ChangePrice,
            foodcoopshop.ModalProductPriceEdit.getHtml(foodcoopshop.Admin.isAdvancedStockManagementEnabled(row)),
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalProductPriceEdit.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalProductPriceEdit.getCloseHandler(modalSelector);
        });

        $(modalSelector + ' input[name="dialogPricePricePerUnitEnabled"]').on('change', function() {
            var priceAsUnitWrapper = $(modalSelector + ' .price-per-unit-wrapper');
            var priceWrapper = $(modalSelector + ' .price-wrapper');
            if ($(this).val() == 'price-per-unit') {
                priceAsUnitWrapper.removeClass('deactivated');
                priceWrapper.addClass('deactivated');
            } else {
                priceAsUnitWrapper.addClass('deactivated');
                priceWrapper.removeClass('deactivated');
            }
        });

        $(modalSelector + ' input, select').on('focus', function() {
            $(this).closest('div').prev().find('input').trigger('click');
        });

        var radioMainSelector = modalSelector + ' input[name="dialogPricePricePerUnitEnabled"]';
        var radio;
        var unitData = {};
        var unitObject = $('#product-unit-object-' + productId);
        if (unitObject.length > 0) {
            unitData = unitObject.data('product-unit-object');
            if (unitData.price_per_unit_enabled === 1) {
                radio = $(radioMainSelector + '.price-per-unit');
            }
            $(modalSelector + ' #dialogPricePriceInclPerUnit').val(unitData.price_incl_per_unit);
            $(modalSelector + ' #dialogPriceUnitName').val(unitData.name);
            $(modalSelector + ' #dialogPriceUnitName').trigger('change');
            $(modalSelector + ' #dialogPriceUnitAmount').val(unitData.amount);
            $(modalSelector + ' #dialogPriceQuantityInUnits').val(parseFloat(unitData.quantity_in_units));
        }
        if (radio === undefined) {
            radio = $(radioMainSelector + '.price');
        }
        radio.prop('checked', true);
        radio.trigger('change');

        var price = foodcoopshop.Helper.getCurrencyAsFloat(row.find('span.price-for-dialog').html()).toFixed(2);
        $(modalSelector + ' #dialogPricePrice').val(price);
        $(modalSelector + ' #dialogPriceProductId').val(productId);
        var label = foodcoopshop.Admin.getProductNameForDialog(row);
        $(modalSelector + ' label[for="dialogPricePrice"]').html('<b>' + label + '</b>');
        $(modalSelector + ' input[name="dialogPriceUseWeightAsAmount"]').prop('checked', unitData.use_weight_as_amount == 1);

        $('#dialogPriceUnitName').on('change', function() {
            var stepValue = '0.001';
            var minValue = '0.001';
            if ($(this).val() == 'g') {
                stepValue = 1;
                minValue = 1;
            }
            var quantityInUnitsField = $(modalSelector + ' #dialogPriceQuantityInUnits');
            quantityInUnitsField.attr('step', stepValue);
            quantityInUnitsField.attr('min', minValue);
            $(modalSelector + ' span.unit-name-placeholder').html($(this).val());
        }).trigger('change');

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

    }

};
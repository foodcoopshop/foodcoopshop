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

foodcoopshop.ModalOrderDetailProductQuantityEdit = {

    init : function() {

        var modalSelector = '#order-detail-product-quantity-edit-form';

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.AdaptWeight,
            foodcoopshop.ModalOrderDetailProductQuantityEdit.getHtml()
        );

        foodcoopshop.Calculator.init(modalSelector);

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderDetailProductQuantityEdit.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalOrderDetailProductQuantityEdit.getCloseHandler();
        });

        $('.order-detail-product-quantity-edit-button').on('click', function() {
            foodcoopshop.ModalOrderDetailProductQuantityEdit.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var html = '<label for="dialogOrderDetailProductQuantityQuantity"></label><br />';
        html += '<br /><span class="quantity-string">' + foodcoopshop.LocalizedJs.admin.DeliveredWeight + '</span>: <input type="number" class="calculator-output" min="0.01" step="0.01" name="dialogOrderDetailProductQuantityQuantity" id="dialogOrderDetailProductQuantityQuantity" value="" />';
        html += '<b></b>';
        html += '<br />';
        html += '<input type="hidden" name="dialogOrderDetailProductQuantityOrderDetailId" id="dialogOrderDetailProductQuantityOrderDetailId" value="" />';
        html += '<ul style="margin-top:5px;margin-bottom:10px;">';
        html += '<li>';
        html += '<a id="dialogOrderDetailProductQuantityShowCalculator" class="calculator-toggle-button" href="javascript:void(0);" style="line-height:29px;">';
        html += foodcoopshop.LocalizedJs.admin.Calculator;
        html += '</a>';
        html += '<input id="dialogOrderDetailProductQuantityCalculator" class="calculator-input" style="margin-left:10px;width:178px;" placeholder="' + foodcoopshop.LocalizedJs.admin.ExampleGivenAbbr + ' 167+142" type="text" />';
        html += '</li>';
        html += '<li class="price-per-unit-base-info"></li>';
        html += '<li>' + foodcoopshop.LocalizedJs.admin.PriceIsAutomaticallyAdaptedAfterSave + '</li>';
        html += '</ul>';
        return html;
    },

    getCloseHandler : function() {
        $('#dialogOrderDetailProductQuantityQuantity').val('');
        $('#dialogOrderDetailProductQuantityOrderDetailId').val('');
        $('#dialogOrderDetailProductQuantityCalculator').val('').hide();
        $('#flashMessage').remove();
    },

    getSuccessHandler : function(modalSelector) {

        var productQuantity = $('#dialogOrderDetailProductQuantityQuantity').val();
        if ($('#dialogOrderDetailProductQuantityOrderDetailId').val() == '') {
            return false;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editProductQuantity/',
            {
                orderDetailId: $('#dialogOrderDetailProductQuantityOrderDetailId').val(),
                productQuantity: productQuantity
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    foodcoopshop.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );

    },

    getOpenHandler : function(button, modalSelector) {

        var row = button.closest('tr');
        var orderDetailId = row.find('td:nth-child(2)').html();
        var unitName = row.find('td.quantity-field span.unit-name').html().trim();
        var quantity = row.find('td.quantity-field span.quantity-in-units').html();
        var quantityInUnitsField = $(modalSelector + ' #dialogOrderDetailProductQuantityQuantity');

        quantityInUnitsField.val(foodcoopshop.Helper.getStringAsFloat(quantity));

        $(modalSelector + ' b').html(unitName);
        $(modalSelector + ' #dialogOrderDetailProductQuantityOrderDetailId').val(orderDetailId);

        var amount = row.find('td:nth-child(3) .product-amount-for-dialog').html();
        var label = '<b>' + row.find('td:nth-child(4) a.name-for-dialog').text() + '</b>';
        label += ' <span style="font-weight:normal;">(';
        var quantityString = $(modalSelector + ' span.quantity-string');
        var newHtml = '';
        if (amount > 1) {
            label += '<b>' + amount + '</b>' + 'x ';
            var regExpDeliveredWeight = new RegExp(foodcoopshop.LocalizedJs.admin.DeliveredWeight);
            newHtml = quantityString.html().replace(regExpDeliveredWeight, foodcoopshop.LocalizedJs.admin.DeliveredTotalWeight);
        } else {
            var regExpDeliveredTotalWeight = new RegExp(foodcoopshop.LocalizedJs.admin.DeliveredTotalWeight);
            newHtml = quantityString.html().replace(regExpDeliveredTotalWeight, foodcoopshop.LocalizedJs.admin.DeliveredWeight);
        }
        quantityString.html(newHtml);
        label += foodcoopshop.LocalizedJs.admin.orderedBy + ' ' + row.find('td.customer-field span.customer-name-for-dialog').html() + ')';
        $(modalSelector + ' label[for="dialogOrderDetailProductQuantityQuantity"]').html(label);

        var stepValue = '0.001';
        var minValue = '0.001';
        switch(unitName) {
        case 'g':
            stepValue = 1;
            minValue = 1;
        }
        quantityInUnitsField.attr('step', stepValue);
        quantityInUnitsField.attr('min', minValue);

        var pricePerUnitBaseInfo = row.find('td.quantity-field span.price-per-unit-base-info').html();
        $(modalSelector + ' li.price-per-unit-base-info').html(foodcoopshop.LocalizedJs.admin.BasePrice + ': ' + pricePerUnitBaseInfo);

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        $('#dialogOrderDetailProductQuantityQuantity').focus().select();

    }

};
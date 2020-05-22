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
foodcoopshop.ModalOrderDetailProductQuantityEdit = {

    init : function() {

        var modalSelector = '#order-detail-product-quantity-edit-form';

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.AdaptWeight,
            foodcoopshop.ModalOrderDetailProductQuantityEdit.getHtml()
        );

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
            html += '<br /><span class="quantity-string">' + foodcoopshop.LocalizedJs.admin.DeliveredWeight + '</span>: <input type="number" min="0.01" step="0.01" name="dialogOrderDetailProductQuantityQuantity" id="dialogOrderDetailProductQuantityQuantity" value="" />';
            html += '<b></b>';
            html += '<br />';
            html += '<input type="hidden" name="dialogOrderDetailProductQuantityOrderDetailId" id="dialogOrderDetailProductQuantityOrderDetailId" value="" />';
            html += '<ul style="margin-top:5px;">';
                html += '<li class="price-per-unit-base-info"></li>';
                html += '<li>' + foodcoopshop.LocalizedJs.admin.PriceIsAutomaticallyAdaptedAfterSave + '</li>';
                html += '<li>' + foodcoopshop.LocalizedJs.admin.FieldIsRedIfWeightNotYetAdapted + '</li>';
            html += '</ul>';
            html += '<label class="checkbox">';
                html += '<input type="checkbox" name="dialogOrderDetailProductQuantityDoNotChangePrice" id="dialogOrderDetailProductQuantityDoNotChangePrice" value="" />';
                html += '<span style="font-weight:normal;">' + foodcoopshop.LocalizedJs.admin.DoNotAutomaticallyAdaptPriceJustChangeWeight + '</span>';
            html += '</label>';
            html += '<br />';
        return html;
    },

    getCloseHandler : function() {
        $('#dialogOrderDetailProductQuantityQuantity').val('');
        $('#dialogOrderDetailProductQuantityOrderDetailId').val('');
        $('#dialogOrderDetailProductQuantityDoNotChangePrice').prop('checked', false);
        $('#flashMessage').remove();
    },

    getSuccessHandler : function(modalSelector) {

        var productQuantity = $('#dialogOrderDetailProductQuantityQuantity').val();
        if (isNaN(parseFloat(productQuantity.replace(/,/, '.'))) || productQuantity < 0) {
            foodcoopshop.Modal.appendFlashMessage(modalSelector, foodcoopshop.LocalizedJs.admin.DeliveredWeightNeedsToBeGreaterThan0);
            foodcoopshop.Modal.resetButtons(modalSelector);
            return false;
        }

        if ($('#dialogOrderDetailProductQuantityOrderDetailId').val() == '') {
            return false;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editProductQuantity/',
            {
                orderDetailId: $('#dialogOrderDetailProductQuantityOrderDetailId').val(),
                productQuantity: productQuantity,
                doNotChangePrice: $('#dialogOrderDetailProductQuantityDoNotChangePrice:checked').length > 0 ? 1 : 0
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
        var orderDetailId = row.find('td:nth-child(2)').html();
        var unitName = row.find('td:nth-child(8) span.unit-name').html().trim();
        var quantity = row.find('td:nth-child(8) span.quantity-in-units').html();
        var quantityInUnitsField = $(modalSelector + ' #dialogOrderDetailProductQuantityQuantity');

        quantityInUnitsField.val(foodcoopshop.Helper.getStringAsFloat(quantity));

        $(modalSelector + ' b').html(unitName);
        $(modalSelector + ' #dialogOrderDetailProductQuantityOrderDetailId').val(orderDetailId);

        var amount = row.find('td:nth-child(3) .product-amount-for-dialog').html();
        var label = row.find('td:nth-child(4) a.name-for-dialog').html();
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
        label += foodcoopshop.LocalizedJs.admin.orderedBy + ' ' + row.find('td:nth-child(9) span.customer-name-for-dialog').html() + ')';
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

        var pricePerUnitBaseInfo = row.find('td:nth-child(8) span.price-per-unit-base-info').html();
        $(modalSelector + ' li.price-per-unit-base-info').html(foodcoopshop.LocalizedJs.admin.BasePrice + ': ' + pricePerUnitBaseInfo);

        $(modalSelector).modal();

        foodcoopshop.Helper.changeInputNumberToTextForEdge();
        $('#dialogOrderDetailProductQuantityQuantity').focus();

    }

};
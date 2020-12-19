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
foodcoopshop.ModalOrderDetailProductPriceEdit = {

    init : function() {

        var modalSelector = '#order-detail-product-price-edit-form';

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.AdaptPrice,
            foodcoopshop.ModalOrderDetailProductPriceEdit.getHtml()
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderDetailProductPriceEdit.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalOrderDetailProductPriceEdit.getCloseHandler();
        });

        $('.order-detail-product-price-edit-button').on('click', function() {
            foodcoopshop.ModalOrderDetailProductPriceEdit.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var html = '<label for="dialogOrderDetailProductPricePrice"></label><br />';
        html += '<input type="number" step="0.01" min="0.01" name="dialogOrderDetailProductPricePrice" id="dialogOrderDetailProductPricePrice" value="" />';
        html += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b>';
        html += '<div class="textarea-wrapper" style="margin-top: 10px;">';
        html += '<label for="dialogEditPriceReason">' + foodcoopshop.LocalizedJs.admin.WhyIsPriceAdapted + '</label>';
        html += '<textarea class="ckeditor" name="dialogEditPriceReason" id="dialogEditPriceReason"></textarea>';
        html += '</div>';
        html += '<input type="hidden" name="dialogOrderDetailProductPriceOrderDetailId" id="dialogOrderDetailProductPriceOrderDetailId" value="" />';
        return html;

    },

    getCloseHandler : function() {
        $('#dialogOrderDetailProductPrice').val('');
        $('#dialogOrderDetailProductPriceOrderDetailId').val('');
        foodcoopshop.Helper.destroyCkeditor('dialogEditPriceReason');
        $('#flashMessage').remove();
    },

    getSuccessHandler : function(modalSelector) {

        if ($('#dialogOrderDetailProductPriceOrderDetailId').val() == '') {
            return;
        }

        var ckeditorData = CKEDITOR.instances['dialogEditPriceReason'].getData().trim();
        if (ckeditorData == '') {
            foodcoopshop.Modal.appendFlashMessage(modalSelector, foodcoopshop.LocalizedJs.admin.AdaptPriceReasonIsMandatory);
            foodcoopshop.Modal.resetButtons(modalSelector);
            return;
        }

        var productPrice = $('#dialogOrderDetailProductPricePrice').val();
        var timebasedCurrencyPriceObject = $('#dialogOrderDetailProductPriceTimebasedCurrencyPrice');
        if (timebasedCurrencyPriceObject.length > 0) {
            productPrice = timebasedCurrencyPriceObject.val();
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/editProductPrice/',
            {
                orderDetailId: $('#dialogOrderDetailProductPriceOrderDetailId').val(),
                productPrice: productPrice,
                editPriceReason: ckeditorData
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

        $(modalSelector).modal();
        foodcoopshop.Helper.initCkeditor('dialogEditPriceReason', true);

        var row = button.closest('tr');
        var orderDetailId = row.find('td:nth-child(2)').html();
        var price = foodcoopshop.Helper.getCurrencyAsFloat(row.find('td:nth-child(6) span.product-price-for-dialog').html()).toFixed(2);
        var productPriceField = $(modalSelector + ' #dialogOrderDetailProductPricePrice');

        $(modalSelector + ' #dialogOrderDetailProductPriceOrderDetailId').val(orderDetailId);
        $(modalSelector + ' label[for="dialogOrderDetailProductPricePrice"]').html('<b>' + row.find('td:nth-child(4) a.name-for-dialog').text() + '</b> <span style="font-weight:normal;">(' + foodcoopshop.LocalizedJs.admin.orderedBy + ' ' + row.find('td.customer-field span.customer-name-for-dialog').text() + ')');

        var productTimebasedCurrencyPriceField;

        $(modalSelector + ' .price-per-unit-info-text').remove();
        if (row.find('td.quantity-field').html() != '') {
            productTimebasedCurrencyPriceField = $(modalSelector + ' #dialogOrderDetailProductPricePrice').before('<b class="price-per-unit-info-text">' + foodcoopshop.LocalizedJs.admin.ExplainationTextApdaptPriceFormApaptWeight + '</b>');
        }

        $(modalSelector + ' span.timebased-currency-wrapper').remove();
        var timebasedCurrencyObject = $('#timebased-currency-object-' + orderDetailId);
        if (timebasedCurrencyObject.length > 0
           && $(modalSelector + ' #dialogOrderDetailProductPriceTimebasedCurrencyPrice').length == 0) {
            var timebasedCurrencyData = timebasedCurrencyObject.data('timebased-currency-object');
            var additionalDialogHtml = '<span class="timebased-currency-wrapper">';
            additionalDialogHtml += '<span class="small"> (' + foodcoopshop.LocalizedJs.admin.OriginalPriceWithoutReductionOfPriceInTime + ')</span>';
            additionalDialogHtml += '<label for="dialogOrderDetailProductPriceTimebasedCurrency"></label><br />';
            additionalDialogHtml += '<input type="number" step="0.01" min="0.01" name="dialogOrderDetailProductPriceTimebasedCurrencyPrice" id="dialogOrderDetailProductPriceTimebasedCurrencyPrice" value="" />';
            additionalDialogHtml += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b><span class="small"> (' + foodcoopshop.LocalizedJs.admin.FromWhichReallyPaidIn + ' ' + foodcoopshop.LocalizedJs.helper.CurrencyName + ')</span>';
            additionalDialogHtml += '</span>';
            $(modalSelector + ' .textarea-wrapper').before(additionalDialogHtml);
        }

        if (timebasedCurrencyObject.length > 0) {
            var newPrice = (price + Number(timebasedCurrencyData.money_incl)).toFixed(2);
            productPriceField.val(newPrice);
            productTimebasedCurrencyPriceField = $(modalSelector + ' #dialogOrderDetailProductPriceTimebasedCurrencyPrice');
            productTimebasedCurrencyPriceField.val(price);
            foodcoopshop.TimebasedCurrency.bindOrderDetailProductPriceField(productPriceField, timebasedCurrencyData, productTimebasedCurrencyPriceField);
            foodcoopshop.TimebasedCurrency.bindOrderDetailProductTimebasedCurrencyPriceField(productTimebasedCurrencyPriceField, timebasedCurrencyData, productPriceField);
        } else {
            productPriceField.val(price);
        }

        foodcoopshop.Helper.changeInputNumberToTextForEdge();
        $('#dialogOrderDetailProductQuantityPrice').focus();

    }

};
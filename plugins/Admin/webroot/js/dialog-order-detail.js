/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.DialogOrderDetail = {

    getHtmlForOrderDetailProductsPickupDayEdit : function(dialogId, title) {
        var dialogHtml = '<p></p>';
        dialogHtml += '<input type="hidden" id="customerId"></input>';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(title, dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForOrderDetailPickupDayCommentEdit : function(dialogId) {
        var dialogHtml = '<div class="textarea-wrapper">';
        dialogHtml += '<textarea class="ckeditor" name="dialogPickupDayComment" id="dialogPickupDayComment"></textarea>';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogCustomerId" id="dialogCustomerId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.pickupDay.ChangePickupDayComment, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderDetailProductAmountEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailProductAmount"></label><br />';
        dialogHtml += '<select name="dialogOrderDetailProductAmountAmount" id="dialogOrderDetailProductAmountAmount" /></select>';
        dialogHtml += '<div class="textarea-wrapper">';
        dialogHtml += '<label for="dialogEditAmountReason">' + foodcoopshop.LocalizedJs.admin.WhyIsAmountDecreased + '</label>';
        dialogHtml += '<textarea class="ckeditor" name="dialogEditAmountReason" id="dialogEditAmountReason"></textarea>';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductAmountOrderDetailId" id="dialogOrderDetailProductAmountOrderDetailId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.admin.DecreaseAmount, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderDetailProductPriceEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailProductPricePrice"></label><br />';
        dialogHtml += '<input type="number" step="0.01" min="0.01" name="dialogOrderDetailProductPricePrice" id="dialogOrderDetailProductPricePrice" value="" />';
        dialogHtml += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b>';
        dialogHtml += '<div class="textarea-wrapper" style="margin-top: 10px;">';
        dialogHtml += '<label for="dialogEditPriceReason">' + foodcoopshop.LocalizedJs.admin.WhyIsPriceAdapted + '</label>';
        dialogHtml += '<textarea class="ckeditor" name="dialogEditPriceReason" id="dialogEditPriceReason"></textarea>';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductPriceOrderDetailId" id="dialogOrderDetailProductPriceOrderDetailId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.admin.AdaptPrice, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderDetailCustomerEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailEditCustomerId" style="margin-bottom:10px;"></label><br />';
        dialogHtml += '<select id="dialogOrderDetailEditCustomerId">' + $('#customerid').html() + '</select>';
        dialogHtml += '<label for="dialogOrderDetailEditCustomerAmount" style="margin-top:10px;width:100%;">' + foodcoopshop.LocalizedJs.admin.AmountThatShouldBeChangedToMember + '</label><br />';
        dialogHtml += '<select style="width:200px;" id="dialogOrderDetailEditCustomerAmount"></select>';
        dialogHtml += '<div class="textarea-wrapper" style="margin-top:10px;">';
        dialogHtml += '<label for="dialogEditCustomerReason">' + foodcoopshop.LocalizedJs.admin.WhyIsMemberEdited + '</label>';
        dialogHtml += '<textarea class="ckeditor" name="dialogEditCustomerReason" id="dialogEditCustomerReason"></textarea>';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailEditCustomerOrderDetailId" id="dialogOrderDetailEditCustomerOrderDetailId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.admin.ChangeMember, dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForOrderDetailProductQuantityEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailProductQuantityQuantity"></label><br />';
        dialogHtml += '<br /><span class="quantity-string">' + foodcoopshop.LocalizedJs.admin.DeliveredWeight + '</span>: <input type="number" min="0.01" step="0.01" name="dialogOrderDetailProductQuantityQuantity" id="dialogOrderDetailProductQuantityQuantity" value="" />';
        dialogHtml += '<b></b>';
        dialogHtml += '<br />';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductQuantityOrderDetailId" id="dialogOrderDetailProductQuantityOrderDetailId" value="" />';
        dialogHtml += '<ul style="margin-top:5px;">';
        dialogHtml += '<li class="price-per-unit-base-info"></li>';
        dialogHtml += '<li>' + foodcoopshop.LocalizedJs.admin.PriceIsAutomaticallyAdaptedAfterSave + '</li>';
        dialogHtml += '<li>' + foodcoopshop.LocalizedJs.admin.FieldIsRedIfWeightNotYetAdapted + '</li>';
        dialogHtml += '</ul>';
        dialogHtml += '<label class="checkbox">';
        dialogHtml += '<input type="checkbox" name="dialogOrderDetailProductQuantityDoNotChangePrice" id="dialogOrderDetailProductQuantityDoNotChangePrice" value="" />';
        dialogHtml += '<span style="font-weight:normal;">' + foodcoopshop.LocalizedJs.admin.DoNotAutomaticallyAdaptPriceJustChangeWeight + '</span>';
        dialogHtml += '</label>';
        dialogHtml += '<br />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.admin.AdaptWeight, dialogId, dialogHtml);
        return dialogHtml;
    }

};

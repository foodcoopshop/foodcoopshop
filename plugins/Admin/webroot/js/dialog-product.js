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
foodcoopshop.DialogProduct = {

    getHtmlForProductNameEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogName">' + foodcoopshop.LocalizedJs.dialogProduct.Name + '</label><br />';
        dialogHtml += '<input type="text" name="dialogName" id="dialogName" value="" /><span class="overlay-info product-description-rename-info">' + foodcoopshop.LocalizedJs.dialogProduct.ProductRenameInfoText + '</span><br />';
        dialogHtml += '<label id="labelUnity" for="dialogUnity">' + foodcoopshop.LocalizedJs.dialogProduct.Unit + ' <span>' + foodcoopshop.LocalizedJs.dialogProduct.UnitDescriptionExample + '<br />' + foodcoopshop.LocalizedJs.admin.EnterApproximateWeightInPriceDialog + '</span></label><br />';
        dialogHtml += '<input type="text" name="dialogUnity" id="dialogUnity" value="" /><br />';
        dialogHtml += '<div class="textarea-wrapper">';
        dialogHtml += '<label for="dialogDescriptionShort" class="label-description-short">' + foodcoopshop.LocalizedJs.dialogProduct.DescriptionShort + '</label><br />';
        dialogHtml += '<textarea class="ckeditor" name="dialogDescriptionShort" id="dialogDescriptionShort"></textarea>';
        dialogHtml += '</div>';
        dialogHtml += '<div class="textarea-wrapper">';
        dialogHtml += '<label for="dialogDescription">' + foodcoopshop.LocalizedJs.dialogProduct.DescriptionLong + '</label><br />';
        dialogHtml += '<label class="is-declaration-ok"><input type="checkbox" name="dialogIsDeclarationOk" id="dialogIsDeclarationOk" />' + foodcoopshop.LocalizedJs.dialogProduct.ProductDeclarationOK + '</label><a href="' + foodcoopshop.LocalizedJs.dialogProduct.DocsUrlProductDeclaration + '" target="_blank"><i class="fas fa-arrow-circle-right"></i> ' + foodcoopshop.LocalizedJs.dialogProduct.Help + '</a><br />';
        dialogHtml += '<textarea class="ckeditor" name="dialogDescription" id="dialogDescription"></textarea>';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogProductId" id="dialogProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogProduct.ChangeNameAndDescription, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForProductPriceEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogPricePrice"></label><br />';
        dialogHtml += '<label class="radio">';
        dialogHtml += '<input type="radio" name="dialogPricePricePerUnitEnabled" value="price" checked="checked" class="price" />';
        dialogHtml += foodcoopshop.LocalizedJs.dialogProduct.PricePerUnit;
        dialogHtml += '</label>';
        dialogHtml += '<div class="price-wrapper">';
        dialogHtml += '<input type="number" step="0.01" name="dialogPricePrice" id="dialogPricePrice" value="" />';
        dialogHtml += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b> (' + foodcoopshop.LocalizedJs.dialogProduct.inclVAT + ')<br />';
        dialogHtml += '</div>';
        dialogHtml += '<label class="radio">';
        dialogHtml += '<input type="radio" name="dialogPricePricePerUnitEnabled" value="price-per-unit" class="price-per-unit"/>';
        dialogHtml += foodcoopshop.LocalizedJs.dialogProduct.PricePerWeightForAdaptionAfterDelivery;
        dialogHtml += '</label>';
        dialogHtml += '<div class="price-per-unit-wrapper deactivated">';
        dialogHtml += '<input type="number" step="0.01" name="dialogPricePriceInclPerUnit" id="dialogPricePriceInclPerUnit" value="" />';
        dialogHtml += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b> (' + foodcoopshop.LocalizedJs.dialogProduct.inclVAT + ') ' + foodcoopshop.LocalizedJs.dialogProduct.for;
        dialogHtml += '<select name="dialogPriceUnitAmount" id="dialogPriceUnitAmount">';
        dialogHtml += '<option value="1" selected>1</option>';
        dialogHtml += '<option value="10">10</option>';
        dialogHtml += '<option value="20">20</option>';
        dialogHtml += '<option value="50">50</option>';
        dialogHtml += '<option value="100">100</option>';
        dialogHtml += '<option value="200">200</option>';
        dialogHtml += '<option value="500">500</option>';
        dialogHtml += '<option value="1000">1.000</option>';
        dialogHtml += '</select> ';
        dialogHtml += '<select name="dialogPriceUnitName" id="dialogPriceUnitName">';
        dialogHtml += '<option value="kg" selected>kg</option>';
        dialogHtml += '<option value="g">g</option>';
        dialogHtml += '<option value="l">l</option>';
        dialogHtml += '</select><br />';
        dialogHtml += '<input type="number" name="dialogPriceQuantityInUnits" id="dialogPriceQuantityInUnits" value="" /> ' + foodcoopshop.LocalizedJs.dialogProduct.approximateDeliveryWeightIn0PerUnit.replaceI18n(0, '<span class="unit-name-placeholder">kg</span>');
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogPriceProductId" id="dialogPriceProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogProduct.ChangePrice, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForProductDepositEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogDepositDeposit"></label><br />';
        dialogHtml += '<input type="text" name="dialogDepositDeposit" id="dialogDepositDeposit" value="" />';
        dialogHtml += '<b>' + foodcoopshop.LocalizedJs.helper.CurrencySymbol + '</b> (' + foodcoopshop.LocalizedJs.dialogProduct.EnterZeroForDelete + ')<br />';
        dialogHtml += '<input type="hidden" name="dialogDepositProductId" id="dialogDepositProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogProduct.Deposit, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForProductIsStockProductEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogIsStockProductIsStockProduct"></label>';
        dialogHtml += '<label class="checkbox">';
        dialogHtml += '<input type="checkbox" name="dialogIsStockProductIsStockProduct" id="dialogIsStockProductIsStockProduct" />';
        dialogHtml += ' ' + foodcoopshop.LocalizedJs.dialogProduct.IsProductStockProduct;
        dialogHtml += '</label>';
        dialogHtml += '<p style="margin-top:10px;float:left;" class="small">' + foodcoopshop.LocalizedJs.dialogProduct.TheDeliveryRhythmOfStockProductsIsAlwaysWeekly + '</p>';
        dialogHtml += '<input type="hidden" name="dialogIsStockProductProductId" id="dialogIsStockProductProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogProduct.StockProduct, dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForProductQuantityEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogQuantityQuantity"></label>';
        dialogHtml += '<div class="field-wrapper">';
        dialogHtml += '<label class="checkbox">';
        dialogHtml += '<input type="checkbox" name="dialogQuantityAlwaysAvailable" id="dialogQuantityAlwaysAvailable" />';
        dialogHtml += ' ' + foodcoopshop.LocalizedJs.dialogProduct.IsTheProductAlwaysAvailable;
        dialogHtml += '</label>';
        dialogHtml += '<div class="quantity-wrapper">';
        dialogHtml += '<label>' + foodcoopshop.LocalizedJs.dialogProduct.AvailableAmount + '</label>';
        dialogHtml += '<input type="number" step="1" name="dialogQuantityQuantity" id="dialogQuantityQuantity" />';
        dialogHtml += '<label>' + foodcoopshop.LocalizedJs.dialogProduct.DefaultQuantityAfterSendingOrderLists + '</label>';
        dialogHtml += '<input type="number" step="1" name="dialogQuantityDefaultQuantityAfterSendingOrderLists" id="dialogQuantityDefaultQuantityAfterSendingOrderLists" />';
        dialogHtml += '<span style="float:left;" class="small">' + foodcoopshop.LocalizedJs.dialogProduct.DefaultQuantityAfterSendingOrderListsHelpText + '</span>'; 
        dialogHtml += '</div>';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogQuantityProductId" id="dialogQuantityProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogProduct.ChangeAmount, dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForProductQuantityIsStockProductEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogQuantityQuantity"></label>';
        dialogHtml += '<div class="field-wrapper">';
        dialogHtml += '<label>' + foodcoopshop.LocalizedJs.dialogProduct.CurrentStock + '</label>';
        dialogHtml += '<input type="number" step="1" name="dialogQuantityQuantity" id="dialogQuantityQuantity" /><br />';
        dialogHtml += '<label>' + foodcoopshop.LocalizedJs.dialogProduct.OrdersPossibleUntilAmountOf + '<br /><span class="small">' + foodcoopshop.LocalizedJs.dialogProduct.zeroOrSmallerZero + '.</span></label>';
        dialogHtml += '<input max="0" type="number" step="1" name="dialogQuantityQuantityLimit" id="dialogQuantityQuantityLimit" /><br />';
        dialogHtml += '<label>' + foodcoopshop.LocalizedJs.dialogProduct.NotificationIfAmountLowerThan + '<br /><span class="small">' + foodcoopshop.LocalizedJs.dialogProduct.ForManufacturersAndContactPersonsCanBeChangedInManufacturerSettings + '</span></label>';
        dialogHtml += '<input style="margin-top:25px;" type="number" step="1" name="dialogQuantitySoldOutLimit" id="dialogQuantitySoldOutLimit" /><br />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogQuantityProductId" id="dialogQuantityProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.admin.Stock, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForProductDeliveryRhythmEdit : function(dialogId, productIds) {
        var dialogHtml = '<label for="dialogDeliveryRhythm"></label>';
        dialogHtml += '<div class="field-wrapper">';
        dialogHtml += '<label>' + foodcoopshop.LocalizedJs.dialogProduct.DeliveryRhythm + '</label>';
            
        dialogHtml += '<select name="dialogDeliveryRhythmType" id="dialogDeliveryRhythmType" /></select>';
            
        dialogHtml += '<label style="margin-top:10px;" class="dynamic-element individual">' + foodcoopshop.LocalizedJs.dialogProduct.OrderPossibleUntil + '</label>';
        dialogHtml += '<input style="margin-top:10px;" autocomplete="off" class="dynamic-element individual datepicker" type="text" name="dialogDeliveryRhythmOrderPossibleUntil" id="dialogDeliveryRhythmOrderPossibleUntil" /><br />';

        dialogHtml += '<label class="dynamic-element default">' + foodcoopshop.LocalizedJs.dialogProduct.LastOrderWeekday + '</label>';
        dialogHtml += '<select class="dynamic-element default" name="dialogDeliveryRhythmSendOrderListWeekday" id="dialogDeliveryRhythmSendOrderListWeekday" /></select><br />';
        dialogHtml += '<label class="dynamic-element individual">' + foodcoopshop.LocalizedJs.dialogProduct.SendOrderListsDay + '</label>';
        dialogHtml += '<input autocomplete="off" class="datepicker dynamic-element individual" type="text" name="dialogDeliveryRhythmSendOrderListDay" id="dialogDeliveryRhythmSendOrderListDay" /><br />';
        dialogHtml += '<div style="float:left;margin-bottom:15px;line-height:14px;">';
        dialogHtml += '<span class="small dynamic-element default">';
        dialogHtml += foodcoopshop.LocalizedJs.dialogProduct.OrderListsAreSentAutomaticallyNextDayInTheMorning;
        dialogHtml += '</span>';
        dialogHtml += '<span class="small dynamic-element individual">';
        dialogHtml += foodcoopshop.LocalizedJs.dialogProduct.OrderListsAreSentAutomaticallyOnThisDay;
        dialogHtml += '</span>';
        dialogHtml += '<br /></div>';

        dialogHtml += '<label class="dynamic-element default">' + foodcoopshop.LocalizedJs.dialogProduct.FirstDeliveryDay + '</label>';
        dialogHtml += '<label class="dynamic-element individual">' + foodcoopshop.LocalizedJs.dialogProduct.DeliveryDay + '</label>';
            
        dialogHtml += '<input autocomplete="off" class="datepicker" type="text" name="dialogDeliveryRhythmFirstDeliveryDay" id="dialogDeliveryRhythmFirstDeliveryDay" /><br />';
        dialogHtml += '<div style="float:right;line-height:14px;"><span class="small">';
        if (productIds.length == 1) {
            dialogHtml += foodcoopshop.LocalizedJs.dialogProduct.FirstDeliveryDayInfoOneProduct;
        } else {
            dialogHtml += foodcoopshop.LocalizedJs.dialogProduct.FirstDeliveryDayInfoMultipleProducts;
        }
        dialogHtml += '</span><br /></div>';
            
        dialogHtml += '</div>';
        dialogHtml += '<p style="margin-top:10px;float:right;"><a target="_blank" href="' + foodcoopshop.LocalizedJs.dialogProduct.DocsUrlOrderHandling + '">' + foodcoopshop.LocalizedJs.dialogProduct.InfoPageForDeliveryRhythm + '</a></p>';
        dialogHtml += '<input type="hidden" name="dialogDeliveryRhythmProductId" id="dialogDeliveryRhythmProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogProduct.DeliveryRhythm, dialogId, dialogHtml);
        return dialogHtml;
    }
    
};
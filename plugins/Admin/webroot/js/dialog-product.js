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
    }

};
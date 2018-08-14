/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
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
        dialogHtml += '<textarea class="ckeditor" name="dialogDescriptionShort" id="dialogDescriptionShort" />';
        dialogHtml += '</div>';
        dialogHtml += '<div class="textarea-wrapper">';
        dialogHtml += '<label for="dialogDescription">' + foodcoopshop.LocalizedJs.dialogProduct.DescriptionLong + '</label><br />';
        dialogHtml += '<label class="is-declaration-ok"><input type="checkbox" name="dialogIsDeclarationOk" id="dialogIsDeclarationOk" />' + foodcoopshop.LocalizedJs.dialogProduct.ProductDeclarationOK + '</label><a href="' + foodcoopshop.LocalizedJs.dialogProduct.DocsUrlProductDeclaration + '" target="_blank"><i class="fa fa-arrow-circle-right"></i> ' + foodcoopshop.LocalizedJs.dialogProduct.Help + '</a><br />';
        dialogHtml += '<textarea class="ckeditor" name="dialogDescription" id="dialogDescription" />';
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

    getHtmlForProductQuantityEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogQuantityQuantity"></label>';
        dialogHtml += '<div class="quantity-wrapper">';
            dialogHtml += '<label>Verfügbare Anzahl<br /><span class="small">Aktueller Lagerstand</span></label>';
            dialogHtml += '<input type="number" step="1" name="dialogQuantityQuantity" id="dialogQuantityQuantity" /><br />';
            dialogHtml += '<label class="checkbox">';
                dialogHtml += '<input type="checkbox" name="dialogQuantityIsNegativeQuantityAllowed" id="dialogQuantityIsNegativeQuantityAllowed" />';
                dialogHtml += ' Negative Anzahl erlauben?<br /><span class="small">Falls das Produkt nachgeliefert werden kann.</span>';
            dialogHtml += '</label>';
            dialogHtml += '<label>E-Mail-Benachrichtigung ab Anzahl<br /><span class="small">leer: keine Benachrichtigung</span></label>';
            dialogHtml += '<input type="number" step="1" name="dialogQuantitySoldOutLimit" id="dialogQuantitySoldOutLimit" /><br />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogQuantityProductId" id="dialogQuantityProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogProduct.ChangeAmount, dialogId, dialogHtml);
        return dialogHtml;
    }

};
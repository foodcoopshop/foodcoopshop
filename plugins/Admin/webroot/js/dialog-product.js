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
        var dialogHtml = '<label for="dialogName">Name</label><br />';
        dialogHtml += '<input type="text" name="dialogName" id="dialogName" value="" /><span class="overlay-info product-description-rename-info">Wichtig: Bitte keine Produkte in andere Produkte umbenennen, sondern dafür ein neues Produkt erstellen!</span><br />';
        dialogHtml += '<label id="labelUnity" for="dialogUnity">Einheit <span>(z.B. Stück, 2 Paar, 6er-Pack) <br />Ungefähres Gewicht bitte beim <b>Preis</b> eintragen.</span></label><br />';
        dialogHtml += '<input type="text" name="dialogUnity" id="dialogUnity" value="" /><br />';
        dialogHtml += '<div class="textarea-wrapper">';
        dialogHtml += '<label for="dialogDescriptionShort" class="label-description-short">Kurze Beschreibung</label><br />';
        dialogHtml += '<textarea class="ckeditor" name="dialogDescriptionShort" id="dialogDescriptionShort" />';
        dialogHtml += '</div>';
        dialogHtml += '<div class="textarea-wrapper">';
        dialogHtml += '<label for="dialogDescription">Lange Beschreibung</label><br />';
        dialogHtml += '<label class="is-declaration-ok"><input type="checkbox" name="dialogIsDeclarationOk" id="dialogIsDeclarationOk" />Produktdeklaration OK?</label><a href="https://foodcoopshop.github.io/de/lebensmittelkennzeichnung" target="_blank"><i class="fa fa-arrow-circle-right"></i> Hilfe</a><br />';
        dialogHtml += '<textarea class="ckeditor" name="dialogDescription" id="dialogDescription" />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogProductId" id="dialogProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Name und Beschreibung ändern', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForProductPriceEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogPricePrice"></label><br />';
        dialogHtml += '<label class="radio">';
        dialogHtml += '<input type="radio" name="dialogPricePricePerUnitEnabled" value="price" checked="checked" class="price" />';
        dialogHtml += 'Preis pro Bestelleinheit';
        dialogHtml += '</label>';
        dialogHtml += '<div class="price-wrapper">';
        dialogHtml += '<input type="text" name="dialogPricePrice" id="dialogPricePrice" value="" />';
        dialogHtml += '* <b>€</b> (inkl. USt.)<br />';
        dialogHtml += '</div>';
        dialogHtml += '<label class="radio">';
        dialogHtml += '<input type="radio" name="dialogPricePricePerUnitEnabled" value="price-per-unit" class="price-per-unit"/>';
        dialogHtml += 'Preis nach Gewicht (für Gewichtsanpassung nach Lieferung)';
        dialogHtml += '</label>';
        dialogHtml += '<div class="price-per-unit-wrapper deactivated">';
        dialogHtml += '<input type="text" name="dialogPricePriceInclPerUnit" id="dialogPricePriceInclPerUnit" value="" />';
        dialogHtml += '* <b>€</b> (inkl. USt.) für 1 ';
        dialogHtml += '<select name="dialogPriceUnitName" id="dialogPriceUnitName">';
        dialogHtml += '<option value="kg" selected>kg (Kilo)</option>';
        dialogHtml += '<option value="g">g (Gramm)</option>';
        dialogHtml += '</select><br />';
        dialogHtml += '<input type="text" name="dialogPriceQuantityInUnits" id="dialogPriceQuantityInUnits" value="" />* ungefähres Liefergewicht in <span class="unit-name-placeholder">kg</span> pro Bestelleinheit';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogPriceProductId" id="dialogPriceProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Preis ändern', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForProductDepositEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogDepositDeposit">Eingabe in €</label><br />';
        dialogHtml += '<input type="text" name="dialogDepositDeposit" id="dialogDepositDeposit" value="" />';
        dialogHtml += '<b>€</b> (zum Löschen <b>0</b> eintragen)<br />';
        dialogHtml += '<input type="hidden" name="dialogDepositProductId" id="dialogDepositProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Pfand', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForProductQuantityEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogQuantityQuantity"></label>';
        dialogHtml += '<input type="text" name="dialogQuantityQuantity" id="dialogQuantityQuantity" value="" />';
        dialogHtml += '<input type="hidden" name="dialogQuantityProductId" id="dialogQuantityProductId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Anzahl ändern', dialogId, dialogHtml);
        return dialogHtml;
    }

};
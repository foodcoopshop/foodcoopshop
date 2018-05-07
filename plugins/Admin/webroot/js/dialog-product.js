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
        dialogHtml += '<label id="labelUnity" for="dialogUnity">Einheit <span style="font-weight:normal">(z.B. 1 kg, 0,5 l)</span></label><br />';
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
        dialogHtml += '<div class="price-wrapper">';
        dialogHtml += '<input type="text" name="dialogPricePrice" id="dialogPricePrice" value="" />';
        dialogHtml += '* <b>€</b>, inkl. USt., pro Bestelleinheit<br />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="checkbox" name="dialogPricePricePerUnitEnabled" id="dialogPricePricePerUnitEnabled" value="" />';
        dialogHtml += '<label class="checkbox-label" for="dialogPricePricePerUnitEnabled">Preis pro Einheit (kg / l) verwenden? <br /><span style="font-weight:normal">(bei Gewichtsänderung nach Lieferung)</span></label><br />';
        dialogHtml += '<div class="price-per-unit-wrapper deactivated">';
        dialogHtml += '<input type="text" name="dialogPricePriceInclPerUnit" id="dialogPricePriceInclPerUnit" value="" />';
        dialogHtml += '* <b>€</b>, inkl. USt., pro </b> ';
        dialogHtml += '<input type="text" name="dialogPriceUnitName" id="dialogPriceUnitName" value="" />';
        dialogHtml += 'z.B. kg / l<br />';
        dialogHtml += '<input type="text" name="dialogPriceQuantityInUnits" id="dialogPriceQuantityInUnits" value="" />* ungefähre Menge in kg / l pro Bestelleinheit (z.B. 0,25)';
        dialogHtml += '</div>';
        dialogHtml += '<br /><br />* auf max. 2 Kommastellen genau';
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
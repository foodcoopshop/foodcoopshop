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
foodcoopshop.Dialog = {
    
    addWrappersAndLoaderToDialogHtml : function(title, dialogId, dialogHtml) {
        var html = '<div id="' + dialogId + '" class="dialog" title="' + title + '">';
        html += '<form onkeypress="return event.keyCode != 13;">';
        html += dialogHtml;
        html += '<img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';
        html += '</form>';
        html += '</div>';
        return html;
    },
        
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
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Name und Beschreibung ändern', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForProductPriceEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogPricePrice"></label><br />';
        dialogHtml += '<div class="price-wrapper">';
        dialogHtml += '<input type="text" name="dialogPricePrice" id="dialogPricePrice" value="" />';
        dialogHtml += '<b>€</b>, inkl. USt., pro Bestelleinheit<br />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="checkbox" name="dialogPricePricePerUnitEnabled" id="dialogPricePricePerUnitEnabled" value="" />';
        dialogHtml += '<label class="checkbox-label" for="dialogPricePricePerUnitEnabled">Preis pro Einheit (kg / l) verwenden? <br /><span style="font-weight:normal">(bei Gewichtsänderung nach Lieferung)</span></label><br />';
        dialogHtml += '<div class="price-per-unit-wrapper deactivated">';
        dialogHtml += '<input type="text" name="dialogPricePriceInclPerUnit" id="dialogPricePriceInclPerUnit" value="" />';
        dialogHtml += '<b>€</b>, inkl. USt., pro </b> ';
        dialogHtml += '<input type="text" name="dialogPriceUnitName" id="dialogPriceUnitName" value="" />';
        dialogHtml += 'z.B. kg / l<br />';
        dialogHtml += '<input type="text" name="dialogPriceQuantityInUnits" id="dialogPriceQuantityInUnits" value="" /> ungefähre Menge in kg / l pro Bestelleinheit (z.B. 0,25)';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogPriceProductId" id="dialogPriceProductId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Preis ändern', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForCustomerCommentEdit : function(dialogId) {
        var dialogHtml = '<div class="textarea-wrapper">';
        dialogHtml += '<textarea class="ckeditor" name="dialogCustomerComment" id="dialogCustomerComment" />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogCustomerId" id="dialogCustomerId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Mitglieder-Kommentar ändern', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForOrderCommentEdit : function(dialogId) {
        var dialogHtml = '<div class="textarea-wrapper">';
        dialogHtml += '<textarea class="ckeditor" name="dialogOrderComment" id="dialogOrderComment" />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderId" id="dialogOrderId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Kommentar zu Bestellung ändern', dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderEdit : function(dialogId) {
        var dialogHtml = '<p style="margin-top: 10px;">Bestellung rückdatieren auf</p>';
        dialogHtml += '<div class="date-dropdown-placeholder"></div>';
        dialogHtml += '<input type="hidden" name="orderId" id="orderId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Bestellung rückdatieren', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForProductDepositEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogDepositDeposit">Eingabe in €</label><br />';
        dialogHtml += '<input type="text" name="dialogDepositDeposit" id="dialogDepositDeposit" value="" />';
        dialogHtml += '<b>€</b> (zum Löschen <b>0</b> eintragen)<br />';
        dialogHtml += '<input type="hidden" name="dialogDepositProductId" id="dialogDepositProductId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Pfand', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForProductQuantityEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogQuantityQuantity"></label>';
        dialogHtml += '<input type="text" name="dialogQuantityQuantity" id="dialogQuantityQuantity" value="" />';
        dialogHtml += '<input type="hidden" name="dialogQuantityProductId" id="dialogQuantityProductId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Anzahl ändern', dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderDetailProductPriceEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailProductPricePrice"></label><br />';
        dialogHtml += '<input type="text" name="dialogOrderDetailProductPricePrice" id="dialogOrderDetailProductPricePrice" value="" />';
        dialogHtml += '<b>€</b>';
        dialogHtml += '<div class="textarea-wrapper" style="margin-top: 10px;">';
        dialogHtml += '<label for="dialogEditPriceReason">Warum wird der Preis korrigiert (Pflichtfeld)?</label>';
        dialogHtml += '<textarea class="ckeditor" name="dialogEditPriceReason" id="dialogEditPriceReason" />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductPriceOrderDetailId" id="dialogOrderDetailProductPriceOrderDetailId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Preis korrigieren', dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderDetailProductAmountEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailProductAmount"></label><br />';
        dialogHtml += '<select name="dialogOrderDetailProductAmountAmount" id="dialogOrderDetailProductAmountAmount" /></select>';
        dialogHtml += '<div class="textarea-wrapper">';
        dialogHtml += '<label for="dialogEditAmountReason">Warum wird Anzahl korrigiert (Pflichtfeld)?</label>';
        dialogHtml += '<textarea class="ckeditor" name="dialogEditAmountReason" id="dialogEditAmountReason" />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductAmountOrderDetailId" id="dialogOrderDetailProductAmountOrderDetailId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Anzahl vermindern', dialogId, dialogHtml);
        return dialogHtml;
    },
    
    getHtmlForCustomerGroupEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogCustomerGroupEditText" id="dialogCustomerGroupEditText"></label><br />';
        dialogHtml += '<select name="dialogCustomerGroupEditGroup" id="dialogCustomerGroupEditGroup" /></select>';
        dialogHtml += '<input type="hidden" name="dialogCustomerGroupEditCustomerId" id="dialogCustomerGroupEditCustomerId" value="" />';
        dialogHtml = this.addWrappersAndLoaderToDialogHtml('Gruppe ändern', dialogId, dialogHtml);
        return dialogHtml;
    }

};
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
foodcoopshop.DialogOrderDetail = {

    getHtmlForOrderDetailProductAmountEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailProductAmount"></label><br />';
        dialogHtml += '<select name="dialogOrderDetailProductAmountAmount" id="dialogOrderDetailProductAmountAmount" /></select>';
        dialogHtml += '<div class="textarea-wrapper">';
        dialogHtml += '<label for="dialogEditAmountReason">Warum wird Anzahl korrigiert (Pflichtfeld)?</label>';
        dialogHtml += '<textarea class="ckeditor" name="dialogEditAmountReason" id="dialogEditAmountReason" />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductAmountOrderDetailId" id="dialogOrderDetailProductAmountOrderDetailId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Anzahl vermindern', dialogId, dialogHtml);
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
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Preis korrigieren', dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderDetailProductQuantityEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailProductQuantityQuantity"></label><br />';
        dialogHtml += '<input type="text" name="dialogOrderDetailProductQuantityQuantity" id="dialogOrderDetailProductQuantityQuantity" value="" />';
        dialogHtml += '<b></b> * - Der Preis wird automatisch angepasst.';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductQuantityOrderDetailId" id="dialogOrderDetailProductQuantityOrderDetailId" value="" />';
        dialogHtml += '<br /><br />* auf max. 2 Kommastellen genau';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Gewicht korrigieren', dialogId, dialogHtml);
        return dialogHtml;
    }
    
};

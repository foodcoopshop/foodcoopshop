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
        dialogHtml += '<label for="dialogEditAmountReason">Warum wird Anzahl angepasst (Pflichtfeld)?</label>';
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
        dialogHtml += '<label for="dialogEditPriceReason">Warum wird der Preis angepasst (Pflichtfeld)?</label>';
        dialogHtml += '<textarea class="ckeditor" name="dialogEditPriceReason" id="dialogEditPriceReason" />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductPriceOrderDetailId" id="dialogOrderDetailProductPriceOrderDetailId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Preis anpassen', dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderDetailProductQuantityEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogOrderDetailProductQuantityQuantity"></label><br />';
        dialogHtml += '<br /><span class="quantity-string">Geliefertes Gewicht</span>: <input type="text" name="dialogOrderDetailProductQuantityQuantity" id="dialogOrderDetailProductQuantityQuantity" value="" />';
        dialogHtml += '<b></b> *';
        dialogHtml += '<br />';
        dialogHtml += '<input type="hidden" name="dialogOrderDetailProductQuantityOrderDetailId" id="dialogOrderDetailProductQuantityOrderDetailId" value="" />';
        dialogHtml += '<ul style="margin-top:5px;">';
        dialogHtml += '<li class="price-per-unit-base-info"></li>';
        dialogHtml += '<li>Der Preis wird nach dem Speichern automatisch angepasst.</li>';
        dialogHtml += '<li>Das Feld ist rot, wenn das Gewicht noch nicht angepasst wurde.</li>';
        dialogHtml += '</ul>';
        dialogHtml += '<label class="checkbox">';
        dialogHtml += '<input type="checkbox" name="dialogOrderDetailProductQuantityDoNotChangePrice" id="dialogOrderDetailProductQuantityDoNotChangePrice" value="" />';
        dialogHtml += '<span style="font-weight:normal;">Den Preis nicht automatisch anpassen, nur das Gewicht ändern.</span>';
        dialogHtml += '</label>';
        dialogHtml += '<br />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml('Gewicht anpassen', dialogId, dialogHtml);
        return dialogHtml;
    }
    
};

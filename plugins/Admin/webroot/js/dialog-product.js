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
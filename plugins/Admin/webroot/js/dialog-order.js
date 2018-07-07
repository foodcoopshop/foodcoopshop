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
foodcoopshop.DialogOrder = {

    getHtmlForOrderCommentEdit : function(dialogId) {
        var dialogHtml = '<div class="textarea-wrapper">';
        dialogHtml += '<textarea class="ckeditor" name="dialogOrderComment" id="dialogOrderComment" />';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogOrderId" id="dialogOrderId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogOrder.ChangeCommentOfOrder, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForOrderEdit : function(dialogId) {
        var dialogHtml = '<p style="margin-top: 10px;">' + foodcoopshop.LocalizedJs.dialogOrder.SetDateOfOrderBackTo + '</p>';
        dialogHtml += '<div class="date-dropdown-placeholder"></div>';
        dialogHtml += '<input type="hidden" name="orderId" id="orderId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogOrder.SetDateOfOrderBack, dialogId, dialogHtml);
        return dialogHtml;
    }

};
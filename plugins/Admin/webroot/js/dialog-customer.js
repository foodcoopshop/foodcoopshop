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
foodcoopshop.DialogCustomer = {

    getHtmlForCustomerGroupEdit : function(dialogId) {
        var dialogHtml = '<label for="dialogCustomerGroupEditText" id="dialogCustomerGroupEditText"></label><br />';
        dialogHtml += '<select name="dialogCustomerGroupEditGroup" id="dialogCustomerGroupEditGroup" /></select>';
        dialogHtml += '<input type="hidden" name="dialogCustomerGroupEditCustomerId" id="dialogCustomerGroupEditCustomerId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogCustomer.ChangeGroup, dialogId, dialogHtml);
        return dialogHtml;
    },

    getHtmlForCustomerCommentEdit : function(dialogId) {
        var dialogHtml = '<div class="textarea-wrapper">';
        dialogHtml += '<textarea class="ckeditor" name="dialogCustomerComment" id="dialogCustomerComment"></textarea>';
        dialogHtml += '</div>';
        dialogHtml += '<input type="hidden" name="dialogCustomerId" id="dialogCustomerId" value="" />';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(foodcoopshop.LocalizedJs.dialogCustomer.ChangeMemberComment, dialogId, dialogHtml);
        return dialogHtml;
    }

};
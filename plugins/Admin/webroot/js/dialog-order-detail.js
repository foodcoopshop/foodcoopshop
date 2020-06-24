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
foodcoopshop.DialogOrderDetail = {

    getHtmlForOrderDetailProductsPickupDayEdit : function(dialogId, title) {
        var dialogHtml = '<p></p>';
        dialogHtml += '<input type="hidden" id="customerId"></input>';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(title, dialogId, dialogHtml);
        return dialogHtml;
    }

};

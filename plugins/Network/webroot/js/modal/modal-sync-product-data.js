/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalSyncProductData = {

    init : function(postData, checkedAttributeLabels, checkedProductsCount, checkedAttributesCount, domains2sync) {

        var modalSelector = '#modal-sync-product-data';

        var preparedString = foodcoopshop.LocalizedJs.syncProductData.SynchronizeDialogInfoText;
        preparedString = preparedString.replace(/\{0\}/, '<b>' + checkedAttributeLabels.join(', ') + '</b>');
        preparedString = preparedString.replace(/\{1\}/, checkedProductsCount + ' ' + (checkedProductsCount == 1 ? foodcoopshop.LocalizedJs.syncProductData.product : foodcoopshop.LocalizedJs.syncProductData.products));
        preparedString = preparedString.replace(/\{2\}/, checkedAttributesCount + ' ' + (checkedAttributesCount == 1 ? foodcoopshop.LocalizedJs.syncProductData.attribute : foodcoopshop.LocalizedJs.syncProductData.attributes));
        preparedString = preparedString.replace(/\{3\}/, '<p>' + domains2sync.join('<br />') + '</p>');

        var html = '<p>' + preparedString + '</p>';
        html += '<b class="negative">' + foodcoopshop.LocalizedJs.syncProductData.ThisActionCannotBeUndone + '</b></p>';

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.helper.yes, 'fas fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.syncProductData.ReallySynchronize,
            html,
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalSyncProductData.getSuccessHandler(postData);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalSyncProductData.getCloseHandler(modalSelector);
        });

        foodcoopshop.ModalSyncProductData.getOpenHandler(modalSelector);

    },

    getCloseHandler : function(modalSelector) {
        foodcoopshop.Modal.destroy(modalSelector);
    },

    getSuccessHandler : function(postData) {
        foodcoopshop.SyncBase.doApiCall(
            '/api/updateProducts.json',
            'POST',
            postData,
            foodcoopshop.SyncProductData.onProductDataUpdated
        );
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
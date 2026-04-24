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

        var preparedString = __(
            'Really_synchronize_data_{0}_from_{1}_and_{2}_to_the_following_foodcoops?_{3}',
            '<b>' + checkedAttributeLabels.join(', ') + '</b>',
            checkedProductsCount + ' ' + (checkedProductsCount == 1 ? __('product') : __('products')),
            checkedAttributesCount + ' ' + (checkedAttributesCount == 1 ? __('attribute') : __('attributes')),
            '<p>' + domains2sync.join('<br />') + '</p>',
        );

        var html = '<p>' + preparedString + '</p>';
        html += '<b class="negative">' + __('This_action_cannot_be_undone.') + '</b></p>';

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], __('Yes'), 'fa-fw fas fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], __('Cancel'), null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            __('Really_synchronize?'),
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
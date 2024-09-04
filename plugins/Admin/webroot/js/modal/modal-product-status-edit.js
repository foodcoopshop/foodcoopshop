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
foodcoopshop.ModalProductStatusEdit = {

    init : function() {

        var modalSelector = '#modal-product-status-edit';

        $('.product-status-edit').on('click', function () {

            var productId = $(this).attr('id').split('-');
            productId = productId[productId.length - 1];

            var previousMainProductRow = $(this).closest('tr').nextAll('.main-product').first();
            var previousProductId = 0;
            if (previousMainProductRow.length > 0) {
                previousProductId = previousMainProductRow.attr('id').split('-');
                previousProductId = previousProductId[previousProductId.length - 1];
            }

            var newState = 1;
            var newStateText = foodcoopshop.LocalizedJs.admin.ActivateProduct;
            var reallyNewStateText = foodcoopshop.LocalizedJs.admin.ReallyActivateProduct0;
            if ($(this).hasClass('set-status-to-inactive')) {
                newState = 0;
                newStateText = foodcoopshop.LocalizedJs.admin.DeactivateProduct;
                reallyNewStateText = foodcoopshop.LocalizedJs.admin.ReallyDeactivateProduct0;
            }

            var dataRow = $('#product-status-edit-' + productId).closest('tr');

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                newStateText,
                '<p>' + reallyNewStateText.replaceI18n(0, '<b>' + dataRow.find('td.cell-name span.name-for-dialog').html() + '</b>')
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductStatusEdit.getSuccessHandler(productId, previousProductId, newState);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductStatusEdit.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductStatusEdit.getOpenHandler(modalSelector);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(productId, previousProductId, newState) {
        document.location.href = '/admin/products/editStatus/' + productId + '/' + previousProductId + '/' + newState;
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
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
foodcoopshop.ModalProductStatusNewEdit = {

    init : function() {

        var modalSelector = '#modal-product-status-new-edit';

        $('.product-new-status-edit').on('click', function () {

            var productId = $(this).attr('id').split('-');
            productId = productId[productId.length - 1];

            var newState = 1;
            var newStateText = foodcoopshop.LocalizedJs.admin.ShowProductAsNew;
            var reallyNewStateText = foodcoopshop.LocalizedJs.admin.ReallyShowProduct0AsNew;
            if ($(this).hasClass('product-new-status-edit-inactive')) {
                newState = 0;
                newStateText = foodcoopshop.LocalizedJs.admin.DoNotShowProductAsNew;
                reallyNewStateText = foodcoopshop.LocalizedJs.admin.ReallyDoNotShowProduct0AsNew;
            }

            var dataRow = $(this).closest('tr');

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                newStateText,
                '<p>' + reallyNewStateText.replaceI18n(0,  '<b>' + dataRow.find('td.cell-name span.name-for-dialog').html() + '</b>')
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductStatusNewEdit.getSuccessHandler(productId, newState);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductStatusNewEdit.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductStatusNewEdit.getOpenHandler(modalSelector);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(productId, newState) {
        document.location.href = '/admin/products/editNewStatus/' + productId + '/' + newState;
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
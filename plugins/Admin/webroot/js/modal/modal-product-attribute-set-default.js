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
foodcoopshop.ModalProductAttributeSetDefault = {

    init : function() {

        $('.set-default-attribute-button').on('click', function () {

            var modalSelector = '#modal-product-attribute-set-default';

            var dataRow = $(this).closest('tr');
            var splittedProductId = dataRow.attr('id').replace(/product-/, '').split('-');
            var productId = splittedProductId[0];
            var productAttributeId = splittedProductId[1];

            var label = foodcoopshop.Admin.getProductNameForDialog(dataRow);
            var html = foodcoopshop.LocalizedJs.admin.ChangingDefaultAttributeInfoText0Html.replaceI18n(0, '<b>' + label + '</b>');

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.ChangeDefaultAttribute,
                html
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductAttributeSetDefault.getSuccessHandler(productId, productAttributeId);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductAttributeSetDefault.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalProductAttributeSetDefault.getOpenHandler(modalSelector);

        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(productId, productAttributeId) {
        document.location.href = '/admin/products/setDefaultAttributeId/' + productId + '/' + productAttributeId;
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
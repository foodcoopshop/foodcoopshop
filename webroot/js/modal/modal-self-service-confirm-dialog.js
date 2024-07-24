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
foodcoopshop.ModalSelfServiceConfirmDialog = {

    init : function() {

        var modalSelector = '#self-service-confirm-dialog';

        $('.btn-order-self-service').on('click', function() {
            var title = '';
            var html='';
            var redirectUrl = '';
            title = foodcoopshop.LocalizedJs.cart.emptyCart + '?';
            html = '<p>' + foodcoopshop.LocalizedJs.cart.selfServiceConfirmPurchase + '</p>';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                title,
                html
            );

            foodcoopshop.ModalProductTaxEdit.getOpenHandler($(this), modalSelector);

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductTaxEdit.getSuccessHandler(modalSelector);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductTaxEdit.getCloseHandler(modalSelector);
            });

        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {
        foodcoopshop.Helper.disableButton($(this));
        foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
        $(this).closest('form').submit();
    }
};
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalProductStatusEditBulk = {

    init : function() {

        var modalSelector = '#modal-product-status-edit-bulk';

        var button = $('#editStatusForSelectedProducts');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {

            var productIds = foodcoopshop.Admin.getSelectedProductIds();

            var html = '';
            var infoText = '';

            if (productIds.length == 1) {
                infoText = foodcoopshop.LocalizedJs.admin.YouSelectedOneProduct;
            } else {
                infoText = foodcoopshop.LocalizedJs.admin.YouSelected0Products.replace(/\{0\}/, '<b>' + productIds.length + '</b>');
            }
            infoText += '<br />';

            var products = [];
            for (var i in productIds) {
                products.push($('tr#product-' + productIds[i] + ' span.product-name').html());
            }
            html += '<ul><li>' + products.join('</li><li>') + '</li></ul>';

            var buttons = [
                foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.admin.Activate, 'fa-fw fas fa-check'),
                foodcoopshop.Modal.createButton(['btn-danger'], foodcoopshop.LocalizedJs.admin.Deactivate, 'fa-fw fas fa-minus-circle'),
                foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
            ];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.ChangeStatus,
                html,
                buttons
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductStatusEditBulk.getSuccessHandler(modalSelector, 1);
            });

            $(modalSelector + ' .modal-footer .btn-danger').on('click', function() {
                foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-minus-circle');
                foodcoopshop.Helper.disableButton($(this));
                foodcoopshop.ModalProductStatusEditBulk.getSuccessHandler(modalSelector, 0);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductStatusEditBulk.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductStatusEditBulk.getOpenHandler(modalSelector);

        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, status) {

        var productIds = foodcoopshop.Admin.getSelectedProductIds();

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editStatusBulk/',
            {
                productIds: productIds,
                status: status,
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    var message = '<p>';
                    message += foodcoopshop.LocalizedJs.admin.ErrorsOccurredWhileProductStatusWasChanged;
                    message += ':</p>';
                    message = message + data.msg;
                    foodcoopshop.appendFlashMessageError(modalSelector, message);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
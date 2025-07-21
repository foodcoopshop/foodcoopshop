/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Martin Hatlauf <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalProductDuplicate = {

    init : function() {

        var modalSelector = '#modal-product-duplicate';

        var button = $('#duplicateSelectedProduct');
        var buttonWrapper = $('#duplicateSelectedProductWrapper');
        foodcoopshop.Helper.disableButton(button);

        this.updateTooltip(buttonWrapper);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Helper.disableButton(button);
            var selectedCount = $('table.list').find('input.row-marker[type="checkbox"]:checked').length;

            foodcoopshop.ModalProductDuplicate.updateTooltip(buttonWrapper);

            if (selectedCount === 1) {
                foodcoopshop.Helper.enableButton(button);
            }
        });

        button.on('click', function () {

            var productId = foodcoopshop.Admin.getSelectedProductIds().pop();
            var title = foodcoopshop.LocalizedJs.admin.CopyProduct;

            const productName = $('tr#product-' + productId + ' span.product-name').html();

            var html = '<p>';
            html += foodcoopshop.LocalizedJs.admin.ReallyCopyProduct0.replace(/\{0\}/, '<b>' + productName + '</b>');
            html += '</p>';

            html += '<p style="margin-bottom:0;">Folgende Daten werden kopiert:</p>';
            html += '<ul>';
                html += '<li>Kategorien, Beschreibungen, Menge, Preis, Steuersatz, Pfand, Lieferrhythmus, Lagerprodukt</li>';
            html += '</ul>';

            html += '<p style="margin-top:15px;margin-bottom:0;">Folgende Daten werden <b>nicht</b> kopiert:</p>';
            html += '<ul>';
                html += '<li>Varianten, Bild</li>';
            html += '</ul>';

            html += '<p style="margin-top:15px;">';
                html += 'Produktname der Kopie(n): <b>' + productName + ' - Kopie X</b><br />';
                html += 'Status der Kopie(n): <b>deaktiviert</b>';
            html += '</p>';

            html += '<div class="field-wrapper">';
            html += '<label class="dynamic-element default" style="width: 140px;" for="copy-amount">'+ foodcoopshop.LocalizedJs.admin.AmountOfCopies +'</label>';
            html += '<select id="copy-amount" name="copy-amount" style="margin-top: 5px;">';

            const maxAmount = 10;
            for (let i = 1; i <= maxAmount; i++) {
                html += '<option value= "' + i + '">' + i + '</option>';
            }
            html += '</select>';
            html += '</div>';

            var buttons = [
                foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.admin.Copy, 'fas fa-check'),
                foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
            ];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                title,
                html,
                buttons,
            );

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                var amountValue = parseInt($(modalSelector + ' #copy-amount').val());
                foodcoopshop.ModalProductDuplicate.getSuccessHandler(modalSelector, productId, amountValue);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductDuplicate.getCloseHandler(modalSelector);
            });
            foodcoopshop.ModalProductDuplicate.getOpenHandler(modalSelector);

        });

    },

    updateTooltip: function(wrapper) {
        var selectedCount = $('table.list').find('input.row-marker[type="checkbox"]:checked').length;
        var tooltipText = foodcoopshop.LocalizedJs.admin.XofXProductsSelected;
        tooltipText = tooltipText.replace(/\{0\}/, selectedCount);
        tooltipText = tooltipText.replace(/\{1\}/, 1);
        wrapper.attr('title', tooltipText);
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, productId, amount) {
        foodcoopshop.Helper.ajaxCall(
            '/admin/products/duplicate/',
            {
                productId: productId,
                copyAmount: amount,
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    var message = '<p>';

                    message += foodcoopshop.LocalizedJs.admin.ErrorsOccurredWhileProductWasCopied;

                    message += ':</p>';
                    message = message + data.msg;
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, message);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};

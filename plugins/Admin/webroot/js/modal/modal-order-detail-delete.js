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
foodcoopshop.ModalOrderDetailDelete = {

    initBulk : function() {

        var button = $('#deleteSelectedProductsButton');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {

            var orderDetailIds = foodcoopshop.Admin.getSelectedOrderDetailIds();

            var infoText = '<p>';
            var textareaLabel = '';
            if (orderDetailIds.length == 1) {
                infoText += foodcoopshop.LocalizedJs.admin.YouSelectedOneProduct;
                textareaLabel = foodcoopshop.LocalizedJs.admin.WhyIsProductCancelled;
            } else {
                infoText += foodcoopshop.LocalizedJs.admin.YouSelected0Products.replace(/\{0\}/, '<b>' + orderDetailIds.length + '</b>');
                textareaLabel = foodcoopshop.LocalizedJs.admin.WhyAreProductsCancelled;
            }

            infoText += ':</p>';
            infoText += '<ul>';
            for (var i in orderDetailIds) {
                var dataRow = $('#delete-order-detail-' + orderDetailIds[i]).closest('tr');
                infoText += '<li>' + dataRow.find('td:nth-child(4) a.name-for-dialog').text() + ' / ' + dataRow.find('td.customer-field span.customer-name-for-dialog').html() + '</li>';
            }
            infoText += '</ul>';

            var modalSelector = '#order-detail-delete';

            var buttons = [
                foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.admin.YesDoCancelButton, 'fas fa-check'),
                foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
            ];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.ProductCancellation,
                '',
                buttons
            );

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalOrderDetailDelete.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalOrderDetailDelete.getOpenHandler($(this), modalSelector, orderDetailIds, infoText, textareaLabel);

        });

    },

    init : function() {

        $('.delete-order-detail').on('click', function() {

            var orderDetailId = $(this).attr('id').split('-');
            orderDetailId = orderDetailId[orderDetailId.length - 1];

            var dataRow = $('#delete-order-detail-' + orderDetailId).closest('tr');
            var infoText = '';

            var productName = dataRow.find('td:nth-child(4) a.name-for-dialog').text();
            var manufacturerName = dataRow.find('td:nth-child(5) a').html();

            if (foodcoopshop.Helper.isManufacturer) {
                infoText = '<p>' + foodcoopshop.LocalizedJs.admin.DoYouReallyWantToCancelProduct0.replace(/\{0\}/, '<b>' + productName + '</b>') + '</p>';
            } else {
                infoText = '<p>' + foodcoopshop.LocalizedJs.admin.DoYouReallyWantToCancelProduct0From1.replace(/\{0\}/, '<b>' + productName + '</b>').replace(/\{1\}/, '<b>' + manufacturerName + '</b>') + '</p>';
            }

            var textareaLabel = foodcoopshop.LocalizedJs.admin.WhyIsProductCancelled;

            var modalSelector = '#order-detail-delete';

            var buttons = [
                foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.admin.YesDoCancelButton, 'fas fa-check'),
                foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
            ];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.ProductCancellation,
                '',
                buttons
            );

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalOrderDetailDelete.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalOrderDetailDelete.getOpenHandler($(this), modalSelector, [orderDetailId], infoText, textareaLabel);
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getOpenHandler : function(button, modalSelector, orderDetailIds, infoText, textareaLabel) {

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        var modalHtml = infoText;

        if (!foodcoopshop.Helper.isManufacturer) {
            modalHtml += '<p class="overlay-info">' + foodcoopshop.LocalizedJs.admin.PleaseOnlyCancelIfOkForManufacturer + '</p>';
        }

        modalHtml += '<div class="textarea-wrapper">';
        modalHtml += '<label for="dialogCancellationReason">' + textareaLabel +'</label>';
        modalHtml += '<textarea name="dialogCancellationReason" id="dialogCancellationReason"></textarea>';
        modalHtml += '</div>';

        $(modalSelector + ' .modal-body').html(modalHtml);

        foodcoopshop.Editor.initSmall('dialogCancellationReason', true);

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderDetailDelete.getSuccessHandler(modalSelector, orderDetailIds);
        });

    },

    getSuccessHandler : function(modalSelector, orderDetailIds) {

        var editorData = $('#dialogCancellationReason').val();
        if (editorData == '') {
            foodcoopshop.Modal.appendFlashMessageError(modalSelector, foodcoopshop.LocalizedJs.admin.CancellationReasonIsMandatory);
            foodcoopshop.Modal.resetButtons(modalSelector);
            return;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/delete',
            {
                orderDetailIds: orderDetailIds,
                cancellationReason: editorData,
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    document.location.reload();
                }
            }
        );

    }

};
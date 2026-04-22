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
                infoText += __('You_selected_1_product.');
                textareaLabel = __('Why_is_product_cancelled_(mandatory_field)?');
            } else {
                infoText += __('You_selected_{0}_products.', '<b>' + orderDetailIds.length + '</b>');
                textareaLabel = __('Why_are_products_cancelled_(mandatory_field)?');
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
                foodcoopshop.Modal.createButton(['btn-success'], __('Yes_do_cancel_button!'), 'fas fa-check'),
                foodcoopshop.Modal.createButton(['btn-outline-light'], __('Cancel'), null, true)
            ];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                __('Product_cancellation'),
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
                infoText = '<p>' + __('Do_you_really_want_to_cancel_product_{0}?', '<b>' + productName + '</b>') + '</p>';
            } else {
                infoText = '<p>' + __('Do_you_really_want_to_cancel_product_{0}_from_{1}?', '<b>' + productName + '</b>', '<b>' + manufacturerName + '</b>') + '</p>';
            }

            var textareaLabel = __('Why_is_product_cancelled_(mandatory_field)?');

            var modalSelector = '#order-detail-delete';

            var buttons = [
                foodcoopshop.Modal.createButton(['btn-success'], __('Yes_do_cancel_button!'), 'fas fa-check'),
                foodcoopshop.Modal.createButton(['btn-outline-light'], __('Cancel'), null, true)
            ];

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                __('Product_cancellation'),
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
            modalHtml += '<p class="overlay-info">' + __('Please_only_cancel_if_ok_for_manufacturer!') + '</p>';
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
            foodcoopshop.Modal.appendFlashMessageError(modalSelector, __('Cancellation_reason_is_mandatory.'));
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
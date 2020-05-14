/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalOrderDetailDelete = {

    initDeleteSelectionButton : function () {

        var button = $('#deleteSelectedProductsButton');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"]').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {
            var orderDetailIds = foodcoopshop.Admin.getSelectedOrderDetailIds();
            foodcoopshop.Admin.openBulkDeleteOrderDetailDialog(orderDetailIds);
        });

    },

    initBulk : function() {

        return;
        
        var infoText = '<p>';
        if (orderDetailIds.length == 1) {
            infoText = foodcoopshop.LocalizedJs.admin.YouSelectedOneProduct;
        } else {
            infoText = foodcoopshop.LocalizedJs.admin.YouSelected0Products.replace(/\{0\}/, '<b>' + orderDetailIds.length + '</b>');
        }

        infoText += ':</p>';
        infoText += '<ul>';
        for (var i in orderDetailIds) {
            var dataRow = $('#delete-order-detail-' + orderDetailIds[i]).closest('tr');
            infoText += '<li>' + dataRow.find('td:nth-child(4) a').html() + ' / ' + dataRow.find('td:nth-child(9) span.customer-name-for-dialog').html() + '</li>';
        }
        infoText += '</ul>';

        foodcoopshop.Admin.openDeleteOrderDetailDialog(orderDetailIds, infoText, textareaLabel, dialogTitle);
        
    },
        
    init : function() {
        
        var modalSelector = '#order-detail-delete';
        
        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.admin.YesDoCancelButton, 'fa fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.no, null, true)
        ];
        
        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.ReallyDeleteOrderedProduct,
            '',
            buttons
        );
        
        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalOrderDetailDelete.getCloseHandler();
        });

        $('.delete-order-detail').on('click', function() {
            foodcoopshop.ModalOrderDetailDelete.getOpenHandler($(this), modalSelector);
        });

    },
    
    getCloseHandler : function() {
        $('#cke_dialogCancellationReason').val('');
        $('#flashMessage').remove();
    },
    
    getSuccessHandler : function(modalSelector, orderDetailIds) {
        
        var ckeditorData = CKEDITOR.instances['dialogCancellationReason'].getData().trim();
        if (ckeditorData == '') {
            foodcoopshop.Modal.appendFlashMessage(modalSelector, foodcoopshop.LocalizedJs.admin.CancellationReasonIsMandatory);
            foodcoopshop.Modal.resetButtons(modalSelector);
            return;
        }

        foodcoopshop.Helper.ajaxCall(
            '/admin/order-details/delete',
            {
                orderDetailIds: orderDetailIds,
                cancellationReason: ckeditorData
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
        
    },
    
    getOpenHandler : function(button, modalSelector) {
        
        $(modalSelector).modal();

        var orderDetailId = button.attr('id').split('-');
        orderDetailId = orderDetailId[orderDetailId.length - 1];

        var dataRow = $('#delete-order-detail-' + orderDetailId).closest('tr');
        var modalHtml = '';

        var customerName = dataRow.find('td:nth-child(4) a').html();
        var manufacturerName = dataRow.find('td:nth-child(5) a').html();

        if (foodcoopshop.Helper.isManufacturer) {
            modalHtml += '<p>' + foodcoopshop.LocalizedJs.admin.DoYouReallyWantToCancelProduct0.replace(/\{0\}/, '<b>' + customerName + '</b>') + '</p>';
        } else {
            modalHtml += '<p>' + foodcoopshop.LocalizedJs.admin.DoYouReallyWantToCancelProduct0From1.replace(/\{0\}/, '<b>' + customerName + '</b>').replace(/\{1\}/, '<b>' + manufacturerName + '</b>') + '</p>';
        }
        
        if (!foodcoopshop.Helper.isManufacturer) {
            modalHtml += '<p class="overlay-info">' + foodcoopshop.LocalizedJs.admin.PleaseOnlyCancelIfOkForManufacturer + '</p>';
        }

        modalHtml += '<div class="textarea-wrapper">';
        modalHtml += '<label for="dialogCancellationReason">' + foodcoopshop.LocalizedJs.admin.WhyIsProductCancelled +'</label>';
        modalHtml += '<textarea class="ckeditor" name="dialogCancellationReason" id="dialogCancellationReason"></textarea>';
        modalHtml += '</div>';
        
        $(modalSelector + ' .modal-body').html(modalHtml);
        
        foodcoopshop.Helper.initCkeditor('dialogCancellationReason');
        
        
        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderDetailDelete.getSuccessHandler(modalSelector, [orderDetailId]);
        });
        

        
    }

};
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
foodcoopshop.ModalProductTaxEdit = {

    init : function() {

        var modalSelector = '#product-tax-edit-form';

        $('.product-tax-edit-button').on('click', function() {
            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                foodcoopshop.LocalizedJs.admin.ChangeTaxRate,
                ''
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

        var productId = $(modalSelector + ' .product-id').val();

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editTax/',
            {
                productId: productId,
                taxId: $(modalSelector + ' #taxes-id-tax-' + productId).val()
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessage(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(button, modalSelector) {

        $(modalSelector).modal();

        var productId = button.data('objectId');
        var formHtml = $('.tax-dropdown-wrapper').clone();

        $(modalSelector + ' .modal-body').append(formHtml);

        var productName = $('#product-' + productId + ' span.name-for-dialog').html();
        $(modalSelector + ' .modal-body').prepend(
            '<p><b>' + productName + '</b></p>'
        );

        // make id unique and preselect
        var select = $(modalSelector).find('select');
        var newId = select.attr('id') + '-' + productId;
        select.attr('id', newId);
        var preselectedTaxRate = $('#product-' + productId + ' input[name="Products[id_tax]"').val();
        select.val(preselectedTaxRate);

        $(modalSelector + ' .product-id').val(productId);

    }

};
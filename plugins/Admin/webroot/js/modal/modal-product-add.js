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
foodcoopshop.ModalProductAdd = {

    init : function() {

        var modalSelector = '#modal-product-add';

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.AddNewProduct,
            foodcoopshop.ModalProductAdd.getHtml()
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalProductAdd.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalProductAdd.getCloseHandler();
        });

        $('#add-product-button-wrapper a').on('click', function () {
            foodcoopshop.ModalProductAdd.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var label = foodcoopshop.LocalizedJs.dialogProduct.WhichProductDoYouWantToAdd;
        if (!foodcoopshop.Helper.isManufacturer) {
            var manufacturerName = $('#manufacturerid').find('option:selected').text();
            label = foodcoopshop.LocalizedJs.dialogProduct.WhichProductDoYouWantToAddFor0.replace(/\{0\}/, '<b>' + manufacturerName + '</b>');
        }
        var html = '<label for="dialogProductAdd">' + label + '</label>';
            html += '<br />' + foodcoopshop.LocalizedJs.dialogProduct.Name + '</span> <input type="text" id="dialogProductAddName" value="" />';
            html += '<br />';
        return html;
    },

    getCloseHandler : function() {
        $('#dialogProductAddName').val('');
    },

    getSuccessHandler : function(modalSelector) {

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/add/',
            {
                manufacturerId: $('#manufacturerid').val(),
                productName: $('#dialogProductAddName').val()
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
        var productName = button.closest('tr').find('span.product-name').val();
        $('#dialogProductAddName').val(productName);
        $(modalSelector).modal();
    }

};
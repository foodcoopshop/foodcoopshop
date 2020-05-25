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

        $('#add-product-button-wrapper a').on('click', function () {
            foodcoopshop.ModalProductAdd.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var infoText = '';
        if (!foodcoopshop.Helper.isManufacturer) {
            var manufacturerName = $('#manufacturerid').find('option:selected').text();
            infoText = foodcoopshop.LocalizedJs.dialogProduct.Manufacturer + ': <b>' + manufacturerName + '</b>';
        }
        var html = '<p>' + infoText + '</p>';
            html += '<label for="dialogProductAdd"><b>' + foodcoopshop.LocalizedJs.dialogProduct.Name + '</b></label><br />';
            html += '<input type="text" id="dialogProductAddName" value="" />';
            html += '<hr />';
            html += '<div class="textarea-wrapper">';
            html += '<label for="dialogProductAddDescriptionShort" class="label-description-short"><b>' + foodcoopshop.LocalizedJs.dialogProduct.DescriptionShort + '</b></label><br />';
            html += '<textarea class="ckeditor" name="dialogProductAddDescriptionShort" id="dialogProductAddDescriptionShort"></textarea>';
            html += '<hr />';
            html += '</div>';
            html += '<div class="textarea-wrapper">';
            html += '<label for="dialogProductAddDescription"><b>' + foodcoopshop.LocalizedJs.dialogProduct.DescriptionLong + '</b></label><br />';
            html += '<textarea class="ckeditor" name="dialogProductAddDescription" id="dialogProductAddDescription"></textarea>';
            html += '</div>';
            html += '<br />';
        return html;
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/add/',
            {
                manufacturerId: $('#manufacturerid').val(),
                productName: $('#dialogProductAddName').val(),
                descriptionShort: CKEDITOR.instances['dialogProductAddDescriptionShort'].getData().trim(),
                description: CKEDITOR.instances['dialogProductAddDescription'].getData().trim()
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

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.AddNewProduct,
            foodcoopshop.ModalProductAdd.getHtml()
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalProductAdd.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalProductAdd.getCloseHandler(modalSelector);
        });

        var productName = button.closest('tr').find('span.product-name').val();
        foodcoopshop.Helper.initCkeditor('dialogProductAddDescriptionShort');
        foodcoopshop.Helper.initCkeditor('dialogProductAddDescription');
        $(modalSelector).modal();
        $('#dialogProductAddName').focus();
    }

};
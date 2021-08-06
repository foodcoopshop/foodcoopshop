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
            foodcoopshop.ModalProductAdd.getOpenHandler(modalSelector);
        });

    },

    getHtml : function() {
        var infoText = '';
        if (!foodcoopshop.Helper.isManufacturer) {
            var manufacturerName = $('#manufacturerid').find('option:selected').text();
            infoText = foodcoopshop.LocalizedJs.dialogProduct.Manufacturer + ': <b>' + manufacturerName + '</b>';
        }
        var html = '<p>' + infoText + '</p>';
        html += '<label for="dialogName"><b>' + foodcoopshop.LocalizedJs.dialogProduct.Name + '</b></label><br />';
        html += '<input type="text" name="dialogName" id="dialogName" value="" /><span class="small" style="float:left;">' + foodcoopshop.LocalizedJs.dialogProduct.ProductRenameInfoText + '</span><br />';
        html += '<hr />';
        html += '<div class="dialog-unity-wrapper">';
        html += '<label id="dialogLabelUnity" for="dialogUnity"><b>' + foodcoopshop.LocalizedJs.dialogProduct.Unit + '</b> <span class="small">' + foodcoopshop.LocalizedJs.dialogProduct.UnitDescriptionExample + '</span></label><br />';
        html += '<input type="text" name="dialogUnity" id="dialogUnity" value="" /><br />';
        html += '<span class="small">' + foodcoopshop.LocalizedJs.admin.EnterApproximateWeightInPriceDialog + '</span>';
        html += '<hr />';
        html += '</div>';

        if ($('.storage-location-dropdown-wrapper').length > 0) {
            html += '<div class="field-wrapper storage-location-wrapper">';
                html += '<label for="dialogStorageLocation"><b>' + foodcoopshop.LocalizedJs.dialogProduct.StorageLocation + '</b></label>';
                html += '<select name="dialogStorageLoation" id="dialogStorageLocation"></select><br />';
                html += '<hr />';
            html += '</div>';
        }

        html += '<div class="textarea-wrapper">';
        html += '<label for="dialogDescriptionShort" class="label-description-short"><b>' + foodcoopshop.LocalizedJs.dialogProduct.DescriptionShort + '</b></label><br />';
        html += '<textarea class="ckeditor" name="dialogDescriptionShort" id="dialogDescriptionShort"></textarea>';
        html += '<hr />';
        html += '</div>';
        html += '<div class="textarea-wrapper">';
        html += '<label for="dialogDescription"><b>' + foodcoopshop.LocalizedJs.dialogProduct.DescriptionLong + '</b></label><br />';
        html += '<div class="declaration-wrapper">';
        html += '<label class="is-declaration-ok"><input type="checkbox" name="dialogIsDeclarationOk" id="dialogIsDeclarationOk" />' + foodcoopshop.LocalizedJs.dialogProduct.ProductDeclarationOK + '</label><a href="' + foodcoopshop.LocalizedJs.dialogProduct.DocsUrlProductDeclaration + '" target="_blank"><i class="fas fa-arrow-circle-right"></i> ' + foodcoopshop.LocalizedJs.dialogProduct.Help + '</a><br />';
        html += '<textarea class="ckeditor hide" name="dialogDescription" id="dialogDescription"></textarea>';
        html += '</div>';
        html += '</div>';
        html += '<input type="hidden" name="dialogProductId" id="dialogProductId" value="" />';
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
                name: $('#dialogName').val(),
                unity: $('#dialogUnity').val(),
                descriptionShort: CKEDITOR.instances['dialogDescriptionShort'].getData().trim(),
                description: CKEDITOR.instances['dialogDescription'].getData().trim(),
                isDeclarationOk: $('#dialogIsDeclarationOk:checked').length > 0 ? 1 : 0,
                idStorageLocation: $('#dialogStorageLocation').length > 0 ? $('#dialogStorageLocation').val() : 0
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

    getOpenHandler : function(modalSelector) {

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.AddNewProduct,
            foodcoopshop.ModalProductAdd.getHtml()
        );

        var storageLocationWrapper = $('.storage-location-dropdown-wrapper');
        if (storageLocationWrapper.length > 0) {
            $(modalSelector + ' #dialogStorageLocation').append(storageLocationWrapper.find('select').html());
        }

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalProductAdd.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalProductAdd.getCloseHandler(modalSelector);
        });

        foodcoopshop.Helper.initCkeditor('dialogDescriptionShort');
        foodcoopshop.Helper.ajaxCall(
            '/admin/manufacturers/setElFinderUploadPath/' + $('#manufacturerid').val(),
            {},
            {
                onOk: function (data) {
                    foodcoopshop.Helper.initCkeditorSmallWithUpload('dialogDescription');
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessage(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
        $('#dialogName').focus();
    }

};
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
            infoText = __('Manufacturer') + ': <b>' + manufacturerName + '</b>';
        }
        var html = '<p>' + infoText + '</p>';

        html = '<div class="block block-a">';

        html += '<label for="dialogName"><b>' + __('Name') + '</b></label><br />';
        html += '<input type="text" name="dialogName" id="dialogName" value="" /><span class="small" style="float:left;">' + __('Product_rename_info_text') + '</span><br />';
        html += '<hr />';
        html += '<div class="dialog-unity-wrapper">';
        html += '<label id="dialogLabelUnity" for="dialogUnity"><b>' + __('Unit') + '</b> <span class="small">' + __('Unit_description_example') + '</span></label><br />';
        html += '<input type="text" name="dialogUnity" id="dialogUnity" value="" /><br />';
        html += '<span class="small">' + __('Enter_approximate_weight_in_price_dialog.') + '</span>';
        html += '</div>';

        if ($('.storage-location-dropdown-wrapper').length > 0) {
            html += '<hr />';
            html += '<div class="field-wrapper storage-location-wrapper">';
            html += '<label for="dialogStorageLocation"><b>' + __('Storage_location') + '</b></label>';
            html += '<select name="dialogStorageLoation" id="dialogStorageLocation"></select><br />';
            html += '</div>';
        }

        if (foodcoopshop.Helper.isSelfServiceModeEnabled) {
            html += '<hr />';
            html += '<div class="dialog-barcode-wrapper">';
            html += '<label id="dialogLabelBarcode" for="dialogBarcode"><b>' + __('EAN_13_code') + '</b></label><br />';
            html += '<input type="text" name="dialogBarcode" id="dialogBarcode" value="" /><br />';
            html += '</div>';
        }

        html += '<hr />';

        html += '<div class="textarea-wrapper">';
        html += '<label for="dialogDescriptionShort" class="label-description-short"><b>' + __('Description_short') + '</b></label><br />';
        html += '<textarea name="dialogDescriptionShort" id="dialogDescriptionShort"></textarea>';
        html += '</div>';
        html += '<hr />';

        html += '</div>';

        html += '<div class="block block-b">';

        html += '<div class="textarea-wrapper">';
        html += '<label for="dialogDescription"><b>' + __('Description_long') + '</b></label><br />';
        html += '<div class="declaration-wrapper">';
        html += '<label class="is-declaration-ok"><input type="checkbox" name="dialogIsDeclarationOk" id="dialogIsDeclarationOk" />' + __('Product_declaration_ok?') + '</label><a href="' + foodcoopshop.config.DocsUrlProductDeclaration + '" target="_blank"><i class="fas fa-arrow-circle-right"></i> ' + __('Help') + '</a><br />';
        html += '<textarea hide" name="dialogDescription" id="dialogDescription"></textarea>';
        html += '</div>';
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
                descriptionShort: $('#dialogDescriptionShort').val(),
                description: $('#dialogDescription').val(),
                isDeclarationOk: $('#dialogIsDeclarationOk:checked').length > 0 ? 1 : 0,
                idStorageLocation: $('#dialogStorageLocation').length > 0 ? $('#dialogStorageLocation').val() : 0,
                barcode: $('#dialogBarcode').length > 0 ? $('#dialogBarcode').val() : '',
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
    },

    getOpenHandler : function(modalSelector) {

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            __('Add_new_product?'),
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

        foodcoopshop.Editor.initSmall('dialogDescriptionShort');
        foodcoopshop.Helper.ajaxCall(
            '/admin/manufacturers/setElFinderUploadPath/' + $('#manufacturerid').val(),
            {},
            {
                onOk: function (data) {
                    foodcoopshop.Editor.initSmallWithUpload('dialogDescription');
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
        $('#dialogName').focus();
    }

};
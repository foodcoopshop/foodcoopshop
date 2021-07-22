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
foodcoopshop.ModalProductNameEdit = {

    init : function() {

        var modalSelector = '#modal-product-name-edit';

        $('a.product-name-edit-button').on('click', function () {
            foodcoopshop.ModalProductNameEdit.getOpenHandler($(this), modalSelector);
        });

    },

    getHtml : function() {
        var html = '<label for="dialogName"><b>' + foodcoopshop.LocalizedJs.dialogProduct.Name + '</b></label><br />';
        html += '<input type="text" name="dialogName" id="dialogName" value="" /><span class="small" style="float:left;">' + foodcoopshop.LocalizedJs.dialogProduct.ProductRenameInfoText + '</span><br />';
        html += '<hr />';
        html += '<div class="dialog-unity-wrapper">';
        html += '<label id="dialogLabelUnity" for="dialogUnity"><b>' + foodcoopshop.LocalizedJs.dialogProduct.Unit + '</b> <span class="small">' + foodcoopshop.LocalizedJs.dialogProduct.UnitDescriptionExample + '</span></label><br />';
        html += '<input type="text" name="dialogUnity" id="dialogUnity" value="" /><br />';
        html += '<span class="small">' + foodcoopshop.LocalizedJs.admin.EnterApproximateWeightInPriceDialog + '</span>';
        html += '<hr />';
        html += '</div>';
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
            '/admin/products/editName/',
            {
                productId: $('#dialogProductId').val(),
                name: $('#dialogName').val(),
                unity: $('#dialogUnity').val(),
                descriptionShort: CKEDITOR.instances['dialogDescriptionShort'].getData().trim(),
                description: CKEDITOR.instances['dialogDescription'].getData().trim(),
                isDeclarationOk: $('#dialogIsDeclarationOk:checked').length > 0 ? 1 : 0
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
            foodcoopshop.LocalizedJs.dialogProduct.ChangeNameAndDescription,
            foodcoopshop.ModalProductNameEdit.getHtml()
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalProductNameEdit.getSuccessHandler(modalSelector);
        });

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalProductNameEdit.getCloseHandler(modalSelector);
        });

        foodcoopshop.Modal.removeTooltipster();

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        foodcoopshop.Helper.initCkeditor('dialogDescriptionShort');

        var row = button.closest('tr');
        var nameCell = row.find('td.cell-name');
        $(modalSelector + ' #dialogName').val(foodcoopshop.Admin.decodeEntities(nameCell.find('span.name-for-dialog .product-name').html()));
        $(modalSelector + ' #dialogIsDeclarationOk').prop('checked', row.find('span.is-declaration-ok-wrapper').data('is-declaration-ok'));
        var unityElement = nameCell.find('span.unity-for-dialog');
        var unity = '';
        if (unityElement.length > 0) {
            unity = foodcoopshop.Admin.decodeEntities(unityElement.html());
        }
        $(modalSelector + ' #dialogUnity').val(unity);
        CKEDITOR.instances['dialogDescriptionShort'].setData(nameCell.find('span.description-short-for-dialog').html());
        $(modalSelector + ' #dialogProductId').val(row.find('td.cell-id').html());

        var manufacturerId = row.data('manufacturerId');
        foodcoopshop.Helper.ajaxCall(
            '/admin/manufacturers/setElFinderUploadPath/' + manufacturerId,
            {},
            {
                onOk: function (data) {
                    foodcoopshop.Helper.initCkeditorSmallWithUpload('dialogDescription');
                    CKEDITOR.instances['dialogDescription'].setData(nameCell.find('span.description-for-dialog').html());
                },
                onError: function (data) {
                    foodcoopshop.Modal.appendFlashMessage(modalSelector, data.msg);
                    foodcoopshop.Modal.resetButtons(modalSelector);
                }
            }
        );

        // hide unity field if product has attributes
        if (button.closest('tr').next().hasClass('sub-row')) {
            $(modalSelector + ' .dialog-unity-wrapper').hide();
            $(modalSelector + ' #dialogLabelUnity').html(foodcoopshop.LocalizedJs.admin.Weight + '<br />' + '<span>' + foodcoopshop.LocalizedJs.admin.EnterApproximateWeightInPriceDialog + '</span>');
        }

        $(modalSelector + ' #dialogName').focus();

    }

};
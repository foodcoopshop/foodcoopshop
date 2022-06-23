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
foodcoopshop.ModalUploadForm = {

    init : function(button, saveMethod, uploadType) {

        $(button).each(function () {

            var modalSelector = '#upload-form';

            $(this).on('click', function () {

                var objectId = $(this).data('objectId');

                var uploadForm = $('form#mini-upload-form-' + uploadType + '-' + objectId).clone();
                var heading = uploadForm.find('.heading').html();

                foodcoopshop.Modal.appendModalToDom(
                    modalSelector,
                    heading,
                    ''
                );

                foodcoopshop.ModalUploadForm.getOpenHandler($(this), modalSelector, uploadForm, uploadType);

                foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                    saveMethod(modalSelector);
                });

                $(modalSelector).on('hidden.bs.modal', function (e) {
                    foodcoopshop.ModalUploadForm.getCloseHandler(modalSelector);
                });

            });

        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getOpenHandler : function(button, modalSelector, html, uploadType) {

        foodcoopshop.Modal.removeTooltipster();

        // avoid double id in dom
        $(modalSelector + ' .modal-body').append(html);
        var formElement = $(modalSelector + ' .modal-body .mini-upload-form');
        formElement.attr('id', formElement.attr('id') + '-modal');
        if (uploadType == 'image') {
            foodcoopshop.Upload.initUploadButtonImage(modalSelector, formElement, button.data('objectId'));
        } else {
            foodcoopshop.Upload.initUploadButtonFile(modalSelector, formElement, button.data('objectId'));
        }
        foodcoopshop.Upload.loadImageSrcFromDataAttribute(modalSelector);
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
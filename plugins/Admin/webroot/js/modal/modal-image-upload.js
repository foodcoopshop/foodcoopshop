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
foodcoopshop.ModalImageUpload = {

    init : function(button, saveMethod, closeMethod) {
        
        $(button).each(function () {
            
            var modalSelector = '#image-upload';

            $(this).on('click', function () {
                
                var objectId = $(this).data('objectId');
                var imageUploadForm = $('form#mini-upload-form-image-' + objectId).clone();
                var heading = imageUploadForm.find('.heading').html();

                foodcoopshop.Modal.appendModalToDom(
                    modalSelector,
                    heading,
                    ''
                );
                
                foodcoopshop.ModalImageUpload.getOpenHandler($(this), modalSelector, imageUploadForm);
                
                foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                    saveMethod(modalSelector);
                });

                $(modalSelector).on('hidden.bs.modal', function (e) {
                    foodcoopshop.ModalImageUpload.getCloseHandler(modalSelector);
                });
                
            });
            
        });
        
    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getOpenHandler : function(button, modalSelector, html) {
        
        $('.tooltipster-base ').remove(); // on mobile tooltipster is triggered on click - interferes with ckeditor

        // avoid double id in dom
        $(modalSelector + ' .modal-body').append(html);
        var formElement = $(modalSelector + ' .modal-body .mini-upload-form');
        formElement.attr('id', formElement.attr('id') + '-modal');
        foodcoopshop.Upload.initUploadButtonImage(modalSelector, formElement, button.data('objectId'));
        foodcoopshop.Upload.loadImageSrcFromDataAttribute(modalSelector);
        $(modalSelector).modal();
    }

};
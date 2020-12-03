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
foodcoopshop.ModalImage = {

    init : function(button) {

        var modalSelector = '#modal-image';

        $(button).on('click', function () {
            foodcoopshop.ModalImage.getOpenHandler(modalSelector, $(this));
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    addLightboxToWysiwygEditorImages: function(selector) {
        $(selector).each(function () {
            $(this).wrap('<a class="open-with-modal" href="javascript:void(0);" data-modal-image="' + $(this).attr('src') + '"></a>');
        });
    },

    getOpenHandler : function(modalSelector, button) {

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            '',
            '',
            []
        );


        $(modalSelector).modal();

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalImage.getCloseHandler(modalSelector);
        });

        var image = $('<img />');
        image.attr('src', button.data('modal-image'));
        $(modalSelector + ' .modal-body').append(image);
        $(modalSelector + ' .modal-title').html(button.data('modal-title'));
        $(modalSelector + ' .modal-dialog').addClass('modal-dialog-centered');

    }

};
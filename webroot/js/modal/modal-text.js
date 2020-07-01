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
foodcoopshop.ModalText = {

    init : function(button) {

        var modalSelector = '#modal-text';

        $(button).on('click', function () {
            foodcoopshop.ModalText.getOpenHandler(modalSelector, $(this));
        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
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
            foodcoopshop.ModalText.getCloseHandler(modalSelector);
        });

        var elementSelector = button.data('element-selector');
        var heading = $(elementSelector).find('h1');
        $(modalSelector + ' .modal-title').html(heading.html());
        heading.hide();
        $(modalSelector + ' .modal-body').append($(elementSelector).html());
    }

};
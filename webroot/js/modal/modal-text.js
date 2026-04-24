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
            [foodcoopshop.Modal.createButton(['btn-outline-light'], __('Close'), null, true)],
        );

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();

        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalText.getCloseHandler(modalSelector);
        });

        const elementSelector = button.data('element-selector');
        var contentWrapper = $(elementSelector).clone();
        let headingHtml = contentWrapper.find('h1, h3').first().html();

        let contentHtml = contentWrapper.html();
        if (elementSelector === '#modal-info-box-wrapper') {
            headingHtml = 'Infos';
            contentHtml = contentHtml.replace(/h3/g, 'h1');
        }

        $(modalSelector + ' .modal-body').append(contentHtml);
        $(modalSelector + ' .modal-title').html(headingHtml);

    }

};
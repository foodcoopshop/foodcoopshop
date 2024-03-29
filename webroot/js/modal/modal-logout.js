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
foodcoopshop.ModalLogout = {

    init : function(redirect) {

        var modalSelector = '#logout-form';

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.helper.yes, 'fa-fw fas fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.no, null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.helper.logout,
            this.getHtml(),
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalLogout.getSuccessHandler(redirect);
        });

        $('a.logout-button').on('click', function () {
            foodcoopshop.ModalLogout.getOpenHandler(modalSelector);
        });

    },

    getHtml : function() {
        return '<p>' + foodcoopshop.LocalizedJs.helper.logoutInfoText + '</p>';
    },

    getSuccessHandler : function(redirect) {
        var redirectUrl = '/' + foodcoopshop.LocalizedJs.helper.routeLogout;
        if (redirect !== undefined) {
            redirectUrl += '?redirect=' + redirect;
        }
        document.location.href = redirectUrl;
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
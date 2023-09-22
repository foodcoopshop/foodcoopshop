/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalElfinder = {

    init : function(editor) {

        var modalSelector = '#modal-elfinder';

        // if closed via dialog-closer-x it can not be opened again
        // destroy on every open
        foodcoopshop.Modal.destroy(modalSelector);

        var opts = {
            url : '/js/elfinder/php/connector.minimal.php',
            cssAutoLoad: false,
            lang: foodcoopshop.LocalizedJs.helper.defaultLocaleShort,
            i18nBaseUrl: '/js/elfinder/js/i18n/',
            workerBaseUrl: '/js/elfinder/js/worker/',
            soundPath: '/js/elfinder/sounds/',
            uiOptions: {
                toolbar: [
                    ['upload', 'rm'],
                ],
            },
            
            getFileCallback : function(file, fm) {
                foodcoopshop.Modal.destroy(modalSelector);
                if (file.mime.startsWith('image/')) {
                    editor.selection.insertNode(
                        editor.create.fromHTML('<img src="' + file.url + '">')
                    );
                } else {
                    foodcoopshop.Helper.copyToClipboard(fm.convAbsUrl(file.url));
                    foodcoopshop.Helper.showSuccessMessage(foodcoopshop.LocalizedJs.admin.TheUrlOfTheFileHasBeenCopiedToYourClipboard);
                }
            }
        };

        var html = $('<div id="elfinder"></div>').elfinder(opts);
        foodcoopshop.ModalElfinder.getOpenHandler(modalSelector, html);

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getOpenHandler : function(modalSelector, html) {

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.UploadImageOrFile,
            '',
            [],
        );

        $(modalSelector + ' .modal-body').html(html);
        
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
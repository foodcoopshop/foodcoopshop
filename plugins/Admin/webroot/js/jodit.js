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
foodcoopshop.Jodit = {

    getDefaultOptions: function () {
        return {
            theme: foodcoopshop.ColorMode.getColorMode(),
            enter: 'br',
            hidePoweredByJodit: true,
            language: foodcoopshop.LocalizedJs.helper.defaultLocaleShort,
            toolbarAdaptive: false,
            showPlaceholder: false,
            showCharsCounter: false,
            showWordsCounter: false,
            showXPathInStatusbar: false,
            defaultActionOnPaste: 'insert_clear_html',
        };
    },

    getUploadButton: function(editor) {
        var button = {
            name: 'Upload',
            tooltip: foodcoopshop.LocalizedJs.admin.UploadImageOrFile,
            exec: (editor) => {
                foodcoopshop.ModalElfinder.init(editor);
            }
        };
        return button;
    },

    initSmall: function (name, startupFocus) {

        var editor = Jodit.make('textarea#' + name, {
            ...this.getDefaultOptions(),
            buttons: [
                'bold',
                'italic',
                'eraser',
            ],
        });
        
        if (startupFocus) {
            editor.selection.focus();
        }

        return editor;

    },

    initSmallWithUpload: function (name, startupFocus) {

        var editor = Jodit.make('textarea#' + name, {
            ... this.getDefaultOptions(),
            buttons: [
                'bold',
                'italic',
                'eraser',
                this.getUploadButton(editor),
            ],
        });

        if (startupFocus) {
            editor.selection.focus();
        }

        return editor;
    },

    initBig: function (name, startupFocus) {

        var editor = Jodit.make('textarea#' + name, {
            ... this.getDefaultOptions(),
            buttons: [
                'bold', 'italic', 'brush',
                '|', 'undo', 'redo', 'eraser',
                '|', 'paragraph', 'ul', 'ol', 'hr',
                '|', 'left', 'center', 'right',
                '|', 'link', 'image', this.getUploadButton(editor),
                '|','source',
            ],
        });

        if (startupFocus) {
            editor.selection.focus();
        }

        return editor;

    },

};
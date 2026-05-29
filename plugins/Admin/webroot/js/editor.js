/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.Editor = {

    tabNavigationBound: false,

    bindTabToJoditEditors: function () {
        if (this.tabNavigationBound) {
            return;
        }

        this.tabNavigationBound = true;
        $(document).on('keydown', 'input, select, textarea, button, [tabindex]', function (e) {
            if (e.key !== 'Tab' || e.shiftKey || e.altKey || e.ctrlKey || e.metaKey) {
                return;
            }

            var current = e.target;
            if (!current || current.closest('.jodit-container') !== null) {
                return;
            }

            var scope = current.form || document;
            var tabbables = $(scope).find('a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])').filter(':visible');
            var currentIndex = tabbables.index(current);
            if (currentIndex === -1) {
                return;
            }

            var next = tabbables.get(currentIndex + 1);
            if (!next || !next.classList || !next.classList.contains('jodit-wysiwyg')) {
                return;
            }

            e.preventDefault();
            next.focus();
        });
    },

    initJoditEditor: function (selector, options, startupFocus) {
        this.bindTabToJoditEditors();
        const editor = Jodit.make(selector, options);

        // Ensure all editor content areas are reachable via keyboard Tab navigation.
        if (editor && editor.editor && typeof editor.editor.setAttribute === 'function') {
            if (editor.editor.getAttribute('tabindex') === null || editor.editor.tabIndex < 0) {
                editor.editor.setAttribute('tabindex', '0');
            }
        }

        if (startupFocus) {
            editor.selection.focus();
        }

        return editor;
    },

    getDefaultOptions: function () {
        return {
            controls: {
                ul: {
                    list: Jodit.atom({
                        default: 'Default',
                    })
                },
                ol: {
                    list: Jodit.atom({
                        default: 'Default',
                    })
                },
                paragraph: {
                    list: Jodit.atom({
                        p: 'Normal',
                        h2: 'Heading 2',
                        h3: 'Heading 3',
                    }),
                },
            },
            theme: foodcoopshop.ColorMode.getColorMode(),
            enter: 'br',
            hidePoweredByJodit: true,
            language: foodcoopshop.config.defaultLocaleShort,
            toolbarAdaptive: false,
            showPlaceholder: false,
            showCharsCounter: false,
            showWordsCounter: false,
            showXPathInStatusbar: false,
            disablePlugins: 'paste',
            defaultActionOnPaste: 'insert_clear_html',
        };
    },

    getEmojiButton: function() {
        var button = {
            name: ':-)',
            tooltip: 'Emoji',
            exec: () => {
                alert(__('Emoji_explanation_text'));
            }
        };
        return button;
    },

    getUploadButton: function() {
        var button = {
            name: 'Upload',
            tooltip: __('Upload_image_or_file'),
            exec: (editor) => {
                foodcoopshop.ModalElfinder.init(editor);
            }
        };
        return button;
    },

    initSmall: function (name, startupFocus) {
        return this.initJoditEditor('textarea#' + name, {
            ...this.getDefaultOptions(),
            buttons: ['bold', 'italic', 'eraser'],
            height: 220,
            width: 270,
        }, startupFocus);

    },

    initSmallWithUpload: function (name, startupFocus) {
        return this.initJoditEditor('textarea#' + name, {
            ... this.getDefaultOptions(),
            buttons: ['bold', 'italic', 'eraser', this.getUploadButton()],
            height: 364,
            width: 270,
        }, startupFocus);
    },

    initBig: function (name, startupFocus) {
        return this.initJoditEditor('textarea#' + name, {
            ... this.getDefaultOptions(),
            width: 760,
            height: 550,
            buttons: [
                'bold', 'italic', 'brush',
                '|', 'undo', 'redo', 'eraser',
                '|', 'paragraph', 'ul', 'ol', 'hr',
                '|', 'left', 'center', 'right', 'link', 'image', this.getUploadButton(),
                '|', 'source', this.getEmojiButton(),
            ],
        }, startupFocus);

    },

    initBigReduced: function (name, startupFocus) {
        return this.initJoditEditor('textarea#' + name, {
            ... this.getDefaultOptions(),
            width: 760,
            height: 550,
            enter: 'p',
            buttons: ['bold', 'italic', 'eraser',
                '|', 'paragraph', 'ul', 'ol',
            ],

        }, startupFocus);

    },    

};
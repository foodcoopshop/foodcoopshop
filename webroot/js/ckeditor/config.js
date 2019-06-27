/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function ( config ) {

    config.width = 308;
    config.height = 250;
    config.format_tags = 'p';
    config.language = foodcoopshop.LocalizedJs.helper.defaultLocale;

    config.enterMode = CKEDITOR.ENTER_BR;
    config.extraPlugins = 'format';

    config.startupOutlineBlocks = false;
    config.forcePasteAsPlainText = true;

    config.toolbar = [
        { name: 'toolbar', items: ['RemoveFormat', 'Italic', 'Bold'] },
    ];

};

CKEDITOR.timestamp = 'v4.11.4-1'; // change this string if version is updated in package.json


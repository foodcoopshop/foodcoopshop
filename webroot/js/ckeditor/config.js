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

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
    ];

    // Remove some buttons, provided by the standard plugins, which we don't
    // need to have in the Standard(s) toolbar.
    config.removeButtons = 'CreateDiv,Underline,Italic,Subscript,Superscript,Strike,Paste,PasteText,Anchor,Table,HorizontalRule,SpecialChar,Maximize,ShowBlocks,Blockquote,Styles';

};

CKEDITOR.timestamp = 'v4.11.2'; // change this string if version is updated in package.json


/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function ( config ) {

    config.width = 308;
    config.height = 250;
    config.format_tags = 'p';
    config.language = 'de';

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

CKEDITOR.timestamp = '4.8.0'; // change this string if version is updated in package.json


/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function ( config ) {

    config.height = 500;
    config.width = 760;
    config.format_tags = 'p;h2;h3';
    config.language = 'de';
    config.enterMode = CKEDITOR.ENTER_BR;

    config.extraPlugins = 'showblocks,justify,format,colorbutton';

    config.startupOutlineBlocks = false;
    config.forcePasteAsPlainText = true;
    config.allowedContent = true;

    config.filebrowserBrowseUrl = '/js/vendor/kcfinder/browse.php?type=files';
    config.filebrowserImageBrowseUrl = '/js/vendor/kcfinder/browse.php?type=images';
    config.filebrowserUploadUrl = '/js/vendor/kcfinder/upload.php?type=files';
    config.filebrowserImageUploadUrl = '/js/vendor/kcfinder/upload.php?type=images';

    config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'colors' },
        { name: 'links' },
        { name: 'insert' },
        { name: 'paragraph',   groups: [ 'list', 'blocks'] },
        { name: 'styles' },
        { name: 'document',    groups: [ 'mode' ] } // source button
    ];

    config.removeButtons = 'Font,FontSize,CreateDiv,Underline,Italic,Subscript,Superscript,Smiley,Iframe,Flash,Strike,Paste,Anchor,Table,SpecialChar,Maximize,ShowBlocks,Blockquote,Styles,PageBreak';

};

CKEDITOR.timestamp = '4.7.3'; // change this string if version is updated in bower.json


CKEDITOR.editorConfig = function ( config ) {

    config.height = 500;
    config.width = 760;
    config.format_tags = 'p;h2;h3';
    config.language = foodcoopshop.LocalizedJs.helper.defaultLocale;
    config.enterMode = CKEDITOR.ENTER_BR;

    config.extraPlugins = 'showblocks,justify,format,colorbutton';

    config.startupOutlineBlocks = false;
    config.allowedContent = true;

    config.filebrowserBrowseUrl = '/js/elfinder/elfinder.html';
    config.filebrowserImageBrowseUrl = '/js/elfinder/elfinder.html';

    config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'colors' },
        { name: 'links' },
        { name: 'align' },
        { name: 'insert' },
        { name: 'paragraph',   groups: [ 'list', 'blocks'] },
        { name: 'styles' },
        { name: 'document',    groups: [ 'mode' ] } // source button
    ];

    config.removeButtons = 'Font,FontSize,CreateDiv,Underline,Subscript,Superscript,Smiley,Iframe,Flash,Strike,Paste,Anchor,Table,SpecialChar,Maximize,ShowBlocks,Blockquote,Styles,PageBreak,JustifyBlock';

};

CKEDITOR.timestamp = 'v4.11.4-1'; // change this string if version is updated in package.json


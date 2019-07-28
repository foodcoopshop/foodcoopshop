CKEDITOR.editorConfig = function ( config ) {

    config.width = 308;
    config.height = 250;
    config.format_tags = 'p';
    config.language = foodcoopshop.LocalizedJs.helper.defaultLocale;

    config.enterMode = CKEDITOR.ENTER_BR;
    config.extraPlugins = 'format';

    config.startupOutlineBlocks = false;

    config.toolbar = [
        { name: 'toolbar', items: ['RemoveFormat', 'Italic', 'Bold'] },
    ];

};

CKEDITOR.timestamp = 'v4.12.1'; // change this string if version is updated in package.json


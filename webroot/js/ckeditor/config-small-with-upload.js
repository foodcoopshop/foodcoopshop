/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
CKEDITOR.editorConfig = function ( config ) {

    config.width = 413;
    config.height = 250;
    config.format_tags = 'p';
    config.language = foodcoopshop.LocalizedJs.helper.defaultLocale;

    config.enterMode = CKEDITOR.ENTER_BR;
    config.extraPlugins = 'format,emoji';

    config.startupOutlineBlocks = false;

    config.contentsCss = [
        '/node_modules/ckeditor4/contents.css',
        '/js/ckeditor/config-small-with-upload.css'
    ];

    config.filebrowserBrowseUrl = '/js/elfinder/elfinder.html';
    config.filebrowserImageBrowseUrl = '/js/elfinder/elfinder.html';

    config.toolbar = [
        { name: 'toolbar', items: [ 'Image', 'RemoveFormat', 'Italic', 'Bold', 'EmojiPanel'] }
    ];

};

CKEDITOR.timestamp = 'v4.15.1'; // change this string if version is updated in package.json


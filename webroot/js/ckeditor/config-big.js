/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
CKEDITOR.editorConfig = function ( config ) {

    config.height = 500;
    config.width = 760;
    config.format_tags = 'p;h2;h3';
    config.language = foodcoopshop.LocalizedJs.helper.defaultLocale;
    config.enterMode = CKEDITOR.ENTER_BR;

    config.extraPlugins = 'showblocks,justify,format,colorbutton,emoji';

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

CKEDITOR.timestamp = 'v4.21.0'; // change this string if version is updated in package.json


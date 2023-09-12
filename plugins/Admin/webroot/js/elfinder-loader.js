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

foodcoopshop.ElfinderLoader = {
 
    load: function() {
        var opts = {
            url : '/js/elfinder/php/connector.minimal.php',
            cssAutoLoad: false,
            lang: 'de',
            i18nBaseUrl: '/js/elfinder/js/i18n/',

            /*
            sync : 5000,
            sortType : 'date',
            sortOrder : 'desc',
            sortStickFolders : false,
            ui : ['toolbar', 'places', 'tree', 'path', 'stat'],
            commandsOptions : {
                edit : {
                    extraOptions : {
                        uploadOpts : {
                            dropEvt: {shiftKey: true, ctrlKey: true}
                        },
                        managerUrl : 'manager.html',
                    }
                },
            quicklook : {
                googleMapsApiKey : 'AIzaSyAmQiMcWI1e0QryaAHuGNblqJ9xRE2NXL8',
                sharecadMimes : ['image/vnd.dwg', 'image/vnd.dxf', 'model/vnd.dwf', 'application/vnd.hp-hpgl', 'application/plt', 'application/step', 'model/iges', 'application/vnd.ms-pki.stl', 'application/sat', 'image/cgm', 'application/x-msmetafile'],
                googleDocsMimes : ['application/pdf', 'image/tiff', 'application/vnd.ms-office', 'application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/postscript', 'application/rtf'],
                officeOnlineMimes : ['application/msword', 'application/vnd.ms-word', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.presentation']
            },
            opennew : {
                url : 'fullscreen.html'
            }
        },
        parrotHeaders: ['X-elFinder-Token'],
        */
        };
        
        // Make elFinder (REQUIRED)
        $('#elfinder').elfinder(opts, function(fm, extraObj) {
            /*
            fm.bind('init', function() {
                //fm.getUI().css('background-image', 'none');
            });
            // for example set document.title dynamically.
            var title = document.title;
            fm.bind('open', function() {
                var path = '',
                    cwd  = fm.cwd();
                if (cwd) {
                    path = fm.path(cwd.hash) || null;
                }
                document.title = path? path + ':' + title : title;
            }).bind('destroy', function() {
                document.title = title;
            });
            */
        });

    },

};
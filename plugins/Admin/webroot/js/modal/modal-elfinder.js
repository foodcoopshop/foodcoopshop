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
foodcoopshop.ModalElfinder = {

    init : function() {

        var modalSelector = '#modal-elfinder';

        var opts = {
            url : '/js/elfinder/php/connector.minimal.php',
            cssAutoLoad: false,
            lang: 'de',
            i18nBaseUrl: '/js/elfinder/js/i18n/',
            workerBaseUrl: '/js/elfinder/js/worker/',

            //https://github.com/Studio-42/elFinder/issues/2905#issuecomment-487106097
            getFileCallback : function(file, fm) {
                var execCopy = function(string) {
                    var temp = document.createElement('textarea');
            
                    temp.value = string;
                    temp.selectionStart = 0;
                    temp.selectionEnd = temp.value.length;
            
                    var s = temp.style;
                    s.position = 'fixed';
                    s.left = '-100%';
            
                    document.body.appendChild(temp);
                    temp.focus();
                    var result = document.execCommand('copy');
                    
                    temp.blur();
                    document.body.removeChild(temp);
            
                    return result;
                };
                if (execCopy(fm.convAbsUrl(file.url))) {
                    foodcoopshop.Modal.destroy(modalSelector);
                    foodcoopshop.Helper.showSuccessMessage(foodcoopshop.LocalizedJs.admin.TheUrlOfTheImageHasBeenCopiedToYourClipboard);
                }
            }
            /*
            sync : 5000,
            sortType : 'date',
            sortOrder : 'desc',
            sortStickFolders : false,
            ui : ['toolbar', 'places', 'tree', 'path', 'stat'],
            */
            /*
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
        var html = $('<div id="elfinder"></div>').elfinder(opts, function(fm, extraObj) {
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

        foodcoopshop.ModalElfinder.getOpenHandler(modalSelector, html);

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getOpenHandler : function(modalSelector, html) {

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            '',
            '',
            [],
        );

        $(modalSelector + ' .modal-body').html(html);;
        
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
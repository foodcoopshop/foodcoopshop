function setElfinder() {
        var opts = {
            url : '/js/elfinder/php/connector.minimal.php',
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


};
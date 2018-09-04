/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.Upload = {

    saveBlogPostTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.blog_posts input[name="BlogPosts[tmp_image]"').val(filename);
        $('body.blog_posts a.add-image-button').removeClass('uploaded').addClass('uploaded').find('img').attr('src', filename);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },

    saveManufacturerTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.manufacturers input[name="Manufacturers[tmp_image]"').val(filename);
        $('body.manufacturers a.add-image-button').removeClass('uploaded').addClass('uploaded').find('img').attr('src', filename);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },

    saveCategoryTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.categories input[name="Categories[tmp_image]"').val(filename);
        $('body.categories a.add-image-button').removeClass('uploaded').addClass('uploaded').find('img').attr('src', filename);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },

    saveSliderTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.sliders input[name="Sliders[tmp_image]"').val(filename);
        $('body.sliders a.add-image-button').removeClass('uploaded').addClass('uploaded').find('img').attr('src', filename);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },

    saveProductImage : function () {

        var filename = foodcoopshop.Helper.cutRandomStringOffImageSrc($('.featherlight-content form .drop img').attr('src'));

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/saveUploadedImageProduct'
            ,
            {
                objectId : $('.featherlight-content form').data('objectId'),
                filename: filename
            }
            ,
            { onOk : function (data) {
                document.location.reload();
            }
                ,onError : function (data) {
                console.log(data);
            }
            }
        );

    },

    initImageUpload : function (button, saveMethod, closeMethod) {

        $(button).each(function () {

            var objectId = $(this).data('objectId');
            var imageUploadForm = $('form#mini-upload-form-' + objectId);

            $(this).featherlight(
                foodcoopshop.AppFeatherlight.initLightboxForForms(
                    function () {
                        saveMethod();
                    },
                    function () {
                        foodcoopshop.AppFeatherlight.disableSaveButton();
                        foodcoopshop.AppFeatherlight.loadImageSrcFromDataAttribute();
                    },
                    closeMethod,
                    imageUploadForm
                )
            );

            foodcoopshop.Upload.initUploadButton($(this));

        });

    },

    initUploadButton : function (container) {

        $(container).on('click', function () {

            var objectId = $(this).data('objectId');
            var imageUploadForm = $('form#mini-upload-form-' + objectId);

            var buttons = {};
            buttons['no'] = foodcoopshop.Helper.getJqueryUiNoButton();
            buttons['yes'] = {
                text: foodcoopshop.LocalizedJs.helper.yes,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    document.location.href = '/admin/products/deleteImage/' + objectId;
                }
            };

            // bind delete button
            if (imageUploadForm.find('a.img-delete').length == 0) {
                if (imageUploadForm.find('img.existingImage').length == 1) {
                    $('<a title="' + foodcoopshop.LocalizedJs.upload.delete + '" class="modify-icon img-delete" href="javascript:void(0);"><img src="/node_modules/famfamfam-silk/dist/png/delete.png" /></a>').appendTo(imageUploadForm.find('.drop'));
                    imageUploadForm.find('a.img-delete').on('click', function (e) {
                        e.preventDefault();
                        $('<div></div>').appendTo('body')
                            .html('<p>' + foodcoopshop.LocalizedJs.upload.ReallyDeleteImage + '</p><img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />')
                            .dialog({
                                modal: true,
                                title: foodcoopshop.LocalizedJs.upload.DeleteImage,
                                autoOpen: true,
                                width: 400,
                                resizable: false,
                                buttons: buttons,
                                close: function (event, ui) {
                                    $(this).remove();
                                }
                            });
                    });
                }
            }

            var ul = imageUploadForm.find('ul');

            var button = imageUploadForm.find('.drop a.upload-button');
            button.off('click');
            button.on('click', function () {
                // Simulate a click on the file input button to show the file browser dialog
                $(this).parent().find('input').click();
            });

            // Initialize the jQuery File Upload plugin
            imageUploadForm.fileupload({

                // This element will accept file drag/drop uploading
                dropZone: imageUploadForm.find('.drop'),

                autoUpload: false,

                // This function is called when a file is added to the queue;
                // either via the browse button, or via drag/drop:
                add: function (e, data) {

                    var tpl = $('<li class="working"><p></p><input type="text" value="0" data-width="48" data-height="48"'+
                        ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /></li><div class="sc"></div>');

                    // Append the file name and file size
                    tpl.find('p').text(data.files[0].name);
                    //.append('<i>' + formatFileSize(data.files[0].size) + '</i>');

                    // Add the HTML to the UL element
                    data.context = tpl.appendTo(ul);

                    // Initialize the knob plugin
                    tpl.find('input').knob();

                    // Listen for clicks on the cancel icon
                    tpl.find('span').click(function () {

                        if (tpl.hasClass('working')) {
                            jqXHR.abort();
                        }

                        tpl.fadeOut(function () {
                            tpl.remove();
                        });

                    });

                    // Automatically upload the file once it is added to the queue
                    var jqXHR = data.submit();
                },

                progress: function (e, data) {

                    // Calculate the completion percentage of the upload
                    var progress = parseInt(data.loaded / data.total * 100, 10);

                    // Update the hidden input field and trigger a change
                    // so that the jQuery knob plugin knows to update the dial
                    data.context.find('input').val(progress).change();

                    if (progress == 100) {
                        data.context.removeClass('working');
                    }
                },

                done: function (e, data) {

                    imageUploadForm.find('ul li').remove();
                    imageUploadForm.find('img.uploadedFile').remove();
                    imageUploadForm.find('.modify-icon').remove();

                    var result = JSON.parse(data.result);
                    if (result.status) {
                        var container = imageUploadForm.find('.drop');
                        container.find('img').remove();
                        container.prepend($('<img />').
                            attr('src', result.filename).
                            addClass('uploadedFile'));
                        container.append('<a title="' + foodcoopshop.LocalizedJs.upload.rotateAntiClockwise + '" class="modify-icon img-rotate-acw" href="javascript:void(0);"><img src="/node_modules/famfamfam-silk/dist/png/arrow_rotate_anticlockwise.png" /></a>');
                        container.append('<a title="' + foodcoopshop.LocalizedJs.upload.rotateClockwise + '" class="modify-icon img-rotate-cw" href="javascript:void(0);"><img src="/node_modules/famfamfam-silk/dist/png/arrow_rotate_clockwise.png" /></a>');

                        container.find('.img-rotate-acw').on('click', function () {
                            foodcoopshop.Upload.rotateImage($(this), 'CW'); //SIC
                        });

                        container.find('.img-rotate-cw').on('click', function () {
                            foodcoopshop.Upload.rotateImage($(this), 'ACW'); //SIC
                        });

                        imageUploadForm.find('ul li').remove();
                        imageUploadForm.find('button.deleteImage').remove();
                        foodcoopshop.AppFeatherlight.enableSaveButton();
                    } else {
                        imageUploadForm.find('ul li').remove();
                        alert(result.msg);
                    }
                },

                fail: function (e, data) {
                    // Something has gone wrong!
                    data.context.addClass('error');
                }

            });

            // Prevent the default action when a file is dropped on the window
            $(document).on('drop dragover', function (e) {
                e.preventDefault();
            });

        });
    },

    rotateImage : function (button, direction) {

        var image = button.parent().find('img.uploadedFile');
        image.css('opacity', 0.3);

        foodcoopshop.Helper.ajaxCall(
            '/admin/tools/rotateImage/'
            ,
            {filename: foodcoopshop.Helper.cutRandomStringOffImageSrc(image.attr('src')),
                direction: direction}
            ,
            {
                onOk : function (data) {
                    image.attr('src', data.rotatedImageSrc);
                    image.css('opacity', 1);
                }
                ,onError : function (data) {
                    alert(data.message);
                }
            }
        );

    }

};

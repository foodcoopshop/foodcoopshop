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
foodcoopshop.Upload = {

    saveBlogPostTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.blog_posts input[name="BlogPosts[tmp_image]"').val(filename);
        var button = $('body.blog_posts a.add-image-button');
        button.removeClass('uploaded').addClass('uploaded');
        button.html('');
        var newImage = $('<img />').attr('src', filename);
        button.append(newImage);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },

    saveManufacturerTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.manufacturers input[name="Manufacturers[tmp_image]"').val(filename);
        var button = $('body.manufacturers a.add-image-button');
        button.removeClass('uploaded').addClass('uploaded');
        button.html('');
        var newImage = $('<img />').attr('src', filename);
        button.append(newImage);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },

    saveCustomerTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.customers input[name="Customers[tmp_image]"').val(filename);
        var button = $('body.customers a.add-image-button');
        button.removeClass('uploaded').addClass('uploaded');
        button.html('');
        var newImage = $('<img />').attr('src', filename);
        button.append(newImage);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },

    saveManufacturerTmpGeneralTermsAndConditionsInForm : function() {
        var filename = $('.featherlight-content form .drop a').attr('href');
        $('body.manufacturers input[name="Manufacturers[tmp_general_terms_and_conditions]"').val(filename);
        var button= $('body.manufacturers a.add-general-terms-and-conditions-button');
        button.removeClass('uploaded').addClass('uploaded').find('a').attr('href', filename);
        button.find('span').text(foodcoopshop.LocalizedJs.upload.ChangeGeneralTermsAndConditions);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },
    
    saveCategoryTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.categories input[name="Categories[tmp_image]"').val(filename);
        var button = $('body.categories a.add-image-button');
        button.removeClass('uploaded').addClass('uploaded');
        button.html('');
        var newImage = $('<img />').attr('src', filename);
        button.append(newImage);
        foodcoopshop.AppFeatherlight.closeLightbox();
    },

    saveSliderTmpImageInForm : function () {
        var filename = $('.featherlight-content form .drop img').attr('src');
        $('body.sliders input[name="Sliders[tmp_image]"').val(filename);
        var button = $('body.sliders a.add-image-button');
        button.removeClass('uploaded').addClass('uploaded');
        button.html('');
        var newImage = $('<img />').attr('src', filename);
        button.append(newImage);
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
            var imageUploadForm = $('form#mini-upload-form-image-' + objectId);

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

            foodcoopshop.Upload.initUploadButtonImage($(this));

        });

    },

    initFileUpload : function (button, saveMethod, closeMethod) {

        $(button).each(function () {

            var objectId = $(this).data('objectId');
            var fileUploadForm = $('form#mini-upload-form-file-' + objectId);

            $(this).featherlight(
                foodcoopshop.AppFeatherlight.initLightboxForForms(
                    function () {
                        saveMethod();
                    },
                    function () {
                        foodcoopshop.AppFeatherlight.disableSaveButton();
                    },
                    closeMethod,
                    fileUploadForm
                )
            );

            foodcoopshop.Upload.initUploadButtonFile($(this));

        });

    },
    
    initUploadButtonFile: function (container) {

        $(container).on('click', function () {

            var objectId = $(this).data('objectId');
            var fileUploadForm = $('form#mini-upload-form-file-' + objectId);
            var ul = fileUploadForm.find('ul');

            var button = fileUploadForm.find('.drop a.upload-button');
            button.off('click');
            button.on('click', function () {
                // Simulate a click on the file input button to show the file browser dialog
                $(this).parent().find('input').trigger('click');
            });

            // Initialize the jQuery File Upload plugin
            fileUploadForm.fileupload({

                // This element will accept file drag/drop uploading
                dropZone: fileUploadForm.find('.drop'),

                autoUpload: false,

                add: function (e, data) {
                    foodcoopshop.Upload.fileUploadAdd(e, data, ul);
                },

                progress: foodcoopshop.Upload.fileUploadProgress,
                
                done: function (e, data) {

                    fileUploadForm.find('ul li').remove();
                    
                    var result = JSON.parse(data.result);
                    if (result.status) {
                        var container = fileUploadForm.find('.drop');
                        container.find('a').not('.upload-button').remove();
                        container.prepend($('<a />').
                            attr('href', result.filename).
                            addClass('uploadedFile').
                            attr('target', '_blank').
                            text(result.text));
                        fileUploadForm.find('ul li').remove();
                        foodcoopshop.AppFeatherlight.enableSaveButton();
                    } else {
                        fileUploadForm.find('ul li').remove();
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

    /**
     * This function is called when a file is added to the queue;
     * either via the browse button, or via drag/drop:
     */
    fileUploadAdd : function (e, data, ul) {

        var tpl = $('<li class="working"><p></p><input type="text" value="0" data-width="48" data-height="48"'+
            ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /></li><div class="sc"></div>');

        // Append the file name and file size
        tpl.find('p').text(data.files[0].name);

        // Add the HTML to the UL element
        data.context = tpl.appendTo(ul);

        // Initialize the knob plugin
        tpl.find('input').knob();

        // Listen for clicks on the cancel icon
        tpl.find('span').on('click', function () {

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
    
    fileUploadProgress : function (e, data) {

        // Calculate the completion percentage of the upload
        var progress = parseInt(data.loaded / data.total * 100, 10);

        // Update the hidden input field and trigger a change
        // so that the jQuery knob plugin knows to update the dial
        data.context.find('input').val(progress).change();

        if (progress == 100) {
            data.context.removeClass('working');
        }
    },

    initUploadButtonImage : function (container) {

        $(container).on('click', function () {

            var objectId = $(this).data('objectId');
            var imageUploadForm = $('form#mini-upload-form-image-' + objectId);

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
                    $('<a title="' + foodcoopshop.LocalizedJs.upload.delete + '" class="modify-icon img-delete" href="javascript:void(0);"><i class="fas fa-times-circle not-ok"></i></a>').appendTo(imageUploadForm.find('.drop'));
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
                $(this).parent().find('input').trigger('click');
            });

            // Initialize the jQuery File Upload plugin
            imageUploadForm.fileupload({

                // This element will accept file drag/drop uploading
                dropZone: imageUploadForm.find('.drop'),

                autoUpload: false,

                add: function (e, data) {
                    foodcoopshop.Upload.fileUploadAdd(e, data, ul);
                },

                progress: foodcoopshop.Upload.fileUploadProgress,

                done: function (e, data) {

                    imageUploadForm.find('ul li').remove();
                    imageUploadForm.find('img.uploadedImage').remove();
                    imageUploadForm.find('.modify-icon').remove();

                    var result = data.result;
                    if (result.status) {
                        var container = imageUploadForm.find('.drop');
                        container.find('img').remove();
                        container.prepend($('<img />').
                            attr('src', result.filename).
                            addClass('uploadedImage'));
                        container.append('<a title="' + foodcoopshop.LocalizedJs.upload.rotateAntiClockwise + '" class="modify-icon img-rotate-acw" href="javascript:void(0);"><i class="fas fa-undo"></a>');
                        container.append('<a title="' + foodcoopshop.LocalizedJs.upload.rotateClockwise + '" class="modify-icon img-rotate-cw" href="javascript:void(0);"><i class="fas fa-redo"></a>');

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

        var image = button.parent().find('img.uploadedImage');
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

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
foodcoopshop.Admin = {

    init: function () {
        this.initFilter();
        this.improveTableLayout();
        foodcoopshop.Helper.initJqueryUiIcons();
        foodcoopshop.Helper.showContent();
        foodcoopshop.Helper.initMenu();
        foodcoopshop.ModalLogout.init();
        this.setMenuFixed();
        this.adaptContentMargin();
        foodcoopshop.Helper.initScrolltopButton();
    },

    addWrappersAndLoaderToDialogHtml : function(title, dialogId, dialogHtml) {
        var html = '<div id="' + dialogId + '" class="dialog" title="' + title + '">';
        html += '<form onkeypress="return event.keyCode != 13;">';
        html += dialogHtml;
        html += '<img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';
        html += '</form>';
        html += '</div>';
        return html;
    },

    disableSelectpickerItems : function (selector, ids) {
        $(selector).find('option').each(function () {
            var currentId = parseInt($(this).val());
            if ($.inArray(currentId, ids) !== -1) {
                $(this).attr('disabled', 'disabled');
            }
        });
        $(selector).selectpicker('render');
    },

    addLoaderToSyncProductDataButton : function (button) {
        button.on('click', function () {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-arrow-circle-left');
            foodcoopshop.Helper.disableButton($(this));
        });
    },

    selectMainMenuAdmin: function (mainMenuTitle, subMenuTitle) {
        foodcoopshop.Helper.selectMainMenu('#menu', mainMenuTitle, subMenuTitle);
    },

    /**
     * @return rowMarker dom element
     */
    initRowMarkerAll : function () {
        var rowMarkerAll = $('input#row-marker-all').on('change', function () {
            if (this.checked) {
                $('input.row-marker[type="checkbox"]:not(:checked)').trigger('click');
            } else {
                $('input.row-marker[type="checkbox"]:checked').trigger('click');
            }
        });
        return rowMarkerAll;
    },

    getSelectedOrderDetailIds : function() {
        var orderDetailIds = [];
        $('table.list').find('input.row-marker[type="checkbox"]:checked').each(function () {
            var orderDetailId = $(this).closest('tr').find('td:nth-child(2)').html();
            orderDetailIds.push(orderDetailId);
        });
        return orderDetailIds;
    },

    getSelectedProductIds : function() {
        var productIds = [];
        $('table.list').find('input.row-marker[type="checkbox"]:checked').each(function () {
            var productId = $(this).closest('tr').find('td.cell-id').html();
            productIds.push(productId);
        });
        return productIds;
    },

    getSelectedCustomerIds : function() {
        var customerIds = [];
        $('table.list').find('input.row-marker[type="checkbox"]:checked').each(function () {
            var customerId = $(this).closest('tr').find('td:nth-child(2)').html();
            customerIds.push(customerId);
        });
        return customerIds;
    },

    initChangePickupDayOfSelectedProductsButton : function () {
        var button = $('#changePickupDayOfSelectedProductsButton');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"]').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {
            var orderDetailIds = foodcoopshop.Admin.getSelectedOrderDetailIds();
            foodcoopshop.Admin.openBulkChangePickupDayDialog(orderDetailIds);
        });

    },

    updateObjectSelectionActionButton : function (button) {
        foodcoopshop.Helper.disableButton(button);
        if ($('table.list').find('input.row-marker[type="checkbox"]:checked').length > 0) {
            foodcoopshop.Helper.enableButton(button);
        }
    },

    initFilter: function (callback) {

        var filterContainer = $('.filter-container');

        filterContainer.find('input:text').on('keyup', function (e) {
            if (e.keyCode == 13) {
                foodcoopshop.Admin.submitFilterForm();
            }
        });

        foodcoopshop.Helper.initBootstrapSelect(filterContainer);

        this.setSelectPickerMultipleDropdowns('.filter-container select[multiple="multiple"]');

        filterContainer.find('input:text, input:checkbox, select:not(.do-not-submit)').on('change', function () {
            foodcoopshop.Admin.triggerFilter();
        });

    },

    /**
     * multiple dropdowns need to be selected manually
     * therefore data-val must be set!
     */
    setSelectPickerMultipleDropdowns : function (selector) {
        $(selector).each(function () {
            var val = $(this).data('val');
            if (val) {
                $(this).selectpicker('val', val.toString().split(','));
            }
        });
    },

    submitFilterForm: function () {
        $('.filter-container form').submit();
    },

    improveTableLayout: function () {

        // copy first row with sums
        var table = $('table.list');
        if (!table.hasClass('no-clone-last-row')) {
            var lastRow = table.find('tr:last-child').clone();
            table.find('tr:first-child').after(lastRow);
        }
        table.show();

        // change color of row on click of checkbox
        table.find('input.row-marker[type="checkbox"]').on('click', function () {
            if ($(this).parent().parent().hasClass('selected')) {
                $(this).parent().parent().removeClass('selected');
            } else {
                $(this).parent().parent().addClass('selected');
            }
        });

    },

    initProductDepositEditDialog: function (container) {

        var dialogId = 'product-deposit-edit-form';
        var dialogHtml = foodcoopshop.DialogProduct.getHtmlForProductDepositEdit(dialogId);
        $(container).append(dialogHtml);

        foodcoopshop.Helper.changeInputNumberToTextForEdge();

        var buttons = {};
        buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
        buttons['save'] = {
            text: foodcoopshop.LocalizedJs.helper.save,
            click: function() {
                if ($('#dialogDepositDeposit').val() == '' || $('#dialogDepositProductId').val() == '') {
                    return false;
                }

                $('#product-deposit-edit-form .ajax-loader').show();
                $('.ui-dialog button').attr('disabled', 'disabled');

                foodcoopshop.Helper.ajaxCall(
                    '/admin/products/editDeposit/',
                    {
                        productId: $('#dialogDepositProductId').val(),
                        deposit: $('#dialogDepositDeposit').val(),
                    },
                    {
                        onOk: function (data) {
                            document.location.reload();
                        },
                        onError: function (data) {
                            var form = $('#product-deposit-edit-form form');
                            form.find('.ajax-loader').hide();
                            foodcoopshop.Helper.appendFlashMessageToDialog(form, data.msg);
                        }
                    }
                );
            }
        };

        var dialog = $('#' + dialogId).dialog({
            autoOpen: false,
            height: 200,
            width: 350,
            modal: true,
            close: function () {
                $('#dialogDepositDeposit').val('');
                $('#dialogDepositProductId').val('');
            },
            buttons: buttons
        });

        $('.product-deposit-edit-button').on('click', function () {
            var row = $(this).closest('tr');
            $('#' + dialogId + ' #dialogDepositDeposit').val(row.find('span.deposit-for-dialog').html());
            $('#' + dialogId + ' #dialogDepositProductId').val(row.find('td.cell-id').html());
            var label = foodcoopshop.Admin.getProductNameForDialog(row);
            $('#' + dialogId + ' label[for="dialogDepositDeposit"]').html(label);
            dialog.dialog('open');
        });

    },

    getProductNameForDialog : function(row) {
        var label = row.find('span.name-for-dialog').html();
        // show name of main product
        if (row.hasClass('sub-row')) {
            label = row.prevAll('.main-product:first').find('span.name-for-dialog .product-name').html() + ': ' + label;
        }
        return label;
    },

    decodeEntities : function (encodedString) {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = encodedString;
        return textArea.value;
    },

    initHighlightedRowId: function (rowId) {
        $.scrollTo(rowId, 1000, {
            offset: {
                top: -100
            }
        });
        $(rowId).css('background-color', 'orange');
        $(rowId).css('color', 'white');
        $(rowId).one('mouseover', function () {
            $(this).removeAttr('style');
        });
    },

    initDeleteSelectedProducts : function() {

        var button = $('#deleteSelectedProducts');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"]').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {
            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            foodcoopshop.Admin.openDeleteProductsDialog(productIds);
        });

    },

    openDeleteProductsDialog : function(productIds) {

        var buttons = {};
        buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
        buttons['yes'] = {
            text: foodcoopshop.LocalizedJs.helper.yes,
            click: function() {
                $('#delete-products-dialog .ajax-loader').show();
                $('.ui-dialog button').attr('disabled', 'disabled');
                foodcoopshop.Helper.ajaxCall(
                    '/admin/products/delete/',
                    {
                        productIds: productIds
                    },
                    {
                        onOk: function (data) {
                            document.location.reload();
                        },
                        onError: function (data) {
                            var form = $('#delete-products-dialog');
                            form.find('.ajax-loader').hide();
                            var message = '<p><b>';
                            if (productIds.length == 1) {
                                message += foodcoopshop.LocalizedJs.admin.ErrorsOccurredWhileProductWasDeleted;
                            } else {
                                message += foodcoopshop.LocalizedJs.admin.ErrorsOccurredWhileProductsWereDeleted;
                            }
                            message += ':</b> </p>';
                            foodcoopshop.Helper.appendFlashMessageToDialog(form, message + data.msg);
                        }
                    });
            }
        };

        var html = '<p style="margin-top: 10px;">';
        if (productIds.length == 1) {
            html += foodcoopshop.LocalizedJs.admin.ReallyDeleteOneProduct;
        } else {
            html += foodcoopshop.LocalizedJs.admin.ReallyDelete0Products.replace(/\{0\}/, '<b>' + productIds.length + '</b>');
        }
        html += '</p><p>' + foodcoopshop.LocalizedJs.admin.BeCarefulNoWayBack + '</p>';

        var products = [];
        for (var i in productIds) {
            products.push($('tr#product-' + productIds[i] + ' span.product-name').html());
        }
        html += '<ul><li>' + products.join('</li><li>') + '</li></ul>';

        html += '<img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';

        $('<div id="delete-products-dialog"></div>').appendTo('body')
            .html(html)
            .dialog({
                modal: true,
                title: productIds.length == 1 ? foodcoopshop.LocalizedJs.admin.DeleteProduct : foodcoopshop.LocalizedJs.admin.DeleteProducts,
                autoOpen: true,
                width: 500,
                height: 300,
                resizable: false,
                buttons: buttons,
                close: function (event, ui) {
                    $(this).remove();
                }
            });
    },

    initProductIsStockProductEditDialog: function (container) {

        $('.product-is-stock-product-edit-button').on('click', function () {

            var row = $(this).closest('tr');

            var dialogId = 'product-is-stock-product-edit-form';
            var dialogHtml = foodcoopshop.DialogProduct.getHtmlForProductIsStockProductEdit(dialogId);
            $(container).append(dialogHtml);

            var buttons = {};
            buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
            buttons['save'] = {
                text: foodcoopshop.LocalizedJs.helper.save,
                click: function() {
                    if ($('#dialogIsStockProductProductId').val() == '') {
                        return false;
                    }

                    $('#product-is-stock-product-edit-form .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');

                    var data = {
                        productId: $('#dialogIsStockProductProductId').val(),
                        isStockProduct: $('#dialogIsStockProductIsStockProduct:checked').length > 0 ? 1 : 0
                    };

                    foodcoopshop.Helper.ajaxCall(
                        '/admin/products/editIsStockProduct/',
                        data,
                        {
                            onOk: function (data) {
                                document.location.reload();
                            },
                            onError: function (data) {
                                var form = $('#product-is-stock-product-edit-form form');
                                form.find('.ajax-loader').hide();
                                foodcoopshop.Helper.appendFlashMessageToDialog(form, data.msg);
                            }
                        }
                    );

                }
            };

            var dialogOptions = {
                autoOpen: false,
                height: 300,
                width: 350,
                modal: true,
                close: function () {
                    $('#dialogIsStockProductIsStockProduct').val('');
                    $('#dialogIsStockProductProductId').val('');
                },
                buttons: buttons
            };

            $('#' + dialogId + ' #dialogIsStockProductIsStockProduct').prop('checked', row.find('td.is-stock-product').html().match('fa-check'));
            $('#' + dialogId + ' #dialogIsStockProductProductId').val(row.find('td.cell-id').html());
            $('#' + dialogId + ' label[for="dialogIsStockProductIsStockProduct"]').html(foodcoopshop.Admin.getProductNameForDialog(row));

            var dialog = $('#' + dialogId).dialog(dialogOptions);
            dialog.dialog('open');
        });

    },

    bindToggleQuantityQuantityByModalSelector : function(modalSelector) {
        var modal = $(modalSelector);
        modal.find('#dialogQuantityAlwaysAvailable').on('change', function() {
            var quantityWrapper = modal.find('.quantity-wrapper');
            var dialogQuantityElement = modal.find('#dialogQuantityAlwaysAvailable');
            if (dialogQuantityElement.prop('checked')) {
                quantityWrapper.hide();
            } else {
                quantityWrapper.show();
            }
        });
    },

    bindToggleQuantityQuantity : function(dialogId) {
        var dialog = $('#' + dialogId);
        dialog.find('#dialogQuantityAlwaysAvailable').on('change', function() {
            var quantityWrapper = dialog.find('.quantity-wrapper');
            var dialogQuantityElement = dialog.find('#dialogQuantityAlwaysAvailable');
            if (dialogQuantityElement.prop('checked')) {
                quantityWrapper.hide();
            } else {
                quantityWrapper.show();
            }
        });
    },

    initProductQuantityList: function(container) {
        var rowContainer = $(container).find('td.amount');
        rowContainer.each(function() {
            var elements = $(this).find('> i, > span').not('.hide');
            elements.addClass('has-separator');
            elements.last().removeClass('has-separator');
        });
    },

    isAdvancedStockManagementEnabled : function(row) {
        if (row.hasClass('sub-row')) {
            row = row.prevAll('.main-product').first();
        }
        return row.find('td.is-stock-product').length > 0 && row.find('td.is-stock-product').html().match('fa-check');
    },

    openBulkChangePickupDayDialog : function(orderDetailIds) {

        $('#cke_dialogChangePickupDayReason').val('');
        var dialogId = 'order-detail-pickup-day-edit-form';

        var dialogHtml = '';
        dialogHtml += '<div class="field-wrapper">';
        dialogHtml += '<label>' + foodcoopshop.LocalizedJs.admin.NewPickupDay + '</label>';
        dialogHtml += '<input style="margin-left:10px;" autocomplete="off" class="datepicker" type="text" name="dialogChangePickupDay" id="dialogChangePickupDay" /><br />';
        dialogHtml += '</div>';
        dialogHtml += '<p style="margin-top:10px;float:left;">' + foodcoopshop.LocalizedJs.admin.ChangePickupDayInvoicesInfoText + '</p>';
        dialogHtml += '<div style="margin-top:10px;float:left;" class="textarea-wrapper">';
        dialogHtml += '<label for="dialogChangePickupDayReason">' + foodcoopshop.LocalizedJs.admin.WhyIsPickupDayChanged +'</label>';
        dialogHtml += '<textarea class="ckeditor" name="dialogChangePickupDayReason" id="dialogChangePickupDayReason"></textarea>';
        dialogHtml += '</div>';
        dialogHtml = foodcoopshop.Admin.addWrappersAndLoaderToDialogHtml(
            foodcoopshop.LocalizedJs.admin.ChangePickupDay + ': ' + orderDetailIds.length + ' ' + (
                orderDetailIds.length == 1 ? foodcoopshop.LocalizedJs.admin.product : foodcoopshop.LocalizedJs.admin.products
            ) , dialogId, dialogHtml
        );
        $('body').append(dialogHtml);

        var buttons = {};
        buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
        buttons['save'] = {
            text: foodcoopshop.LocalizedJs.helper.save,
            click: function() {
                var ckeditorData = CKEDITOR.instances['dialogChangePickupDayReason'].getData().trim();
                $('.ui-dialog .ajax-loader').show();
                $('.ui-dialog button').attr('disabled', 'disabled');
                foodcoopshop.Helper.ajaxCall(
                    '/admin/order-details/editPickupDay',
                    {
                        orderDetailIds: orderDetailIds,
                        pickupDay: $('#dialogChangePickupDay').val(),
                        changePickupDayReason: ckeditorData
                    },
                    {
                        onOk: function (data) {
                            document.location.reload();
                        },
                        onError: function (data) {
                            var form = $('#order-detail-pickup-day-edit-form form');
                            form.find('.ajax-loader').hide();
                            foodcoopshop.Helper.appendFlashMessageToDialog(form, data.msg);
                        }
                    }
                );
            }
        };

        var dialog = $('#' + dialogId).dialog({
            modal: true,
            autoOpen: true,
            width: 400,
            open: function () {
                foodcoopshop.Helper.initCkeditor('dialogChangePickupDayReason');
                $('#dialogChangePickupDay').blur();
            },
            resizable: false,
            buttons: buttons,
            close: function (event, ui) {
                $('#' + dialogId).remove(); // ckeditor was always empty if opened more than once
                foodcoopshop.Helper.destroyCkeditor('dialogChangePickupDayReason');
            }
        });

        dialog.dialog('open');

        foodcoopshop.Helper.initDatepicker();
        var datepickerInput = $('#dialogChangePickupDay');
        datepickerInput.val($('.filter-container input[name="pickupDay[]"').val());
        foodcoopshop.Admin.addDatepickerInDialog(datepickerInput);

    },

    initChangeNewState: function () {

        $('.change-new-state').on('click', function () {

            var productId = $(this).attr('id').split('-');
            productId = productId[productId.length - 1];

            var newState = 1;
            var newStateText = foodcoopshop.LocalizedJs.admin.ShowProductAsNew;
            var reallyNewStateText = foodcoopshop.LocalizedJs.admin.ReallyShowProduct0AsNew;
            if ($(this).hasClass('change-new-state-inactive')) {
                newState = 0;
                newStateText = foodcoopshop.LocalizedJs.admin.DoNotShowProductAsNew;
                reallyNewStateText = foodcoopshop.LocalizedJs.admin.ReallyDoNotShowProduct0AsNew;
            }

            var buttons = {};
            buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
            buttons['save'] = {
                text: foodcoopshop.LocalizedJs.helper.save,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    document.location.href = '/admin/products/changeNewStatus/' + productId + '/' + newState;
                }
            };

            var dataRow = $(this).closest('tr');
            $('<div></div>').appendTo('body')
                .html('<p>' + reallyNewStateText.replaceI18n(0,  '<b>' + dataRow.find('td.cell-name span.name-for-dialog').html() + '</b>') + '</p><img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />')
                .dialog({
                    modal: true,
                    title: newStateText,
                    autoOpen: true,
                    width: 400,
                    resizable: false,
                    buttons: buttons,
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });
        });
    },

    initDeleteProductAttribute: function (container) {

        $(container).find('.delete-product-attribute-button').on('click', function () {

            var splittedProductId = $(this).closest('tr').attr('id').replace(/product-/, '').split('-');
            var productId = splittedProductId[0];
            var productAttributeId = splittedProductId[1];

            var dataRow = $(this).closest('tr');
            var htmlCode = '<p>' + foodcoopshop.LocalizedJs.admin.ReallyDeleteAttribute0.replaceI18n(0, '<b>' + dataRow.find('td.cell-name span.name-for-dialog').html() + '</b>');
            htmlCode += '<img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';

            var buttons = {};
            buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
            buttons['yes'] = {
                text: foodcoopshop.LocalizedJs.helper.yes,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    document.location.href = '/admin/products/deleteProductAttribute/' + productId + '/' + productAttributeId;
                }
            };

            $('<div></div>').appendTo('body')
                .html(htmlCode)
                .dialog({
                    modal: true,
                    title: foodcoopshop.LocalizedJs.admin.DeleteAttribute,
                    autoOpen: true,
                    width: 450,
                    resizable: false,
                    buttons: buttons,
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });
        });

    },

    initAddProductAttribute: function (container) {

        $(container).find('.add-product-attribute-button').on('click', function () {

            var dataRow = $(this).closest('tr');
            var productId = dataRow.attr('id').replace(/product-/, '').split('-');
            productId = productId[productId.length - 1];

            var htmlCode = '<p>' + foodcoopshop.LocalizedJs.admin.PleaseChoseTheNewAttributeForProduct0.replaceI18n(0, '<b> ' + dataRow.find('td.cell-name span.name-for-dialog').html() + '</b>') + '</p>';
            var productAttributesDropdown = $('#productattributeid').clone(true);

            if (productAttributesDropdown.find('option').length == 0) {
                alert(foodcoopshop.LocalizedJs.admin.ThisFunctionCanOnlyBeUsedIfAttributesExist);
                return;
            }

            productAttributesDropdown.show();
            productAttributesDropdown.removeClass('hide');
            htmlCode += '<select class="product-attributes-dropdown">' + productAttributesDropdown.html() + '</select>';

            htmlCode += '<img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';

            var buttons = {};
            buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
            buttons['save'] = {
                text: foodcoopshop.LocalizedJs.helper.save,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    document.location.href = '/admin/products/addProductAttribute/' + productId + '/' + $('.product-attributes-dropdown').val();
                }
            };

            $('<div></div>').appendTo('body')
                .html(htmlCode)
                .dialog({
                    modal: true,
                    title: foodcoopshop.LocalizedJs.admin.AddNewAttributeForProduct,
                    autoOpen: true,
                    width: 450,
                    resizable: false,
                    buttons: buttons,
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });
        });

    },

    initSetDefaultAttribute: function (container) {
        $(container).find('.set-as-default-attribute-button').on('click', function () {

            var row = $(this).closest('tr');

            var productIdString = row.attr('id').replace(/product-/, '').split('-');
            var productId = productIdString[0];
            var attributeId = productIdString[1];

            var label = foodcoopshop.Admin.getProductNameForDialog(row);
            var htmlCode = foodcoopshop.LocalizedJs.admin.ChangingDefaultAttributeInfoText0Html.replaceI18n(0, '<b>' + label + '</b>');
            htmlCode += '<img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';

            var buttons = {};
            buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
            buttons['save'] = {
                text: foodcoopshop.LocalizedJs.helper.save,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    document.location.href = '/admin/products/changeDefaultAttributeId/' + productId + '/' + attributeId;
                }
            };

            $('<div></div>').appendTo('body')
                .html(htmlCode)
                .dialog({
                    modal: true,
                    title: foodcoopshop.LocalizedJs.admin.ChangeDefaultAttribute,
                    autoOpen: true,
                    width: 450,
                    resizable: false,
                    buttons: buttons,
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });

        });
    },

    initCopySelectedCustomerEmailsToClipboard: function() {

        var btnSelector = '.btn-clipboard';
        var button = $(btnSelector);

        foodcoopshop.Helper.disableButton(button);
        $('table.list').find('input.row-marker[type="checkbox"]').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        var clipboard = new ClipboardJS(
            btnSelector,
            {
                text: function(trigger) {
                    var customerIds = foodcoopshop.Admin.getSelectedCustomerIds();
                    var emails = [];
                    for(var i=0; i < customerIds.length; i++) {
                        var email = $('tr.data[data-customer-id="'+customerIds[i]+'"]').find('span.email').html();
                        emails.push(email);
                    }
                    return emails.join(',');
                }
            }
        );

        clipboard.on('success', function(e) {
            var emailAddressesCount = e.text.split(',').length;
            var response = foodcoopshop.LocalizedJs.admin.EmailAddressesSuccessfullyCopiedToClipboard.replaceI18n(0, emailAddressesCount);
            if (emailAddressesCount == 1) {
                response = foodcoopshop.LocalizedJs.admin.OneEmailAddressSuccessfullyCopiedToClipboard;
            }
            foodcoopshop.Helper.showSuccessMessage(response);
        });

    },

    initEmailToAllButton: function () {
        var clipboard = new ClipboardJS('.btn-clipboard');
        clipboard.on('success', function(e) {
            var emailAddressesCount = e.text.split(',').length;
            var response = foodcoopshop.LocalizedJs.admin.EmailAddressesSuccessfullyCopiedToClipboard.replaceI18n(0, emailAddressesCount);
            if (emailAddressesCount == 1) {
                response = foodcoopshop.LocalizedJs.admin.OneEmailAddressSuccessfullyCopiedToClipboard;
            }
            foodcoopshop.Helper.showSuccessMessage(response);
        });
    },

    initForm: function () {

        $('.filter-container .right a.submit').on('click', function () {
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
            $(this).closest('#container').find('form.fcs-form').submit();
        });

        $('.filter-container .right a.cancel').on('click', function () {
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-times');
            var referer = $('input[name=referer').val();
            if (referer == '') {
                referer = '/';
            }
            document.location.href = referer;
        });

        // copy save and cancel button below form
        var form = $('form.fcs-form');
        form.after('<div class="form-buttons"></div>');
        $('#content .form-buttons').append($('.filter-container .right > a.submit, .filter-container .right > a.cancel').clone(true)); // true clones events

        // submit form on enter in text fields
        form.find('input[type=text], input[type=number], input[type=password], input[type="tel"]').keypress(function (e) {
            if (e.which == 13) {
                $(this).blur();
                $('.filter-container .right a.submit').trigger('click');
            }
        });

        form.find('select').not('.selectpicker-disabled').selectpicker({
            liveSearch: true,
            showIcon: true
        });

        var afterLabelElement = form.find('label span.after');
        afterLabelElement.each(function () {
            var parentWrapper = $(this).closest('.input');
            var errorWrapper = parentWrapper.find('.error-message');
            if (errorWrapper.length > 0) {
                errorWrapper.before($(this));
            } else {
                $(this).appendTo(parentWrapper);
            }
        });

        var errorWrapper = form.find('.error-message');
        errorWrapper.each(function () {
            if ($(this).prev().hasClass('long')) {
                $(this).addClass('long');
            }
        });

    },

    triggerFilter : function () {
        $('#filter-loader').remove();
        $('#content').css('opacity', '.3');
        $('#container').prepend('<div id="filter-loader"><i class="fas fa-circle-notch"></i></div>');
        var marginTop = $('.filter-container').outerHeight();
        $('#filter-loader').css('top', marginTop + 20);
        foodcoopshop.Admin.submitFilterForm();
    },

    initNextAndPreviousDayLinks: function () {
        $('.btn-previous-day').on('click', function () {
            var datepicker = $(this).next();
            var date = datepicker.datepicker('getDate');
            date.setDate(date.getDate() - 1);
            datepicker.datepicker('setDate', date);
            if ($(this).closest('.filter-container').length > 0) {
                foodcoopshop.Admin.triggerFilter();
            }
        });
        $('.btn-next-day').on('click', function () {
            var datepicker = $(this).prev();
            var date = datepicker.datepicker('getDate');
            date.setDate(date.getDate() + 1);
            datepicker.datepicker('setDate', date);
            if ($(this).closest('.filter-container').length > 0) {
                foodcoopshop.Admin.triggerFilter();
            }
        });
    },

    setOrderDetailTimebasedCurrencyData : function(elementToAttach, timebasedCurrencyObject) {
        elementToAttach.data('timebased-currency-object', $.parseJSON(timebasedCurrencyObject));
    },

    setProductUnitData : function(elementToAttach, productUnitObject) {
        elementToAttach.data('product-unit-object', $.parseJSON(productUnitObject));
    },

    initOrderDetailCustomerEditDialog: function (container) {

        $('#cke_dialogEditCustomerReason').val('');

        var dialogId = 'order-detail-customer-edit-form';
        var dialogHtml = foodcoopshop.DialogOrderDetail.getHtmlForOrderDetailCustomerEdit(dialogId);
        $(container).append(dialogHtml);

        var customerDropdownSelector = '#dialogOrderDetailEditCustomerId';
        $(customerDropdownSelector).selectpicker({
            liveSearch: true,
            size: 7,
            title: foodcoopshop.LocalizedJs.admin.PleaseSelectNewMember
        });
        foodcoopshop.Admin.initCustomerDropdown(0, 0, 0, customerDropdownSelector);

        var buttons = {};
        buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
        buttons['save'] = {
            text: foodcoopshop.LocalizedJs.helper.save,
            click: function() {

                var ckeditorData = CKEDITOR.instances['dialogEditCustomerReason'].getData().trim();
                var customerId = $('#dialogOrderDetailEditCustomerId').val();

                $('#order-detail-customer-edit-form .ajax-loader').show();
                $('.ui-dialog button').attr('disabled', 'disabled');

                foodcoopshop.Helper.ajaxCall(
                    '/admin/order-details/editCustomer/',
                    {
                        orderDetailId: $('#dialogOrderDetailEditCustomerOrderDetailId').val(),
                        customerId: customerId,
                        editCustomerReason: ckeditorData,
                        amount: $('#dialogOrderDetailEditCustomerAmount').val()
                    },
                    {
                        onOk: function (data) {
                            document.location.reload();
                        },
                        onError: function (data) {
                            var form = $('#order-detail-customer-edit-form');
                            form.find('.ajax-loader').hide();
                            foodcoopshop.Helper.appendFlashMessageToDialog(form, data.msg);
                        }
                    }
                );

            }
        };

        var dialog = $('#' + dialogId).dialog({

            autoOpen: false,
            width: 550,
            modal: true,
            close: function () {
                $('#dialogOrderDetailCustomerId').val('');
                $('#dialogOrderDetailEditCustomerOrderDetailId').val('');
                foodcoopshop.Helper.destroyCkeditor('dialogEditCustomerReason');
            },
            open: function () {
                foodcoopshop.Helper.initCkeditor('dialogEditCustomerReason');
            },
            buttons: buttons
        });

        $('.order-detail-customer-edit-button').on('click', function () {

            var row = $(this).closest('tr');
            var orderDetailId = row.find('td:nth-child(2)').html();
            var customerId = row.find('td:nth-child(9) span.customer-id-for-dialog').html();
            $('#' + dialogId + ' #dialogOrderDetailEditCustomerOrderDetailId').val(orderDetailId);
            $('#' + dialogId + ' #dialogOrderDetailEditCustomerId').selectpicker('val', '');

            var infoText = foodcoopshop.LocalizedJs.admin.ToWhichMemberShouldTheOrderedProduct0Of1BeAssignedTo.replace(/\{0\}/, '<b>' + row.find('td:nth-child(4) a.name-for-dialog').text() + '</b>');
            infoText = infoText.replace(/\{1\}/, '<b>' + row.find('td:nth-child(9) span.customer-name-for-dialog').html() + '</b>');
            $('#' + dialogId + ' label[for="dialogOrderDetailEditCustomerId"]').html('<span style="font-weight:normal;">' + infoText + '</span>');

            var amount = row.find('.product-amount-for-dialog').html();
            var select = $('#' + dialogId + ' #dialogOrderDetailEditCustomerAmount');
            var selectLabel = $('#' + dialogId + ' label[for="dialogOrderDetailEditCustomerAmount"]');

            select.hide();
            selectLabel.hide();
            select.find('option').remove();
            for (var i = 1; i <= amount; i++) {
                var text = i;
                if (i == amount) {
                    text += ' (' + foodcoopshop.LocalizedJs.admin.all + ')';
                }
                select.append($('<option>', {
                    value: i,
                    text: text
                }));
            }

            if (amount > 1) {
                select.prepend($('<option>', {
                    value: '',
                    text: foodcoopshop.LocalizedJs.admin.PleaseSelect
                }));
                select.show();
                selectLabel.show();
                select.val('');
            }

            dialog.dialog('open');
        });

    },

    initOrderDetailProductAmountEditDialog: function (container) {

        $('#cke_dialogEditAmountReason').val('');

        var dialogId = 'order-detail-product-amount-edit-form';
        var dialogHtml = foodcoopshop.DialogOrderDetail.getHtmlForOrderDetailProductAmountEdit(dialogId);
        $(container).append(dialogHtml);

        var buttons = {};
        buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
        buttons['save'] = {
            text: foodcoopshop.LocalizedJs.helper.save,
            click: function() {
                if ($('#dialogOrderDetailProductAmountAmount').val() == '' || $('#dialogOrderDetailProductAmountOrderDetailId').val() == '') {
                    return false;
                }

                var ckeditorData = CKEDITOR.instances['dialogEditAmountReason'].getData().trim();
                if (ckeditorData == '') {
                    alert(foodcoopshop.LocalizedJs.admin.AdaptAmountReasonIsMandatory);
                    return;
                }

                $('#order-detail-product-amount-edit-form .ajax-loader').show();
                $('.ui-dialog button').attr('disabled', 'disabled');

                foodcoopshop.Helper.ajaxCall(
                    '/admin/order-details/editProductAmount/',
                    {
                        orderDetailId: $('#dialogOrderDetailProductAmountOrderDetailId').val(),
                        productAmount: $('#dialogOrderDetailProductAmountAmount').val(),
                        editAmountReason: ckeditorData
                    },
                    {
                        onOk: function (data) {
                            document.location.reload();
                        },
                        onError: function (data) {
                            dialog.dialog('close');
                            $('#order-detail-product-amount-edit-form .ajax-loader').hide();
                            alert(data.msg);
                        }
                    }
                );
            }
        };

        var dialog = $('#' + dialogId).dialog({

            autoOpen: false,
            height: 600,
            width: 450,
            modal: true,

            close: function () {
                $('#dialogOrderDetailProductAmountAmount').val('');
                $('#dialogOrderDetailProductAmountOrderDetailId').val('');
                foodcoopshop.Helper.destroyCkeditor('dialogEditAmountReason');
            },
            open: function () {
                foodcoopshop.Helper.initCkeditor('dialogEditAmountReason');
            },
            buttons: buttons
        });

        $('.order-detail-product-amount-edit-button').on('click', function () {
            var currentAmount = $(this).closest('tr').find('td:nth-child(3) span.product-amount-for-dialog').html();
            var select = $('#' + dialogId + ' #dialogOrderDetailProductAmountAmount');
            select.find('option').remove();
            for (var i = currentAmount - 1; i >= 1; i--) {
                select.append($('<option>', {
                    value: i,
                    text: i
                }));
            }
            $('#' + dialogId + ' #dialogOrderDetailProductAmountOrderDetailId').val($(this).closest('tr').find('td:nth-child(2)').html());
            var infoTextForEditProductAmount = '<span style="font-weight:normal"><br />' + foodcoopshop.LocalizedJs.admin.DecreaseAmountExplainationText + '<br /><br /></span>';
            infoTextForEditProductAmount += $(this).closest('tr').find('td:nth-child(4) a.name-for-dialog').text();
            infoTextForEditProductAmount += ' <span style="font-weight:normal;">(' + foodcoopshop.LocalizedJs.admin.orderedBy + ' ';
            infoTextForEditProductAmount += $(this).closest('tr').find('td:nth-child(9) span.customer-name-for-dialog').html() + ')<br />' + foodcoopshop.LocalizedJs.admin.NewAmount + ':';
            $('#' + dialogId + ' label[for="dialogOrderDetailProductAmount"]').html(infoTextForEditProductAmount);
            dialog.dialog('open');
        });

    },

    getParentLocation: function() {
        var url = (window.location != window.parent.location)
            ? document.referrer
            : document.location.href;
        return url;
    },

    addParameterToURL : function(url, param) {
        url += (url.split('?')[1] ? '&':'?') + param;
        return url;
    },

    setMenuFixed: function () {
        $(window).scroll(function () {
            $('#menu').css('left', -$(window).scrollLeft());
            $('.filter-container').css('margin-left', -$(window).scrollLeft());
        });
        $('#menu').show();
    },

    adaptContentMargin: function () {
        var marginTop = $('.filter-container').outerHeight();
        $('#content').css('margin-top', marginTop);
        $('#menu').css('min-height', marginTop + $('#content').height() + 4);
    },

    initProductChangeActiveState: function () {

        $('.change-active-state').on('click', function () {

            var productId = $(this).attr('id').split('-');
            productId = productId[productId.length - 1];

            var newState = 1;
            var newStateText = foodcoopshop.LocalizedJs.admin.ActivateProduct;
            var reallyNewStateText = foodcoopshop.LocalizedJs.admin.ReallyActivateProduct0;
            if ($(this).hasClass('set-state-to-inactive')) {
                newState = 0;
                newStateText = foodcoopshop.LocalizedJs.admin.DeactivateProduct;
                reallyNewStateText = foodcoopshop.LocalizedJs.admin.ReallyDeactivateProduct0;
            }

            var buttons = {};
            buttons['no'] = foodcoopshop.Helper.getJqueryUiNoButton();
            buttons['yes'] = {
                text: foodcoopshop.LocalizedJs.helper.yes,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    document.location.href = '/admin/products/changeStatus/' + productId + '/' + newState;
                }
            };

            var dataRow = $('#change-active-state-' + productId).closest('tr');
            $('<div></div>')
                .appendTo('body')
                .html('<p>' + reallyNewStateText.replaceI18n(0, '<b>' + dataRow.find('td.cell-name span.name-for-dialog').html() + '</b>') + '</p><img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />')
                .dialog({
                    modal: true,
                    title: newStateText,
                    autoOpen: true,
                    width: 400,
                    resizable: false,
                    buttons: buttons,
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });
        });
    },

    addDatepickerInDialog : function(inputField) {
        inputField.datepicker({
            beforeShow: function(input, inst) {
                $('.ui-dialog').addClass('has-datepicker');
            },
            onClose: function(input, inst) {
                $('.ui-dialog').removeClass('has-datepicker');
                // if datepicker is closed without selecting a date, it's focused and another click does not trigger to open calendar again
                $(this).off('click').on('click', function() {
                    inputField.datepicker('show');
                });
            }
        });
    },

    initDeletePayment: function () {

        $('.delete-payment-button').on('click',function () {

            var dataRow = $(this).closest('tr');

            var dialogHtml = '<p>' + foodcoopshop.LocalizedJs.admin.ReallyDeletePayment + '<br />';
            dialogHtml += foodcoopshop.LocalizedJs.admin.Date + ': <b>' + dataRow.find('td:nth-child(2)').html() + '</b> <br />';
            dialogHtml += foodcoopshop.LocalizedJs.admin.Amount + ': <b>' + dataRow.find('td:nth-child(4)').html();
            if (dataRow.find('td:nth-child(6)').length > 0) {
                dialogHtml += dataRow.find('td:nth-child(6)').html();
            }
            dialogHtml += '</b>';
            dialogHtml += '</p><img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';

            var buttons = {};
            buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
            buttons['yes'] = {
                text: foodcoopshop.LocalizedJs.helper.yes,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    var paymentId = dataRow.find('td:nth-child(1)').html();
                    foodcoopshop.Helper.ajaxCall(
                        '/admin/payments/changeState/',
                        {
                            paymentId: paymentId
                        },
                        {
                            onOk: function (data) {
                                document.location.reload();
                            },
                            onError: function (data) {
                                alert(data.msg);
                            }
                        }
                    );
                }
            };

            $('<div></div>')
                .appendTo('body')
                .html(dialogHtml)
                .dialog({
                    modal: true,
                    title: foodcoopshop.LocalizedJs.admin.DeletePayment,
                    autoOpen: true,
                    width: 400,
                    resizable: false,
                    buttons: buttons,
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });
        });

    },

    initGenerateMemberCardsOfSelectedCustomersButton : function() {
        var button = $('#generateMemberCardsOfSelectedCustomersButton');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"]').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {
            var customerIds = foodcoopshop.Admin.getSelectedCustomerIds();
            window.open('/admin/customers/generateMemberCards.pdf?customerIds=' + customerIds.join(','));
        });
    },

    initGenerateProductCardsOfSelectedProductsButton : function() {
        var button = $('#generateProductCardsOfSelectedProductsButton');
        foodcoopshop.Helper.disableButton(button);

        $('table.list').find('input.row-marker[type="checkbox"]').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        button.on('click', function () {
            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            var stockProductIds = [];
            for(var i=0; i < productIds.length; i++) {
                var isStockProductElement = $('tr#product-' + productIds[i] + ' td.is-stock-product');
                if (isStockProductElement.length == 1 && isStockProductElement.find('i.fa-check').length == 1) {
                    stockProductIds.push(productIds[i]);
                }
            }
            if (stockProductIds.length == 0) {
                alert(foodcoopshop.LocalizedJs.admin.NoStockProductsSelected);
            } else {
                window.open('/admin/products/generateProductCards.pdf?productIds=' + stockProductIds.join(','));
            }
        });
    },

    initProductDropdown: function (selectedProductId, manufacturerId) {

        manufacturerId = manufacturerId || 0;
        var productDropdown = $('select#productid').closest('.bootstrap-select').find('.dropdown-toggle');

        if (selectedProductId > 0) {
            this.populateDropdownWithProducts(productDropdown, selectedProductId, manufacturerId);
        }

        productDropdown.on('click', function () {
            if ($('select#productid optgroup').length == 0) {
                foodcoopshop.Admin.populateDropdownWithProducts($(this), selectedProductId, manufacturerId);
            }
        });

    },

    populateDropdownWithProducts : function(productDropdown, selectedProductId, manufacturerId) {
        this.populateDropdownWithData(
            '/admin/products/ajaxGetProductsForDropdown/' + '/' + manufacturerId,
            'select#productid',
            productDropdown,
            selectedProductId
        );
    },

    initCustomerDropdown: function (selectedCustomerId, includeManufacturers, includeOfflineCustomers, selector, onChange) {

        selector = selector || 'select#customerid';
        var customerDropdown = $(selector).closest('.bootstrap-select').find('.dropdown-toggle');

        if (selectedCustomerId > 0) {
            this.populateDropdownWithCustomers(customerDropdown, selectedCustomerId, includeManufacturers, includeOfflineCustomers, selector, onChange);
        }

        customerDropdown.on('click', function () {
            if ($(selector + ' optgroup').length == 0) {
                foodcoopshop.Admin.populateDropdownWithCustomers($(this), selectedCustomerId, includeManufacturers, includeOfflineCustomers, selector, onChange);
            }
        });

    },

    populateDropdownWithCustomers : function(customerDropdown, selectedCustomerId, includeManufacturers, includeOfflineCustomers, selector, onChange) {
        this.populateDropdownWithData(
            '/admin/customers/ajaxGetCustomersForDropdown/' + includeManufacturers + '/' + includeOfflineCustomers,
            selector,
            customerDropdown,
            selectedCustomerId,
            onChange
        );
    },

    populateDropdownWithData : function(ajaxMethod, selector, dropdown, selectedIndex, onChange) {
        dropdown.parent().find('div.filter-option-inner-inner').append('<i class="fas fa-circle-notch fa-spin"></i>');
        foodcoopshop.Helper.ajaxCall(
            ajaxMethod, {}, {
                onOk: function (data) {
                    var select = $(selector);
                    select.append(data.dropdownData);
                    select.attr('disabled', false);
                    if (onChange) {
                        select.on('change', function() {
                            onChange();
                        });
                    }
                    if (selectedIndex) {
                        select.selectpicker('val', selectedIndex);
                        if (onChange) {
                            select.trigger('change');
                        }
                    }
                    select.selectpicker('refresh');
                    select.find('i.fa-circle-notch').remove();
                },
                onError: function (data) {
                    console.log(data.msg);
                }
            });
    },

    initSaveCsvUploadPayments : function() {
        $('body.reports.payment form#csv-records button[type="submit"]').on('click', function () {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
            foodcoopshop.Helper.disableButton($(this));
            $(this).closest('form').submit();
        });
    },

    bindSelectCsvRecord : function(selector) {
        $(selector).on('change', function() {
            var row = $(this).closest('tr');
            if (row.hasClass('not-selected')) {
                row.removeClass('not-selected');
            } else {
                row.addClass('not-selected');
            }
        });
    },

    initRemoveValidationErrorAfterSelectChange : function(selector) {
        $(selector).on('change', function() {
            if ($(this).val() > 0) {
                var wrapper = $(this).closest('.select');
                wrapper.removeClass('error');
                wrapper.find('.error-message').remove();
            }
        });
    },

    initCopyPersonalTransactionCodeToClipboardButton: function (successMessage) {
        var clipboard = new ClipboardJS('.btn-clipboard');
        clipboard.on('success', function(e) {
            foodcoopshop.Helper.showSuccessMessage(successMessage);
        });
    },


};

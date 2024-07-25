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
foodcoopshop.Admin = {

    init: function () {
        this.initFilter();
        this.improveTableLayout();
        foodcoopshop.ColorMode.init();
        foodcoopshop.Helper.showContent();
        foodcoopshop.Helper.initMenu();
        foodcoopshop.ModalLogout.init();
        foodcoopshop.ModalSelfServiceConfirmDialog.init();
        this.initRowMarkerAll();
        this.setMenuFixed();
        this.adaptContentMargin();
        this.initStickyTableHeader();
        foodcoopshop.Helper.initScrolltopButton();
    },

    loadGetCreditBalance: function(customerId) {
        var getCreditBalanceTimeouts = [];
        $('#latest-invoices-tooltip-wrapper-' + customerId).hover(function() {
            getCreditBalanceTimeouts[customerId] = setTimeout(function() {
                foodcoopshop.Helper.ajaxCall(
                    '/admin/customers/getCreditBalance/' + customerId,
                    {},
                    {
                        onOk: function (data) {
                            $('#credit-balance-' + customerId).html(data.creditBalance);
                        },
                        onError: function (data) {
                            console.log(data.msg);
                        }
                    }
                );
            }, 300);
        }, function() {
            clearTimeout(getCreditBalanceTimeouts[customerId]);
        }
        );
    },

    hasProductAttributes: function(row) {
        return row.next().hasClass('sub-row');
    },

    initKeepSelectedCheckbox : function() {

        var cookieName = 'SelectedOrderDetailIds';
        var preselectedOrderDetailIds = Cookies.get(cookieName);

        if (preselectedOrderDetailIds) {
            preselectedOrderDetailIds = preselectedOrderDetailIds.split(',');
            if (preselectedOrderDetailIds.length > 0) {
                for (var i in preselectedOrderDetailIds) {
                    $('#row-marker-' + preselectedOrderDetailIds[i]).trigger('click');
                }
            }
        }


        $('.row-marker,#row-marker-all').on('click', function () {

            var selectedOrderDetailIds = foodcoopshop.Admin.getSelectedOrderDetailIds();

            if (preselectedOrderDetailIds) {
                selectedOrderDetailIds = $.merge(preselectedOrderDetailIds, selectedOrderDetailIds);
            }
            selectedOrderDetailIds = foodcoopshop.Helper.unique(selectedOrderDetailIds);

            var unselectedOrderDetailIds = foodcoopshop.Admin.getUnselectedOrderDetailIds();
            for (var index in unselectedOrderDetailIds) {
                var removeId = unselectedOrderDetailIds[index];
                selectedOrderDetailIds = $.grep(selectedOrderDetailIds, function(value) {
                    return value != removeId;
                });
            }

            Cookies.set(cookieName, selectedOrderDetailIds, { expires: 1 });

        });

    },

    initDownloadInvoicesAsZipFile : function() {
        $('.btn-download-invoices-as-zip-file').on('click', function() {
            var url = '/admin/invoices/download-as-zip-file/?dateFrom=' + $('input[name="dateFrom"]').val() + '&dateTo=' + $('input[name="dateTo"]').val() + '&customerId=' + $('#customerid').val();
            window.open(url);
        });
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
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-arrow-circle-right');
            foodcoopshop.Helper.disableButton($(this));
        });
    },

    selectMainMenuAdmin: function (mainMenuTitle, subMenuTitle) {
        foodcoopshop.Helper.selectMainMenu('#menu', mainMenuTitle, subMenuTitle);
    },

    initRowMarkerAll : function () {
        var rowMarkerAll = $('input#row-marker-all').on('click', function () {
            var row;
            if (this.checked) {
                row = $('input.row-marker[type="checkbox"]:not(:checked):not(:disabled)');
                if (row.closest('tr').css('display') != 'none') {
                    row.prop('checked', true);
                    row.closest('tr').addClass('selected');
                }
            } else {
                row = $('input.row-marker[type="checkbox"]:checked');
                row.prop('checked', false);
                row.closest('tr').removeClass('selected');
            }
        });
        return rowMarkerAll;
    },

    getUnselectedOrderDetailIds : function() {
        var orderDetailIds = [];
        $('table.list').find('input.row-marker[type="checkbox"]').not(':checked').each(function () {
            var orderDetailId = $(this).closest('tr').find('td:nth-child(2)').html();
            orderDetailIds.push(orderDetailId);
        });
        return orderDetailIds;
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

    getSelectedIds : function() {
        var ids = [];
        $('table.list').find('input.row-marker[type="checkbox"]:checked').each(function () {
            var id = $(this).closest('tr').find('td:nth-child(2)').html();
            ids.push(id);
        });
        return ids;
    },

    updateObjectSelectionActionButton: function (button) {
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
                var valAsArray = val.toString().split(',');
                $(this).selectpicker().val(valAsArray);
                $(this).selectpicker('destroy');
                $(this).selectpicker('render');
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
            var row = $(this).closest('tr');
            if (row.hasClass('selected')) {
                row.removeClass('selected');
            } else {
                row.addClass('selected');
            }
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
        var newTop = $('.filter-container').height() + $(rowId).closest('table').find('tr.sort').height() + 10;
        $.scrollTo(rowId, 1000, {
            offset: {
                top: newTop * -1,
            }
        });
        $(rowId).css('background-color', 'orange');
        $(rowId).css('color', 'white');
        $(rowId).one('mouseover', function () {
            $(this).removeAttr('style');
        });
    },

    bindToggleQuantityQuantity : function(modalSelector) {
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

    initCopyTableContentToClipboard: function() {

        var clipboard = new ClipboardJS(
            '.btn-clipboard-table',
            {
                target: function(trigger) {
                    return trigger.nextElementSibling;
                }
            }
        );

        clipboard.on('success', function(e) {
            foodcoopshop.Helper.showSuccessMessage(foodcoopshop.LocalizedJs.admin.SuccessfullyCopiedTableContentToClipboard);
            e.clearSelection();
        });

    },

    initCopySelectedEmailsToClipboard: function(object) {

        var btnSelector = '.btn-clipboard';
        var button = $(btnSelector);

        foodcoopshop.Helper.disableButton(button);
        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            foodcoopshop.Admin.updateObjectSelectionActionButton(button);
        });

        var clipboard = new ClipboardJS(
            btnSelector,
            {
                text: function(trigger) {
                    var ids = foodcoopshop.Admin.getSelectedIds();
                    var emails = [];
                    for(var i=0; i < ids.length; i++) {
                        var email = $('tr.data[data-' + object + '-id="'+ids[i]+'"]').find('i.' + object + '-email-button').data('email');
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
        foodcoopshop.Helper.showLoader();
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

    setProductUnitData : function(elementToAttach, productUnitObject) {
        elementToAttach.data('product-unit-object', $.parseJSON(productUnitObject));
    },

    getParentLocation: function() {
        var url = (window.location != window.parent.location)
            ? document.referrer
            : document.location.href;
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

    initStickyTableHeader : function() {
        var newTop = $('.filter-container').height();
        $('table.list th').css('top', newTop + 11);
    },

    setCheckboxClickCallback : function(callback) {
        $('table.list').find('input.row-marker[type="checkbox"],#row-marker-all').on('click', function () {
            callback();
        });
    },

    initGenerateMemberCardsOfSelectedCustomersButton : function() {
        var button = $('#generateMemberCardsOfSelectedCustomersButton');
        foodcoopshop.Helper.disableButton(button);

        foodcoopshop.Admin.setCheckboxClickCallback(() => 
            foodcoopshop.Admin.updateObjectSelectionActionButton(button)
        );

        button.on('click', function () {
            var customerIds = foodcoopshop.Admin.getSelectedIds();
            window.open('/admin/customers/generateMemberCards.pdf?customerIds=' + customerIds.join(','));
        });
    },

    initGenerateProductCardsOfSelectedProductsButton : function() {
        var button = $('#generateProductCardsOfSelectedProductsButton');
        foodcoopshop.Helper.disableButton(button);

        foodcoopshop.Admin.setCheckboxClickCallback(() => 
            foodcoopshop.Admin.updateObjectSelectionActionButton(button)
        );

        button.on('click', function () {
            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            foodcoopshop.Helper.postFormInNewWindow('/admin/products/generateProductCards', {productIds: productIds});
        });
    },

    initExportProductsButton : function() {
        var button = $('#exportProductsButton');
        foodcoopshop.Helper.disableButton(button);

        foodcoopshop.Admin.setCheckboxClickCallback(() => 
            foodcoopshop.Admin.updateObjectSelectionActionButton(button)
        );

        button.on('click', function () {
            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            foodcoopshop.Helper.postFormInNewWindow('/admin/products/export', {productIds: productIds});
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
            '/admin/customers/getCustomersForDropdown/' + includeManufacturers + '/' + includeOfflineCustomers,
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
                        select.selectpicker().val(selectedIndex);
                        if (onChange) {
                            select.trigger('change');
                        }
                    }
                    select.selectpicker('refresh');
                    select.selectpicker('render');
                    select.find('i.fa-circle-notch').remove();
                },
                onError: function (data) {
                    console.log(data.msg);
                }
            });
    },

    initCsvUploadPaymentsCustomerDropdowns: function() {
        let selector = '#csv-records .select-member';
        $(selector).selectpicker({
            liveSearch: true,
            size: 7,
            title: foodcoopshop.LocalizedJs.admin.PleaseSelectAMember,
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

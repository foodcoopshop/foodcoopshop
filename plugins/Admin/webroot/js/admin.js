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

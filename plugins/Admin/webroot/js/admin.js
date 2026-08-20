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
            var url = '/admin/invoices/download-as-zip-file/?dateFrom=' + $('input[name="dateFrom"]').val() + '&dateTo=' + $('input[name="dateTo"]').val() + '&customerIds=' + $('#customerids').val();
            window.open(url);
        });
    },

    disableSelectItems : function (selector, ids) {
        $(selector).find('option').each(function () {
            var currentId = parseInt($(this).val());
            if ($.inArray(currentId, ids) !== -1) {
                $(this).attr('disabled', 'disabled');
            }
        });
        var instance = foodcoopshop.TomSelectCustom.get(selector);
        if (instance) {
            instance.sync();
        }
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

        foodcoopshop.TomSelectCustom.initAll(filterContainer);

        foodcoopshop.TomSelectCustom.setMultipleDropdowns('.filter-container select[multiple="multiple"]');
        filterContainer.find('input:text, input:checkbox, select:not(.do-not-submit)').on('change', function () {
            if ($(this).data('optionsLoading') || $(this).data('suppressFilterChange')) {
                return;
            }
            foodcoopshop.Admin.triggerFilter();
        });

    },

    submitFilterForm: function () {
        $('.filter-container form').submit();
    },

    improveTableLayout: function () {

        $('table.list').each(function () {
            var table = $(this);

            // copy first row with sums
            if (!table.hasClass('no-clone-last-row')) {
                var rows = table.find('> tbody > tr, > tr');
                var lastRow = rows.last().clone();
                rows.first().after(lastRow);
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

            var stretchHeaders = table.find('tr th.stretch');
            var stretchCount = stretchHeaders.length;
            if (stretchCount > 0) {
                var stretchClass = 'stretch stretch-' + stretchCount;
                stretchHeaders.each(function () {
                    var colIndex = $(this).index() + 1;
                    $(this).addClass(stretchClass);
                    table.find('tr.data td:nth-child(' + colIndex + ')').addClass(stretchClass);
                });
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
            foodcoopshop.Helper.showSuccessMessage(__('The_table_content_was_copied_successfully_to_your_clipboard.'));
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
            var response = __('{0}_email_addresses_have_been_copied_successfully_to_your_clipboard.', emailAddressesCount);
            if (emailAddressesCount == 1) {
                response = __('1_email_address_has_been_copied_successfully_to_your_clipboard.');
            }
            foodcoopshop.Helper.showSuccessMessage(response);
        });

    },

    initEmailToAllButton: function () {
        var clipboard = new ClipboardJS('.btn-clipboard');
        clipboard.on('success', function(e) {
            var emailAddressesCount = e.text.split(',').length;
            var response = __('{0}_email_addresses_have_been_copied_successfully_to_your_clipboard.', emailAddressesCount);
            if (emailAddressesCount == 1) {
                response = __('1_email_address_has_been_copied_successfully_to_your_clipboard.');
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
        var formButtons = $('#content .form-buttons');
        formButtons.append($('.filter-container .right > a.submit, .filter-container .right > a.cancel').clone(true)); // true clones events
        this.initDeleteEntityButton(form, formButtons);

        // submit form on enter in text fields
        form.find('input[type=text], input[type=number], input[type=password], input[type="tel"]').keypress(function (e) {
            if (e.which == 13) {
                $(this).blur();
                $('.filter-container .right a.submit').trigger('click');
            }
        });

        form.find('select').not('.no-tom-select').each(function () {
            foodcoopshop.TomSelectCustom.init(this);
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

    initDeleteEntityButton: function (form, formButtons) {

        var deleteEntityInput = form.find('input.js-delete-entity-input');
        if (deleteEntityInput.length === 0) {
            return;
        }

        var formButtonsLeft = $('#content .form-buttons-left');
        if (formButtonsLeft.length === 0) {
            formButtonsLeft = $('<div class="form-buttons-left"></div>');
            formButtonsLeft.insertAfter(form);
        }

        var buttonClass = deleteEntityInput.data('deleteButtonClass') || 'btn-danger';
        var buttonLabel = deleteEntityInput.data('deleteLabel') || __('Delete');
        var confirmMessage = deleteEntityInput.data('deleteConfirm') || __('Are you sure?');
        var buttonIcon = deleteEntityInput.data('deleteIcon') || 'fa-trash-alt';
        var isDisabled = !!deleteEntityInput.data('deleteDisabled');
        var disabledTitle = deleteEntityInput.data('deleteDisabledTitle') || '';

        var deleteButtonClass = 'btn ' + buttonClass + ' delete-entity';

        if (isDisabled) {
            if (disabledTitle !== '') {
                var infoText = $('<span class="delete-entity-info"></span>').text(disabledTitle);
                formButtonsLeft.append(infoText);
                formButtonsLeft.addClass('has-delete-info');
            }
            return;
        }

        var deleteButton = $('<a href="javascript:void(0);" class="' + deleteButtonClass + '"><i class="fa-fw fas ' + buttonIcon + '"></i> ' + buttonLabel + '</a>');
        formButtonsLeft.append(deleteButton);

        deleteButton.on('click', function () {
            if (!confirm(confirmMessage)) {
                return;
            }
            deleteEntityInput.val(1);
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), buttonIcon);
            form.submit();
        });

    },

    initHeaderPromoVisibility: function(imageButtonSelector, deleteImageSelector, sectionSelector) {

        var imageButton = imageButtonSelector || 'body.pages a.add-image-button';
        var headerPromoSection = sectionSelector || '.header-promo-section';

        var toggleHeaderPromoSection = function () {
            var hasImage = $(imageButton).hasClass('uploaded');
            $(headerPromoSection).toggleClass('hide', !hasImage);
        };

        toggleHeaderPromoSection();
        $(document).on('fcs:page-header-image-updated', toggleHeaderPromoSection);

    },

    initHomeBlocksEditor: function () {

        var blocksContainer = $('.home-blocks-list');
        var blockTemplate = $('.home-block-row.template');
        var emptyState = $('.home-blocks-empty-state');
        var uploadFormsContainer = $('#home-block-upload-forms');
        var uploadFormTemplate = $('form#mini-upload-form-image-__INDEX__');
        var blockIndex = $('.home-block-row:not(.template)').length;

        var toggleEmptyState = function () {
            if (blocksContainer.find('.home-block-row:not(.template)').length === 0) {
                emptyState.removeClass('hide');
            } else {
                emptyState.addClass('hide');
            }
        };

        var scrollToRow = function (row) {
            var filterContainerHeight = $('.filter-container:visible').outerHeight() || 0;
            var targetTop = Math.max(0, row.offset().top - filterContainerHeight - 12);
            $('html, body').stop(true).animate({scrollTop: targetTop}, 250);
        };

        var initBlockRow = function (row) {
            var objectId = row.data('objectId');
            foodcoopshop.Editor.initBigReduced('home-block-content-' + objectId);
            foodcoopshop.Upload.initImageUpload('body.pages .block-image-upload-button[data-object-id="' + objectId + '"]', foodcoopshop.Upload.saveBlockTmpImageInForm);
        };

        var addUploadFormForBlock = function (objectId) {
            var uploadForm = uploadFormTemplate.clone();
            uploadForm.removeClass('hide');
            uploadForm.attr('id', 'mini-upload-form-image-' + objectId);
            uploadForm.attr('data-object-id', objectId);
            uploadForm.find('.heading').text(__('Upload_new_image'));
            uploadForm.find('.drop img').remove();
            uploadForm.find('a.uploadedFile').remove();
            uploadForm.find('input[type="file"]').val('');
            uploadFormsContainer.append(uploadForm);
        };

        $('.home-block-row:not(.template)').each(function () {
            initBlockRow($(this));
        });
        toggleEmptyState();

        $(document).on('click', '.add-home-block-button', function () {
            var objectId = 'new-block-' + blockIndex + '-' + Date.now();
            blockIndex++;
            var row = blockTemplate.clone();
            row.removeClass('template hide').show();
            row.attr('data-object-id', objectId);
            row.html(row.html().replace(/__INDEX__/g, objectId));
            blocksContainer.append(row);
            addUploadFormForBlock(objectId);
            initBlockRow(row);
            toggleEmptyState();
            scrollToRow(row);
        });

        $(document).on('click', '.remove-home-block-button', function () {
            if (!confirm(__('Really delete block? Don\'t forget to click save afterwards.'))) {
                return;
            }

            var row = $(this).closest('.home-block-row');
            var objectId = row.data('objectId');
            $('form#mini-upload-form-image-' + objectId).remove();
            row.remove();
            toggleEmptyState();
        });

        $(document).on('click', '.home-block-row-actions .submit', function (e) {
            e.preventDefault();
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
            $('#pageEditForm').submit();
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
        $('#content').css('min-height', 'calc(100vh - ' + marginTop + 'px)');
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

    initExportAllProductsButton : function() {
        var button = $('#exportAllProductsButton');
        foodcoopshop.Helper.disableButton(button);

        foodcoopshop.Admin.setCheckboxClickCallback(() => 
            foodcoopshop.Admin.updateObjectSelectionActionButton(button)
        );

        button.on('click', function () {
            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            foodcoopshop.Helper.postFormInNewWindow('/admin/products/export', {
                productIds: productIds,
                onlyStockProducts: 0,
            });
        });
    },

    initExportStockProductsButton : function() {
        var button = $('#exportStockProductsButton');
        foodcoopshop.Helper.disableButton(button);

        foodcoopshop.Admin.setCheckboxClickCallback(() => 
            foodcoopshop.Admin.updateObjectSelectionActionButton(button)
        );

        button.on('click', function () {
            var productIds = foodcoopshop.Admin.getSelectedProductIds();
            foodcoopshop.Helper.postFormInNewWindow('/admin/products/export', {
                productIds: productIds,
                onlyStockProducts: 1,
            });
        });
    },

    /**
     * Product/customer dropdowns are initialized empty and their data is only fetched
     * via AJAX the first time the dropdown is opened (or immediately, if a value should
     * be preselected). The fetch hook is attached via the Tom Select "dropdown_open" event.
     */
    initProductDropdown: function (selectedProductId, manufacturerId) {

        manufacturerId = manufacturerId || 0;
        var selector = 'select#productid';

        foodcoopshop.TomSelectCustom.init(selector, {}, {
            dropdown_open: function () {
                if (!$(selector).data('optionsLoaded') && !$(selector).data('optionsLoading')) {
                    foodcoopshop.Admin.populateDropdownWithProducts(selector, selectedProductId, manufacturerId);
                }
            }
        });

        if (selectedProductId > 0) {
            this.populateDropdownWithProducts(selector, selectedProductId, manufacturerId);
        }

    },

    populateDropdownWithProducts : function(selector, selectedProductId, manufacturerId) {
        this.populateDropdownWithData(
            '/admin/products/ajaxGetProductsForDropdown/' + '/' + manufacturerId,
            selector,
            selectedProductId
        );
    },

    initCustomerDropdown: function (selectedCustomerId, includeManufacturers, includeOfflineCustomers, selector, onChange, settings) {

        selector = selector || 'select#customerid';

        foodcoopshop.TomSelectCustom.init(selector, settings || {}, {
            dropdown_open: function () {
                if (!$(selector).data('optionsLoaded') && !$(selector).data('optionsLoading')) {
                    foodcoopshop.Admin.populateDropdownWithCustomers([selectedCustomerId], includeManufacturers, includeOfflineCustomers, selector, onChange);
                }
            }
        });

        if (selectedCustomerId > 0) {
            this.populateDropdownWithCustomers([selectedCustomerId], includeManufacturers, includeOfflineCustomers, selector, onChange);
        }

    },

    initCustomerMultiDropdown: function (selectedCustomerIds, includeManufacturers, includeOfflineCustomers, selector, onChange) {

        selector = selector || 'select#customerids';

        foodcoopshop.TomSelectCustom.init(selector, {}, {
            dropdown_open: function () {
                if (!$(selector).data('optionsLoaded') && !$(selector).data('optionsLoading')) {
                    foodcoopshop.Admin.populateDropdownWithCustomers(selectedCustomerIds, includeManufacturers, includeOfflineCustomers, selector, onChange);
                }
            }
        });

        if (selectedCustomerIds.length > 0) {
            this.populateDropdownWithCustomers(selectedCustomerIds, includeManufacturers, includeOfflineCustomers, selector, onChange);
        }

    },

    populateDropdownWithCustomers : function(selectedCustomerIds, includeManufacturers, includeOfflineCustomers, selector, onChange) {
        this.populateDropdownWithData(
            '/admin/customers/getCustomersForDropdown/' + includeManufacturers + '/' + includeOfflineCustomers,
            selector,
            selectedCustomerIds,
            onChange
        );
    },

    populateDropdownWithData : function(ajaxMethod, selector, selectedValue, onChange) {
        var instance = foodcoopshop.TomSelectCustom.get(selector);
        // fetch the options via AJAX only once per page load; every later call (e.g. re-opening
        // the dropdown) reuses the already-fetched data instead of triggering another request
        if (!instance || $(selector).data('optionsLoaded') || $(selector).data('optionsLoading')) {
            return;
        }
        $(selector).data('optionsLoading', true);
        $(selector).data('suppressFilterChange', true);
        $(selector).next('.ts-wrapper').find('.ts-control').append('<i class="fas fa-circle-notch fa-spin"></i>');
        foodcoopshop.Helper.ajaxCall(
            ajaxMethod, {}, {
                onOk: function (data) {
                    // setData and setValue emit native change events; keep suppress flags until
                    // after those synchronous updates so filter forms are not submitted
                    foodcoopshop.TomSelectCustom.setData(instance, data.dropdownData);
                    instance.enable();
                    $(selector).data('optionsLoaded', true);
                    if (onChange) {
                        $(selector).off('change.populateDropdownWithData').on('change.populateDropdownWithData', function() {
                            onChange();
                        });
                    }
                    // only preselect (and notify onChange) if a real value was passed; merely
                    // opening a lazy-loaded dropdown without any preselection must not fire a change
                    var selectedValueAsArray = (Array.isArray(selectedValue) ? selectedValue : [selectedValue]).filter(function (value) {
                        return value !== null && value !== undefined && value !== '' && parseInt(value, 10) > 0;
                    });
                    if (selectedValueAsArray.length > 0) {
                        // second arg true: silent, do not trigger change; page already
                        // reflects the preselected filter from the URL
                        instance.setValue(selectedValueAsArray.map(String), true);
                        if (onChange) {
                            onChange();
                        }
                    }
                    $(selector).next('.ts-wrapper').find('i.fa-circle-notch').remove();
                    $(selector).data('optionsLoading', false);
                    $(selector).data('suppressFilterChange', false);
                },
                onError: function (data) {
                    $(selector).data('optionsLoading', false);
                    $(selector).data('suppressFilterChange', false);
                    console.log(data.msg);
                }
            });
    },

    initCsvUploadPaymentsCustomerDropdowns: function() {
        let selector = '#csv-records .select-member';
        $(selector).each(function () {
            foodcoopshop.TomSelectCustom.init(this, {
                placeholder: __('Please_select_a_member.')
            });
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

    initFontPreview: function () {
        var fontConfigSelect = $('#font-config-select');
        if (fontConfigSelect.length === 0) {
            return;
        }

        fontConfigSelect.on('change', function () {
            var selectedFont = $(this).val();
            var fontPreview = $('#font-preview');
            if (fontPreview.length === 0) {
                return;
            }

            // Remove all font classes
            fontPreview.removeClass(function (index, className) {
                return (className.match(/\bfont-\S+/g) || []).join(' ');
            });

            // Add the new font class
            fontPreview.addClass('font-' + selectedFont);
        });
    },


};

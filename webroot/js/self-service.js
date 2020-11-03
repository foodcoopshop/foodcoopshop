/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.SelfService = {

    autoLogoutTimer : 180,
    currentLogoutTimer : 0,

    init : function() {
        foodcoopshop.ModalLogout.init(document.location.href);
        this.initWindowResize();
        if (!foodcoopshop.Helper.isMobile()) {
            this.initAutoLogout();
        }
        this.initSearchForm();
        this.initDepositPayment();
    },

    initMobileBarcodeScanningWithCamera : function(afterElementForLoader, afterElementForCamera, callback) {

        if (!this.isMobileBarcodeScanningSupported) {
            alert('mobile_barcode_scanning_not_supported');
            return;
        }

        if ($('#camera').length > 0) {
            Quagga.stop();
            $('#camera').remove();
            return;
        }

        $(afterElementForCamera).after($('<div />').attr('id', 'camera'));
        foodcoopshop.SelfService.hideLoader();
        foodcoopshop.SelfService.showLoader(afterElementForLoader);

        Quagga.init({
            inputStream : {
                name : 'Live',
                type : 'LiveStream',
                target: document.querySelector('#camera'),
            },
            numOfWorkers: navigator.hardwareConcurrency,
            decoder : {
                readers : ['code_39_reader']
            },
        }, function(err) {
            if (err) {
                console.log(err);
                return;
            }

            Quagga.start();

            $('#camera').animate({
                height: 'toggle'
            }, 150);
            foodcoopshop.SelfService.hideLoader();

        });
        Quagga.offDetected();
        Quagga.onDetected(function(result) {
            Quagga.stop();
            foodcoopshop.SelfService.hideLoader();
            foodcoopshop.SelfService.showLoader(afterElementForLoader);
            callback(result);
        });

    },

    mobileScannerCallbackForLogin : function(result) {
        var loginForm = $('#LoginForm');
        loginForm.find('#barcode').val(result.codeResult.code);
        foodcoopshop.SelfService.submitForm(loginForm, 'fa-sign-in-alt');
    },

    mobileScannerCallbackForProducts : function(result) {
        var redirectUrl = '/' + foodcoopshop.LocalizedJs.helper.routeSelfService + '?keyword=' + result.codeResult.code;
        document.location.href = redirectUrl;
    },

    showLoader : function(afterElementForLoader) {
        $('#responsive-header ' + afterElementForLoader).after($('<i />').addClass('fa fa-circle-notch fa-spin fa-2x'));
    },

    hideLoader: function() {
        $('#responsive-header i.fa-circle-notch').remove();
    },

    isMobileBarcodeScanningSupported : function() {
        return navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function';
    },

    initLoginForm : function() {

        var barcodeInputField = $('#barcode');
        barcodeInputField.on('keyup focus', function (e) {
            $(this).prop('type', 'password'); // to avoid autocomplete
        });

        var loginForm = $('#LoginForm');
        var formIsSubmitted = false;
        loginForm.on('submit', function(e) {
            if (formIsSubmitted) {
                return false;
            }
            formIsSubmitted = true;
        });

        barcodeInputField.on('keypress', foodcoopshop.Helper.debounce(
            function() {
                foodcoopshop.SelfService.submitForm(loginForm, 'fa-sign-in-alt');
            }, 1000)
        );

        barcodeInputField.focus();

        var cameraButton = $('<a/>').
            addClass('btn').
            addClass('btn-camera').
            addClass('btn-success').
            attr('href', 'javascript:void(0);').
            html('<i class="fas fa-camera fa-2x"></i>').
            on('click', function() {
                foodcoopshop.SelfService.initMobileBarcodeScanningWithCamera('.btn-camera', '#login-form h1', foodcoopshop.SelfService.mobileScannerCallbackForLogin);
            });
        $('#responsive-header .sb-toggle-left').after(cameraButton);

    },

    initDepositPayment : function() {
        foodcoopshop.ModalPaymentAdd.initDepositSingle('.btn-add-deposit', $('#add-payment-deposit-form'));
    },

    initSearchForm : function() {

        var searchForm = $('#product-search');

        var formIsSubmitted = false;
        searchForm.on('submit', function(e) {
            if (formIsSubmitted) {
                return false;
            }
            formIsSubmitted = true;
        });

        if (!foodcoopshop.Helper.isMobile()) {
            foodcoopshop.Helper.initBootstrapSelect(searchForm);
        }
        searchForm.find('select, input[type="text"]').on('change', function() {
            foodcoopshop.SelfService.submitForm(searchForm, 'fa-search');
        });
        searchForm.find('select, input[type="text"]').on('keypress', foodcoopshop.Helper.debounce(
            function() {
                foodcoopshop.SelfService.submitForm(searchForm, 'fa-search');
            }, 1000)
        );

        searchForm.find('input[type="text"]').focus();
    },

    submitForm : function(searchForm, icon) {
        var submitButton = searchForm.find('.btn[type="submit"]');
        foodcoopshop.Helper.addSpinnerToButton(submitButton, icon);
        foodcoopshop.Helper.disableButton(submitButton);
        searchForm.submit();
    },

    initHighlightedProductIdForMobileBarcodeScanning: function(productId) {
        $('#products').show();
        $('.product-wrapper').hide();
        var rowId = '#product-wrapper-' + productId;
        $(rowId).show();
        this.initHighlightedProductId(productId);
    },

    initHighlightedProductId: function(productId) {
        var rowId = '#product-wrapper-' + productId;
        $.scrollTo(rowId, 1000, {
            offset: {
                top: -100
            }
        });
        $(rowId).css('background-color', '#f3515c');
        $(rowId).css('color', 'white');
        $(rowId).one('mouseover', function () {
            $(this).removeAttr('style');
        });
        $(rowId).find('.quantity-in-units-input-field-wrapper input').focus();
    },

    initCartErrors: function (cartErrors) {
        cartErrors = $.parseJSON(cartErrors);
        for (var key in cartErrors) {
            var container;
            var errorMessageString = '<ul class="error-message ' + key + '"><li>' + cartErrors[key].join('</li><li>') + '</li></ul>';
            if (key == 'global') {
                container = $('#SelfServiceForm');
                container.addClass('error');
                container.prepend(errorMessageString);
            } else {
                container = $('#cart .product.' + key);
                container.addClass('error');
                container.after(errorMessageString);
            }
        }
    },

    initWindowResize: function () {
        $(window).on('resize', function () {
            foodcoopshop.SelfService.onWindowResize();
        });
        foodcoopshop.SelfService.onWindowResize();
    },

    setFocusToSearchInputField : function() {
        $('#product-search input[name="keyword"]').focus();
    },

    onWindowResize : function() {
        $('.right-box').css('max-height', parseInt($(window).height()));
    },

    initAutoLogout : function() {

        this.resetTimer();
        this.renderTimer();

        $(document).idle({
            startAtIdle : true,
            onActive: function(){
                foodcoopshop.SelfService.resetTimer();
                foodcoopshop.SelfService.renderTimer();
            },
            onIdle: function() {
                foodcoopshop.SelfService.currentLogoutTimer--;
                foodcoopshop.SelfService.renderTimer();
                if (foodcoopshop.SelfService.currentLogoutTimer == 0) {
                    document.location.href = '/' + foodcoopshop.LocalizedJs.helper.routeLogout + '?redirect=' + document.location.href;
                }
            },
            recurIdleCall : true,
            idle: 1000
        });

    },

    resetTimer : function() {
        this.currentLogoutTimer = this.autoLogoutTimer;
    },

    renderTimer : function() {
        $('.auto-logout-timer').html(this.currentLogoutTimer);
    }

};

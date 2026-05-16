/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.SelfService = {

    autoLogoutTimer : 180,
    currentLogoutTimer : 0,

    init : function() {
        foodcoopshop.ModalLogout.init(document.location.href);
        foodcoopshop.ColorMode.init();
        this.initWindowResize();
        this.initSearchForm();
        this.bindQuantityInUnitsInputFields();
        this.initDepositPayment();
        this.initGlobalBarcodeScannerListener();
    },

    injectLoginButtons : function(buttonHtml) {
        $('.self-service-login-button-wrapper').append(atob(buttonHtml));
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
                readers : [
                    'code_39_reader',
                    'ean_reader',
                ],
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
        var redirectUrl = '/' + __('route_self_service') + '?keyword=' + result.codeResult.code;
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

        var searchForms = $('.product-search-form-wrapper form');

        searchForms.each(function() {

            var searchForm = $(this);
            searchForm.on('submit', function(e) {
                e.preventDefault();
                foodcoopshop.SelfService.ajaxScan();
                return false;
            });

            if (!foodcoopshop.Helper.isMobile()) {
                foodcoopshop.Helper.initBootstrapSelect(searchForm);
            }
            searchForm.find('select, input[type="text"]').on('change', function() {
                foodcoopshop.SelfService.submitForm(searchForm, 'fa-search');
            });

            var inputField = searchForm.find('input[type="text"]');
            if (inputField.length > 0) {
                var length = inputField.val().length;
                inputField[0].setSelectionRange(length, length);
                inputField.focus();
            }

        });

    },

    submitForm : function(searchForm, icon) {
        var submitButton = searchForm.find('.btn[type="submit"]');
        foodcoopshop.Helper.addSpinnerToButton(submitButton, icon);
        foodcoopshop.Helper.disableButton(submitButton);
        searchForm.submit();
    },

    ajaxScan: function() {
        var searchForm = $('form#product-search-1');
        var searchInput = searchForm.find('input[name="keyword"]');
        var keyword = searchInput.val();
        
        var requestUrl = searchForm.attr('action');
        if (!requestUrl) {
            requestUrl = '/' + __('route_self_service');
        }
        requestUrl += '?' + searchForm.serialize();
        
        if (keyword != '') {
            searchInput.val('');
        }

        foodcoopshop.Cart.queue.push(function() {
            foodcoopshop.Helper.removeFlashMessage();
            var submitButton = searchForm.find('.btn[type="submit"]');
            foodcoopshop.Helper.addSpinnerToButton(submitButton, 'fa-search');
            foodcoopshop.Helper.disableButton(submitButton);
            
            $.ajax({
                url: requestUrl,
                type: 'GET',
                success: function(response) {
                    try {
                        foodcoopshop.Helper.removeSpinnerFromButton(submitButton, 'fa-search');
                        foodcoopshop.Helper.enableButton(submitButton);
                        
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(response, 'text/html');
                        
                        var flashMessageSuccess = $(doc).find('#flashMessage.success');
                        if (flashMessageSuccess.length > 0) {
                            $('.right-box').replaceWith($(doc).find('.right-box')[0].outerHTML);
                            
                            var cartScriptMatch = response.match(/foodcoopshop\.Cart\.initCartProducts\('(?:[^'\\]|\\.)*'\);/);
                            if (cartScriptMatch) {
                                eval(cartScriptMatch[0]);
                            }
                            
                            foodcoopshop.SelfService.bindQuantityInUnitsInputFields();
                            
                            foodcoopshop.Cart.isProcessing = false;
                            foodcoopshop.Cart.processQueue();
                            foodcoopshop.SelfService.setFocusToSearchInputField();
                        } else if ($(doc).find('#flashMessage.error').length > 0) {
                            foodcoopshop.Helper.showErrorMessage($(doc).find('#flashMessage.error').html());
                            foodcoopshop.SelfService.playErrorSound();
                            foodcoopshop.Cart.isProcessing = false;
                            foodcoopshop.Cart.processQueue();
                            foodcoopshop.SelfService.setFocusToSearchInputField();
                        } else {
                            if ($(doc).find('.product-wrapper').length === 0 && /^\d{4,}$/.test(keyword)) {
                                $('.right-box').replaceWith($(doc).find('.right-box')[0].outerHTML);
                                foodcoopshop.SelfService.bindQuantityInUnitsInputFields();
                                foodcoopshop.Helper.showErrorMessage('Barcode ' + keyword + ' nicht gefunden.');
                                foodcoopshop.SelfService.playErrorSound();
                                foodcoopshop.Cart.isProcessing = false;
                                foodcoopshop.Cart.processQueue();
                                foodcoopshop.SelfService.setFocusToSearchInputField();
                            } else {
                                document.location.href = requestUrl;
                            }
                        }
                    } catch (e) {
                        console.error('AJAX Success processing error: ', e);
                        foodcoopshop.Cart.isProcessing = false;
                        foodcoopshop.Cart.processQueue();
                        foodcoopshop.SelfService.setFocusToSearchInputField();
                    }
                },
                error: function() {
                    foodcoopshop.Helper.removeSpinnerFromButton(submitButton, 'fa-search');
                    foodcoopshop.Helper.enableButton(submitButton);
                    foodcoopshop.Helper.showErrorMessage(__('An_error_occurred'));
                    foodcoopshop.SelfService.playErrorSound();
                    foodcoopshop.Cart.isProcessing = false;
                    foodcoopshop.Cart.processQueue();
                    foodcoopshop.SelfService.setFocusToSearchInputField();
                }
            });
        });
        foodcoopshop.Cart.processQueue();
    },

    playErrorSound: function() {
        try {
            var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var oscillator = audioCtx.createOscillator();
            var gainNode = audioCtx.createGain();

            oscillator.type = 'sawtooth';
            oscillator.frequency.setValueAtTime(200, audioCtx.currentTime); // Low buzz
            oscillator.frequency.setValueAtTime(150, audioCtx.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);

            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.3);
        } catch (e) {
            console.warn('Web Audio API not supported', e);
        }
    },

    barcodeBuffer: '',
    barcodeTimer: null,

    initGlobalBarcodeScannerListener: function() {
        $(document).on('keypress', function(e) {
            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                foodcoopshop.SelfService.barcodeBuffer += e.key;
                if (foodcoopshop.SelfService.barcodeTimer) clearTimeout(foodcoopshop.SelfService.barcodeTimer);
                foodcoopshop.SelfService.barcodeTimer = setTimeout(function() {
                    foodcoopshop.SelfService.barcodeBuffer = '';
                }, 300);
            } else if (e.key === 'Enter') {
                if (foodcoopshop.SelfService.barcodeBuffer.length >= 4) {
                    var code = foodcoopshop.SelfService.barcodeBuffer;
                    foodcoopshop.SelfService.barcodeBuffer = '';
                    e.preventDefault();
                    
                    var searchInput = $('form#product-search-1 input[name="keyword"]');
                    if (searchInput.length) {
                        searchInput.val(code);
                        searchInput.closest('form').submit();
                    }
                } else {
                    foodcoopshop.SelfService.barcodeBuffer = '';
                }
            }
        });
    },

    initHighlightedProductIdForMobileBarcodeScanning: function(productId) {
        $('#products').show();
        $('.pw').hide();
        var rowId = '#pw-' + productId;
        $(rowId).show();
        this.initHighlightedProductId(productId);
    },

    initHighlightedProductId: function(productId) {
        var rowId = '#pw-' + productId;
        $.scrollTo(rowId, 1000, {
            offset: {
                top: -100
            }
        });
        $(rowId).css('background-color', '#f3515c');
        $(rowId).css('color', 'white');
        $(rowId).find('.line *').css('color', 'white');
        $(rowId).one('mouseover', function () {
            $(this).find('.line *').removeAttr('style');
            $(this).removeAttr('style');
        });
        $(rowId).find('.quantity-in-units-input-field-wrapper input').focus();
    },

    bindQuantityInUnitsInputFields: function(){
        $('.quantity-in-units-input-field-wrapper input').on('keypress', function(e) {
            if (e.which === 13) {
                if (foodcoopshop.SelfService.barcodeBuffer && foodcoopshop.SelfService.barcodeBuffer.length >= 4) {
                    $(this).val('');
                    return;
                }
                $(this).closest('.ew').find('.btn-cart').trigger('click');
                $(this).val('');
            }
        });
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
        var inputField = $('.product-search-form-wrapper input[name="keyword"]');
        inputField.focus();
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
                    document.location.href = '/' + __('route_sign_out') + '?redirect=' + document.location.href;
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
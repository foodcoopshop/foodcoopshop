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

String.prototype.replaceI18n = function(object, replace) {
    var regExp = new RegExp('\\{' + object + '\\}', 'g');
    return this.replace(regExp, replace);
};

foodcoopshop.Helper = {

    init: function () {
        this.initMenu();
        this.initLogoutButton();
        this.changeOutgoingLinksTargetToBlank();
        if (!this.isMobile()) {
            this.initRight();
            this.initScrolltopButton();
            this.showContent();
        }
    },
    
    getUniqueHtmlValueOfDomElements: function(domElements, defaultValue) {
        var values = this.unique(
            $.map($(domElements),
                function(element) {
                    return $(element).html();
            })
        );
        if (values.length > 1) {
            values = defaultValue;
        }
        return values;
    },
    
    /**
     * $.unique does not work with strings
     */
    unique : function(array) {
        return $.grep(array, function(el, index) {
            return index === $.inArray(el, array);
        });
    },

    getJqueryUiNoButton : function() {
        return this.getJqueryUiCloseDialogButton(foodcoopshop.LocalizedJs.helper.no);
    },

    getJqueryUiCancelButton : function() {
        return this.getJqueryUiCloseDialogButton(foodcoopshop.LocalizedJs.helper.cancel);
    },

    getJqueryUiCloseDialogButton : function(label) {
        return {
            text: label,
            click: function() {
                $(this).dialog('close');
            }
        };
    },

    initBlogPostCarousel: function () {

        var container = $('.blog-wrapper');
        container.addClass('owl-carousel');

        container.owlCarousel({
            responsiveClass: true,
            nav: true,
            navText: [
                '<i class="far fa-arrow-alt-circle-left fa-3x"></i>',
                '<i class="far fa-arrow-alt-circle-right fa-3x"></i>'
            ],
            responsive: {
                320: {
                    items: 2,
                    center: false
                },
                480: {
                    items: 3,
                    center: false
                },
                640: {
                    items: 4,
                    center: false
                },
                768: {
                    items: 3
                }
            }
        });
    },

    isMobile: function () {
        var isMobile = false;
        if ($('div.is-mobile-detector').length == 1) {
            isMobile = true;
        }
        return isMobile;
    },
    
    isEdge : function() {
        var isEdge = false;
        if (navigator.userAgent.indexOf('Edge') >= 0) {
            isEdge = true;
        }
        return isEdge;
    },
    
    /**
     * as long as edge is the only major browser that does not support commas in input fields
     * with type="number", manually change the type to text
     */
    changeInputNumberToTextForEdge : function() {
        if (this.isEdge()) {
            var numberInputs = $('input[type="number"]');
            numberInputs.each(function() {
                $(this).get(0).type = 'text';
            });
        }        
    },

    initLoginForm: function () {
        $('#LoginForm button[type="submit"]').on('click', function () {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-sign-in-alt');
            foodcoopshop.Helper.disableButton($(this));
            $(this).closest('form').submit();
        });
    },

    initSearchForm: function () {
        $('#product-search button[type="submit"]').on('click', function () {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-search');
            foodcoopshop.Helper.disableButton($(this));
            $(this).closest('form').submit();
        });
    },

    initRegistrationForm: function (isPost) {

        $('#RegistrationForm .btn-success').on('click', function () {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-user');
            foodcoopshop.Helper.disableButton($(this));
            $(this).closest('form').submit();
        });

        if (isPost) {
            $('#RegistrationForm .detail-form').show();
        } else {
            $('#RegistrationForm #RegistraionFormEmail').on('focus', function () {
                $('#RegistrationForm .detail-form').animate({
                    height: 'toggle'
                }, 500);
                $(this).unbind('focus');
            });
        }

        this.updateAntiSpamField($('#RegistrationForm'));

    },

    /**
     * http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches?lq=1
     */
    updateAntiSpamField: function (form) {

        if (document.getElementById('antiSpam')) {
            var a = document.getElementById('antiSpam');
            if (isNaN(a.value) == true) {
                a.value = 0;
            } else {
                a.value = parseInt(a.value) + 1;
            }
        }

        setTimeout(function () {
            foodcoopshop.Helper.updateAntiSpamField(form);
        }, 1000);

    },

    changeOutgoingLinksTargetToBlank: function () {
        $('a[href^="http://"], a[href^="https://"]:not([href^="' + window.location.host + '"])').attr('target', '_blank');
    },

    inIframe: function () {
        try {
            return window.self !== window.top;
        } catch (e) {
            return true;
        }
    },

    selectMainMenu : function (menu, mainMenuTitle, subMenuTitle) {

        $(menu + ' > li > a').filter(function () {
            return $(this).html().substr($(this).html().length - mainMenuTitle.length) === mainMenuTitle;
        }).addClass('active');

        if (subMenuTitle) {
            $(menu + ' ul > li > a').filter(function () {
                return $(this).html().substr($(this).html().length - subMenuTitle.length) === subMenuTitle;
            }).addClass('active');
        }

    },

    selectMainMenuFrontend: function (pageTitle) {
        this.selectMainMenu('#main-menu', pageTitle);
    },

    initSlider: function () {
        $('#slider').cycle({
            fx: 'scrollHorz',
            prev: '#slider-wrapper .prev',
            next: '#slider-wrapper .next',
            timeout: 6000,
            speed: 3000,
            swipe: true,
            pause: 0
        });
    },

    initScrolltopButton: function () {

        $('#scroll-to-top').hide();

        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('#scroll-to-top').fadeIn();
            } else {
                $('#scroll-to-top').fadeOut();
            }
        });
        
        $('#scroll-to-top a').on('click', function () {
            $('body,html').animate({
                scrollTop: 0
            }, 400);
            return false;
        });

        $('#scroll-to-top a').mouseenter(function () {
            $(this).children('i').removeClass('fas');
            $(this).children('i').addClass('far');
        }).mouseleave(function () {
            $(this).children('i').removeClass('far');
            $(this).children('i').addClass('fas');
        });
        
        $('#scroll-to-top a').on('click', function () {
            $('body,html').animate({
                scrollTop: 0
            }, 400);
            return false;
        });

    },

    initRight: function () {

        $(window).scroll(function () {
            foodcoopshop.Helper.onWindowScroll();
        });

        foodcoopshop.Helper.onWindowScroll();

    },

    onWindowScroll: function () {

        // keep right column on its place
        var newLeft = $(window).scrollLeft() * -1 + parseInt($('#content').width()) + parseInt($('#content').css('padding-left')) + 6;
        $('.inner-right').css('left', newLeft);

        // adapt height of cart
        var difference = 150;
        var loadLastOrderDetailsDropdown = $('#cart #load-last-order-details');
        if (loadLastOrderDetailsDropdown.length > 0) {
            difference += loadLastOrderDetailsDropdown.closest('.input').height();
        }
        $('#cart p.products').css('max-height', parseInt($(window).height()) - difference);

    },

    initMenu: function () {

        // select and show submenu of vertical menu, recursive!
        var selectedVerticalSubMenu = $('.menu.vertical ul a.active').closest('ul');
        var s = selectedVerticalSubMenu.closest('li').find('a').parentsUntil('ul.vertical-menu', 'li.has-children');
        s.each(function () {
            var m = $(this).find('a').first();
            m.addClass('active');
            m.css('display', 'block');
        });

        // bind horizontal menu hover
        $('.menu.horizontal li').mouseenter(function () {
            $(this).children('ul').stop(true).animate({
                opacity: 'toggle'
            }, 500);
        }).mouseleave(function () {
            $(this).children('ul').stop(true).animate({
                opacity: 'toggle'
            }, 200);
        });

        // select horizontal main if sub is selected
        var selectedHorizontalSubMenu = $('.menu.horizontal ul a.active').closest('ul');
        selectedHorizontalSubMenu.closest('li').find('a').first().addClass('active'); // set main manu item active if sub navi is selected

    },

    initProductAttributesButtons: function () {
        $('.attribute-button').on('click', function () {
            var entityWrappers = $(this).closest('.product-wrapper').find('.entity-wrapper');
            entityWrappers.hide();
            entityWrappers.removeClass('active');
            var id = $(this).attr('id').replace(/attribute-button-/, '');
            var activeEntityWrapper = $('#entity-wrapper-' + id);
            activeEntityWrapper.addClass('active');
            activeEntityWrapper.show();
        });
    },

    addSpinnerToButton: function (button, iconClass) {
        button.find('i').removeClass(iconClass);
        button.find('i').addClass('fa-circle-notch');
        button.find('i').addClass('fa-spin');
    },

    removeSpinnerFromButton: function (button, iconClass) {
        button.find('i').removeClass('fa-circle-notch');
        button.find('i').removeClass('fa-spin');
        button.find('i').addClass(iconClass);
    },

    enableButton: function (button) {
        button.attr('disabled', false);
        button.removeClass('disabled');
    },

    disableButton: function (button) {
        button.attr('disabled', 'disabled');
        button.addClass('disabled'); // :enabled selector does not work in chrome, bootstrap adds pointer-events: none;
    },

    applyBlinkEffect: function (container, callback) {
        container.fadeTo(150, 1, function () {
            $(this).fadeTo(150, 0, function () {
                $(this).fadeTo(150, 1);
                if (callback) {
                    callback();
                }
            });
        });
    },

    formatFloatAsCurrency: function (float) {
        var currency = this.formatFloatAsString(float) + ' ' + foodcoopshop.LocalizedJs.helper.CurrencySymbol;
        if (foodcoopshop.LocalizedJs.helper.defaultLocaleInBCP47 == 'en-US') {
            currency = foodcoopshop.LocalizedJs.helper.CurrencySymbol + this.formatFloatAsString(float);
        }
        return currency;
    },

    getCurrencyAsFloat: function (string) {
        var currencyRegExp = new RegExp(' \\' + foodcoopshop.LocalizedJs.helper.CurrencySymbol);
        if (foodcoopshop.LocalizedJs.helper.defaultLocaleInBCP47 == 'en-US') {
            currencyRegExp = new RegExp('\\' + foodcoopshop.LocalizedJs.helper.CurrencySymbol);
        }
        return this.getStringAsFloat(string.replace(currencyRegExp, ''));
    },

    formatFloatAsString: function(float) {
        var floatAsString = float.toLocaleString(
            foodcoopshop.LocalizedJs.helper.defaultLocaleInBCP47,
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
        return floatAsString;
    },

    getStringAsFloat: function (string) {
        // german uses , as decimal separator and not as thousand separator
        if (foodcoopshop.LocalizedJs.helper.defaultLocaleInBCP47 == 'de-DE') {
            string = string.replace(/,/, '.');
        }
        return parseFloat(string);
    },

    bindToggleLinks: function (autoOpen) {

        $('.toggle-link').on('click', function () {

            var elementToToggle = $(this).next();
            var toggleMode = elementToToggle.css('display');

            if (toggleMode == 'none') {
                var showMoreRegExp = new RegExp(foodcoopshop.LocalizedJs.helper.ShowMore);
                $(this).html($(this).html().replace(showMoreRegExp, foodcoopshop.LocalizedJs.helper.ShowLess));
                $(this).addClass('collapsed');
            } else {
                var showLessRegExp = new RegExp(foodcoopshop.LocalizedJs.helper.ShowLess);
                $(this).html($(this).html().replace(showLessRegExp, foodcoopshop.LocalizedJs.helper.ShowMore));
                $(this).removeClass('collapsed');
            }

            elementToToggle.stop(true, true).animate({
                height: 'toggle'
            }, 400);

        });

        if (autoOpen) {
            $('.toggle-link').trigger('click');
        }

    },

    setCakeServerName: function (cakeServerName) {
        this.cakeServerName = cakeServerName;
    },

    setIsManufacturer: function (isManufacturer) {
        this.isManufacturer = isManufacturer;
    },

    setPaymentMethods: function (paymentMethods) {
        this.paymentMethods = paymentMethods;
    },

    initAnystretch: function () {
        $.backstretch(
            '/img/bg-v2.4.jpg',
            {
                positionY: 'top',
                transitionDuration: 400
            }
        );
    },

    initLogoutButton: function () {
        $('a.logout-button').on('click', function () {

            var buttons = {};
            buttons['no'] = foodcoopshop.Helper.getJqueryUiNoButton();
            buttons['yes'] = {
                text: foodcoopshop.LocalizedJs.helper.yes,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    document.location.href = '/' + foodcoopshop.LocalizedJs.helper.routeLogout;
                }
            };

            $('<div></div>').appendTo('body')
                .html('<p>' + foodcoopshop.LocalizedJs.helper.logoutInfoText + '</p><img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />')
                .dialog({
                    modal: true,
                    title: foodcoopshop.LocalizedJs.helper.logout,
                    dialogClass: 'logout-button',
                    autoOpen: true,
                    width: 400,
                    resizable: false,
                    buttons: buttons
                });
        });
    },

    initLogoutInstantOrderCustomerButton: function () {
        $('#cart .instant-order-customer-info a.btn').on('click', function () {
            var buttons = {};
            buttons['no'] = foodcoopshop.Helper.getJqueryUiNoButton();
            buttons['yes'] = {
                text: foodcoopshop.LocalizedJs.helper.yes,
                click: function() {
                    $('.ui-dialog .ajax-loader').show();
                    $('.ui-dialog button').attr('disabled', 'disabled');
                    foodcoopshop.Helper.ajaxCall(
                        '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/ajaxDeleteInstantOrderCustomer',
                        {},
                        {
                            onOk: function (data) {
                                $('.featherlight', window.parent.document).remove();
                                document.location.reload();
                            },
                            onError: function (data) {
                                document.location.reload();
                            }
                        }
                    );
                }
            };

            $('<div></div>').appendTo('body')
                .html('<p>' + foodcoopshop.LocalizedJs.helper.ReallyCancelInstantOrder + '</p><img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />')
                .dialog({
                    modal: true,
                    title: foodcoopshop.LocalizedJs.helper.CancelInstantOrder,
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

    initTooltip: function (container, trigger) {
        trigger = trigger || 'hover';
        $(container).each(function() {
            $(this).not('.tooltipstered').tooltipster({
                contentAsHTML: true,
                interactive: true,
                maxWidth: 400,
                trigger: trigger,
                animationDuration: 150,
                delay: 100,
                theme: ['tooltipster-light']
            });
        });
    },

    cutRandomStringOffImageSrc: function (imageSrc) {
        return imageSrc.replace(/\?.{3}/g, '');
    },

    initJqueryUiIcons: function () {
        $('li.ui-state-default').hover(
            function () {
                $(this).addClass('ui-state-hover');
            },
            function () {
                $(this).removeClass('ui-state-hover');
            }
        );
    },

    showContent: function () {
        // do not use jquery .animate() or .show() here, if loaded in iframe and firefox, this does not work
        // only css('display') works
        $('body:not(.cake_errors) #container').css('display', 'block');
    },

    initCkeditor: function (name) {

        if (!CKEDITOR.env.isCompatible) {
            return false;
        }

        this.destroyCkeditor(name);

        CKEDITOR.timestamp = 'v4.11.2';
        $('textarea#' + name + '.ckeditor').ckeditor({
            customConfig: '/js/ckeditor/config.js'
        });

    },

    destroyCkeditor: function (name) {

        if (!CKEDITOR.env.isCompatible) {
            return false;
        }

        var editor = CKEDITOR.instances[name];
        if (editor) {
            editor.destroy(true);
        }

    },

    initCkeditorBig: function (name) {

        if (!CKEDITOR.env.isCompatible) {
            return false;
        }

        this.destroyCkeditor(name);

        CKEDITOR.timestamp = 'v4.11.2';
        $('textarea#' + name + '.ckeditor').ckeditor({
            customConfig: '/js/ckeditor/config-big.js'
        });

    },

    initCkeditorSmallWithUpload: function (name) {

        if (!CKEDITOR.env.isCompatible) {
            return false;
        }

        this.destroyCkeditor(name);

        CKEDITOR.timestamp = 'v4.11.2';
        $('textarea#' + name + '.ckeditor').ckeditor({
            customConfig: '/js/ckeditor/config-small-with-upload.js'
        });

    },

    initDatepicker: function () {
        jQuery(function ($) {
            $.datepicker.regional = {
                closeText: foodcoopshop.LocalizedJs.datepicker.close,
                prevText: '&#x3c;' + foodcoopshop.LocalizedJs.datepicker.prev,
                nextText: foodcoopshop.LocalizedJs.datepicker.next + '&#x3e;',
                currentText: foodcoopshop.LocalizedJs.datepicker.today,
                monthNames: [
                    foodcoopshop.LocalizedJs.helper.January,
                    foodcoopshop.LocalizedJs.helper.February,
                    foodcoopshop.LocalizedJs.helper.March,
                    foodcoopshop.LocalizedJs.helper.April,
                    foodcoopshop.LocalizedJs.helper.May,
                    foodcoopshop.LocalizedJs.helper.June,
                    foodcoopshop.LocalizedJs.helper.July,
                    foodcoopshop.LocalizedJs.helper.August,
                    foodcoopshop.LocalizedJs.helper.September,
                    foodcoopshop.LocalizedJs.helper.October,
                    foodcoopshop.LocalizedJs.helper.November,
                    foodcoopshop.LocalizedJs.helper.December
                ],
                monthNamesShort: [
                    foodcoopshop.LocalizedJs.helper.JanuaryShort,
                    foodcoopshop.LocalizedJs.helper.FebruaryShort,
                    foodcoopshop.LocalizedJs.helper.MarchShort,
                    foodcoopshop.LocalizedJs.helper.AprilShort,
                    foodcoopshop.LocalizedJs.helper.MayShort,
                    foodcoopshop.LocalizedJs.helper.JuneShort,
                    foodcoopshop.LocalizedJs.helper.JulyShort,
                    foodcoopshop.LocalizedJs.helper.AugustShort,
                    foodcoopshop.LocalizedJs.helper.SeptemberShort,
                    foodcoopshop.LocalizedJs.helper.OctoberShort,
                    foodcoopshop.LocalizedJs.helper.NovemberShort,
                    foodcoopshop.LocalizedJs.helper.DecemberShort
                ],
                dayNames: [
                    foodcoopshop.LocalizedJs.helper.Sunday,
                    foodcoopshop.LocalizedJs.helper.Monday,
                    foodcoopshop.LocalizedJs.helper.Tuesday,
                    foodcoopshop.LocalizedJs.helper.Wednesday,
                    foodcoopshop.LocalizedJs.helper.Thursday,
                    foodcoopshop.LocalizedJs.helper.Friday,
                    foodcoopshop.LocalizedJs.helper.Saturday
                ],
                dayNamesShort: [
                    foodcoopshop.LocalizedJs.helper.SundayShort,
                    foodcoopshop.LocalizedJs.helper.MondayShort,
                    foodcoopshop.LocalizedJs.helper.TuesdayShort,
                    foodcoopshop.LocalizedJs.helper.WednesdayShort,
                    foodcoopshop.LocalizedJs.helper.ThursdayShort,
                    foodcoopshop.LocalizedJs.helper.FridayShort,
                    foodcoopshop.LocalizedJs.helper.SaturdayShort
                ],
                dayNamesMin: [
                    foodcoopshop.LocalizedJs.helper.SundayShort,
                    foodcoopshop.LocalizedJs.helper.MondayShort,
                    foodcoopshop.LocalizedJs.helper.TuesdayShort,
                    foodcoopshop.LocalizedJs.helper.WednesdayShort,
                    foodcoopshop.LocalizedJs.helper.ThursdayShort,
                    foodcoopshop.LocalizedJs.helper.FridayShort,
                    foodcoopshop.LocalizedJs.helper.SaturdayShort
                ],
                weekHeader: foodcoopshop.LocalizedJs.datepicker.weekHeader,
                dateFormat: foodcoopshop.LocalizedJs.datepicker.dateFormat,
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: '',
                changeYear: true,
                changeMonth: true,
                duration: 'fast',
                yearRange: '2014:2020'
            };
            $.datepicker.setDefaults($.datepicker.regional);
        });
    },

    /**
     * @return value of an object by given path (separated by .)
     */
    resolveIndex : function (path, obj) {
        return path.split('.').reduce(function (prev, curr) {
            return prev ? prev[curr] : undefined;
        }, obj || self);
    },

    getRandomCode: function () {
        return Math.floor(Math.random() * 981151510);
    },

    removeFlashMessage: function () {
        $('#flashMessage').remove();
    },

    appendFlashMessageCloser: function () {
        $('#flashMessage').prepend('<a class="closer" title="' + foodcoopshop.LocalizedJs.helper.Close + '" href="javascript:void(0);"><i class="far fa-times-circle"></i></a>');
    },

    bindFlashMessageCloser: function () {
        $('#flashMessage a.closer').on('click', function () {
            $(this).parent().animate({
                height: 'toggle'
            }, 500, function () {
                $(this).remove();
            });
        });
    },

    showFlashMessage: function (message, type) {

        this.removeFlashMessage();

        var root = '#content';

        var responsiveHeaderSelector = '#responsive-header';
        if (foodcoopshop.Helper.isMobile() && $(responsiveHeaderSelector).length == 1) {
            root = responsiveHeaderSelector;
        }

        var messageNode = $('<div />');
        messageNode.html(message)
            .addClass(type)
            .attr('id', 'flashMessage');
        $(root).append(messageNode);

        this.appendFlashMessageCloser();
        this.bindFlashMessageCloser();

    },

    showOrAppendSuccessMessage : function (message) {
        if ($('#flashMessage').length === 0) {
            this.showSuccessMessage(message);
        } else {
            $('#flashMessage').append('<br />' + message);
        }
    },

    /**
     * if flash message was success message, transfer it into error message
     */
    showOrAppendErrorMessage : function (message) {
        if ($('#flashMessage').length === 0) {
            this.showErrorMessage(message);
        } else {
            $('#flashMessage').removeClass('success').addClass('error').append('<br />' + message);
        }
    },

    showSuccessMessage: function (message) {
        this.showFlashMessage(message, 'success');
    },

    showErrorMessage: function (message) {
        this.showFlashMessage(message, 'error');
    },

    ajaxCall: function (url, data, callbacks) {

        return jQuery.ajax({
            url: url,
            type: callbacks.method || 'POST',
            contentType: 'application/x-www-form-urlencoded; charset=utf-8',
            data: data,
            dataType: 'json',
            success: function (data, textStatus) {
                try {
                    if (callbacks.onEnd) {
                        callbacks.onEnd(data);
                    }
                    if (data.status == 1) {
                        callbacks.onOk(data);
                    } else {
                        callbacks.onError(data);
                    }
                    $('.ui-dialog button').attr('disabled', false);
                } catch (e) {
                    if (console && console.error) {
                        console.error(e);
                    } else {
                        alert(e.toString());
                    }
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                data = {
                    status: 9,
                    msg: foodcoopshop.LocalizedJs.helper.anErrorOccurred + '.',
                    jquery: {
                        XMLHttpRequest: XMLHttpRequest,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    }
                };
                if (XMLHttpRequest.responseJSON && XMLHttpRequest.responseJSON.msg) {
                    data.msg = XMLHttpRequest.responseJSON.msg;
                }
                if (callbacks.onEnd) {
                    callbacks.onEnd(data);
                }
                callbacks.onError(data);
                if (window.console && console.error) {
                    console.error(data);
                } else {
                    alert(data.msg + ' ' + textStatus + ' ' + errorThrown);
                }
                $('.ui-dialog button').attr('disabled', false);
            }
        });
    }

};
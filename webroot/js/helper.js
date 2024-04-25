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

String.prototype.replaceI18n = function(object, replace) {
    var regExp = new RegExp('\\{' + object + '\\}', 'g');
    return this.replace(regExp, replace);
};

foodcoopshop.Helper = {

    init: function () {
        this.initMenu();
        foodcoopshop.ModalLogout.init();
        this.changeOutgoingLinksTargetToBlank();
        this.initCookieBanner();
        foodcoopshop.ColorMode.init();
        if (!this.isMobile()) {
            this.initWindowResize();
            this.initScrolltopButton();
            this.initMenuAutoHide();
            this.adaptionsForHorizontalScrolling();
            this.showContent();
        }
    },

    // https://stackoverflow.com/questions/2367979/pass-post-data-with-window-location-href
    postForm: function(path, params, method) {
        method = method || 'post';
    
        var form = document.createElement('form');
        form.setAttribute('method', method);
        form.setAttribute('action', path);
    
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                var hiddenField = document.createElement('input');
                hiddenField.setAttribute('type', 'hidden');
                hiddenField.setAttribute('name', key);
                hiddenField.setAttribute('value', params[key]);
    
                form.appendChild(hiddenField);
            }
        }
    
        document.body.appendChild(form);
        form.submit();
    },

    // https://github.com/Studio-42/elFinder/issues/2905#issuecomment-487106097
    copyToClipboard: function(string) {

        var temp = document.createElement('textarea');

        temp.value = string;
        temp.selectionStart = 0;
        temp.selectionEnd = temp.value.length;

        var s = temp.style;
        s.position = 'fixed';
        s.left = '-100%';

        document.body.appendChild(temp);
        temp.focus();
        var result = document.execCommand('copy');

        temp.blur();
        document.body.removeChild(temp);

        return result;
    },

    showLoader: function(targetElement) {
        this.removeLoader();
        targetElement = targetElement || 'body';
        $(targetElement).append('<div id="full-page-loader"><i class="fas fa-circle-notch  fa-spin"></i></div>');
    },

    removeLoader: function() {
        $('#full-page-loader').remove();
    },

    initShowLoaderOnContentChange: function() {
        var allowList = [
            'a, button',
        ];
        var disallowList = [
            foodcoopshop.Cart.disabledButtonsDuringUpdateCartRequest,
            '#user-menu a',
            '.order-for-different-customer-info a',
            '.swiper-button-prev',
            '.swiper-button-next',
            '.toggle-link',
            'a.calculator-toggle-button',
            'a.as',
            'a[href^="http://"]',
            'a[href^="https://"]',
            'a.sb-toggle-left',
            'a.open-with-modal',
            'a.color-mode-toggle',
            'button.dropdown-toggle',
            '.product-search-form-wrapper button',
            '.modal-content button',
            '.modal-content a',
            '#flashMessage a',
            'a.responsive-cart'
        ];
        $(allowList.join(',')).not(disallowList.join(',')).on('click', function() {
            foodcoopshop.Helper.showLoader();
        });
    },

    isNumeric: function(str) {
        if (typeof str != 'string') return false; // we only process strings!
        return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
                !isNaN(parseFloat(str)); // ...and ensure strings of whitespace fail
    },

    initRegistrationAsCompany: function() {

        var isCompanyCheckbox = $('#customers-is-company');
        isCompanyCheckbox.on('change', function() {
            var firstnameElements = $('label[for="customers-firstname"], #customers-firstname-error');
            var lastnameElements = $('label[for="customers-lastname"], #customers-lastname-error');
            var lastnameWrapper = $('label[for="customers-lastname"]').closest('.input');
            var regExp;
            var newHtml;
            if ($(this).prop('checked')) {
                firstnameElements.each(function() {
                    regExp = new RegExp(foodcoopshop.LocalizedJs.helper.Firstname);
                    newHtml = $(this).html().replace(regExp, foodcoopshop.LocalizedJs.helper.CompanyName);
                    $(this).html(newHtml);
                });
                lastnameElements.each(function() {
                    regExp = new RegExp(foodcoopshop.LocalizedJs.helper.PleaseEnterYourLastname);
                    newHtml = $(this).html().replace(regExp, foodcoopshop.LocalizedJs.helper.PleaseEnterTheContactPerson);
                    $(this).html(newHtml);
                    regExp = new RegExp(foodcoopshop.LocalizedJs.helper.Lastname);
                    newHtml = $(this).html().replace(regExp, foodcoopshop.LocalizedJs.helper.ContactPerson);
                    $(this).html(newHtml);
                });
                lastnameWrapper.removeClass('required');
            } else {
                firstnameElements.each(function() {
                    regExp = new RegExp(foodcoopshop.LocalizedJs.helper.CompanyName);
                    newHtml = $(this).html().replace(regExp, foodcoopshop.LocalizedJs.helper.Firstname);
                    $(this).html(newHtml);
                });
                lastnameElements.each(function() {
                    regExp = new RegExp(foodcoopshop.LocalizedJs.helper.PleaseEnterTheContactPerson);
                    newHtml = $(this).html().replace(regExp, foodcoopshop.LocalizedJs.helper.PleaseEnterYourLastname);
                    $(this).html(newHtml);
                    regExp = new RegExp(foodcoopshop.LocalizedJs.helper.ContactPerson);
                    newHtml = $(this).html().replace(regExp, foodcoopshop.LocalizedJs.helper.Lastname);
                    $(this).html(newHtml);
                });
                lastnameWrapper.addClass('required');
            }
        });

        if (isCompanyCheckbox.prop('checked')) {
            isCompanyCheckbox.trigger('change');
        }

    },

    setFutureOrderDetails: function(futureOrderDetails) {

        futureOrderDetails = $.parseJSON(futureOrderDetails);

        if (futureOrderDetails.length == 0) {
            return;
        }

        var groupedOrderDetails = [];

        for(var i=0;i<futureOrderDetails.length;i++) {
            var productId = futureOrderDetails[i].product_id;
            if (groupedOrderDetails[productId] === undefined) {
                groupedOrderDetails[productId] = [];
            }
            groupedOrderDetails[productId].push(futureOrderDetails[i]);
        }

        var result = [];
        var html = '';
        var linesHtml = '';
        var lines;

        for(productId in groupedOrderDetails) {
            html = '<p style="margin-top:5px;"><i><b>';
            lines = [];
            linesHtml = '';
            for(i in groupedOrderDetails[productId]) {
                linesHtml = foodcoopshop.LocalizedJs.helper.YouHaveAlreadyOrdered01TimesFor2.replaceI18n(0, '"' + groupedOrderDetails[productId][i].product_name + '"');
                linesHtml = linesHtml.replaceI18n(1, groupedOrderDetails[productId][i].product_amount);
                var formattedPickupDay = new Date(groupedOrderDetails[productId][i].pickup_day).toLocaleDateString(foodcoopshop.LocalizedJs.helper.defaultLocaleInBCP47, { year:'numeric', month:'2-digit', day:'2-digit'});
                linesHtml = linesHtml.replaceI18n(2, formattedPickupDay);
                lines.push(linesHtml);
            }
            html += lines.join('<br />');
            html += '</b></i></p>';
            result.push(html);
            $('#pw-' + productId).find('.c2').append(html);
        }

    },

    openPrintDialogForFile : function(file) {
        var iframe = document.createElement('iframe');
        iframe.style.visibility = 'hidden';
        iframe.src = file;
        document.body.appendChild(iframe);
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    },

    initCookieBanner: function() {
        new CookiesEuBanner(function () {
            // callback when cookies are accepted
        }, true);
    },

    initMenuAutoHide : function() {

        // scroll is still position
        var scroll = $(document).scrollTop();
        var headerHeight = $('#header').height();

        $(window).scroll(function() {
            // scrolled is new position just obtained
            var scrolled = $(document).scrollTop();

            // optionally emulate non-fixed positioning behaviour
            if (scrolled > headerHeight){
                $('#header').addClass('off-canvas');
            } else {
                $('#header').removeClass('off-canvas');
            }

            if (scrolled > scroll){
                // scrolling down
                $('#header').removeClass('fixed');
            } else {
                //scrolling up
                $('#header').addClass('fixed');
            }

            scroll = $(document).scrollTop();
        });
    },

    addPrevAndNextCategoryLinks : function() {
        this.addPrevAndNextLinks(
            '#categories-menu li a',
            '#inner-content h1'
        );
    },

    addPrevAndNextManufacturerLinks : function() {
        this.addPrevAndNextLinks(
            '#manufacturers-menu li a',
            '#inner-content .manufacturer-infos'
        );
    },

    addPrevAndNextLinks : function(menu, afterContainerTop) {
        menu = $(menu);
        var activeElementHref = document.location.pathname;
        var nextElement = null;
        var prevElement = null;
        var i = 0;
        menu.each(function() {
            if (activeElementHref == $(this).attr('href')) {
                if ($(menu[i+1]).length > 0) {
                    nextElement = $(menu[i+1]).clone();
                }
                if ($(menu[i-1]).length > 0) {
                    prevElement = $(menu[i-1]).clone();
                }
            }
            i++;
        });
        var productsAvailable = $('#inner-content .pw').length > 0;
        if (prevElement) {
            prevElement.attr('class', 'prev-next-button prev-button btn btn-outline-light');
            prevElement.html('<i class="fas fa-arrow-circle-left fa"></i> ' + prevElement.text());
            if (productsAvailable) {
                $(afterContainerTop).after(prevElement.clone());
            }
            $('#inner-content').append(prevElement.addClass('bottom'));
        }
        if (nextElement) {
            nextElement.attr('class', 'prev-next-button next-button btn btn-outline-light');
            nextElement.html(nextElement.text() + ' <i class="fas fa-arrow-circle-right fa"></i>');
            if (productsAvailable) {
                $(afterContainerTop).after(nextElement.clone());
            }
            $('#inner-content').append(nextElement.addClass('bottom'));
        }
        if ((prevElement || nextElement)) {
            $('#inner-content .prev-next-button.bottom').first().before($('<hr style="clear:both;" />'));
        }
    },

    initBootstrapSelect : function(container) {
        container.find('select:not(.no-bootstrap-select)').each(function () {
            var options = {
                liveSearch: true,
                showIcon: true,
                iconBase: 'fontawesome',
                tickIcon: 'fas fa-check'
            };
            if ($(this).attr('multiple') == 'multiple') {
                var emptyElement = $(this).find('option').first();
                if (emptyElement.val() == '') {
                    options.noneSelectedText = emptyElement.html();
                    emptyElement.remove();
                }
            }
            $(this).selectpicker(options);
        });
    },

    initAmountSwitcher : function() {
        $('.ew a.as').on('click', function() {
            var inputField = $(this).closest('.amount-wrapper').find('input[name="amount"]');
            var currentValue = parseInt(inputField.val());
            if (isNaN(currentValue)) {
                currentValue = 0;
            }
            var result = 0;
            if ($(this).hasClass('as-plus')) {
                result = currentValue + 1;
            } else {
                result = currentValue - 1;
            }
            if (result < 2) {
                result = 1;
            }
            var maximum = $(this).closest('.amount-wrapper').find('.availibility');
            if (maximum.length > 0) {
                var max = parseInt(maximum.html().replace(/\D+/g, ''));
                if (result > max) {
                    result = max;
                }
            }
            var amountSwitcherMinus = $(this).closest('.amount-wrapper').find('.as-minus .fas');
            if (result > 1) {
                amountSwitcherMinus.show();
            } else {
                amountSwitcherMinus.hide();
            }
            inputField.val(result);
        });
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

    initBlogPostCarousel: function () {

        var selector = '.blog-wrapper';
        $(selector).addClass('swiper');

        var slides = $(selector).find('.blog-post-wrapper');
        if (slides.length > 3) {
            $(selector).append('<a href="javascript:void(0);" class="swiper-button-prev"></a>');
            $(selector).append('<a href="javascript:void(0);" class="swiper-button-next"></a>');
        }
        $(selector).append('<div class="swiper-wrapper"></div>');
        $(selector).find('.swiper-wrapper').append(slides);

        new Swiper(selector, {
            loop: false,
            speed: 300,
            centeredSlides: true,
            slidesPerView: 2,
            spaceBetween: 16,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            },
            breakpoints: {
                768: {
                    speed: 1000,
                    centeredSlides: true,
                    slidesPerView: 1,
                    initialSlide: 0,
                    spaceBetween: 16
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

    initLoginForm: function () {
        $('#LoginForm button[type="submit"]').on('click', function () {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-sign-in-alt');
            foodcoopshop.Helper.disableButton($(this));
            $(this).closest('form').submit();
        });
    },

    initSearchForm: function () {
        $('.product-search-form-wrapper form button[type="submit"]').on('click', function () {
            var form = $(this).closest('form');
            if (form.find('input').val() != '') {
                foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-search');
                foodcoopshop.Helper.disableButton($(this));
                form.submit();
            }
        });
        $('.product-search-form-wrapper a.btn').on('click', function () {
            console.log('click');
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-backspace');
            foodcoopshop.Helper.disableButton($(this));
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

        if ($('#antiSpam').length == 0) {
            var inputField = $('<input />').attr('id', 'antiSpam').attr('name', 'antiSpam').attr('type', 'hidden');
            $('#RegistrationForm').prepend(inputField);
        }
        var a = document.getElementById('antiSpam');
        if (isNaN(a.value) == true) {
            a.value = 0;
        } else {
            a.value = parseInt(a.value) + 1;
        }

        setTimeout(function () {
            foodcoopshop.Helper.updateAntiSpamField(form);
        }, 1000);
    },

    changeOutgoingLinksTargetToBlank: function () {
        $('a[href^="http://"]:not(".do-not-change-to-target-blank"):not([href*="' + window.location.host + '"])').attr('target', '_blank');
        $('a[href^="https://"]:not(".do-not-change-to-target-blank"):not([href*="' + window.location.host + '"])').attr('target', '_blank');
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

        var selector = '#slider';
        var hasOnlyOneSlide = $(selector).find('.swiper-slide').length == 1;
        if (hasOnlyOneSlide) {
            return;
        }

        $(selector).append('<a href="javascript:void(0);" class="swiper-button-prev"></a>');
        $(selector).append('<a href="javascript:void(0);" class="swiper-button-next"></a>');

        var mySwiper = new Swiper(selector, {
            loop: true,
            autoHeight: true,
            speed: 1500,
            autoplay: {
                delay: 7000
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            }
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

    adaptionsForHorizontalScrolling : function() {
        $('#header').scrollToFixed({'offsetLeft': 2});
        $('.inner-right').scrollToFixed();
    },

    initWindowResize: function () {
        $(window).on('resize', function () {
            foodcoopshop.Helper.onWindowResize();
        });
        foodcoopshop.Helper.onWindowResize();
    },

    onWindowResize: function () {

        if ($('body').hasClass('self_services')) {
            return;
        }

        var difference = 0;
        var shoppingPriceElement;

        // whole page is called in iframe in order-for-different-customer-mode
        var orderForDifferentCustomerIframe = window.parent.$('#order-for-different-customer-add .modal-body iframe');

        if (orderForDifferentCustomerIframe.length > 0) {
            difference = 130;
            difference += $('.order-for-different-customer-info').height();
            shoppingPriceElement = $('.shopping-price-info');
            if (shoppingPriceElement.length > 0) {
                difference += shoppingPriceElement.height() + 8;
            }
            newCartHeight = orderForDifferentCustomerIframe.height();
        } else {
            difference = 120;
            var loadLastOrderDetailsDropdown = $('#cart #load-last-order-details');
            if (loadLastOrderDetailsDropdown.length > 0) {
                difference += loadLastOrderDetailsDropdown.closest('.input').height();
            }
            var globalNoDeliveryDayBox = $('#global-no-delivery-day-box');
            if (globalNoDeliveryDayBox.length > 0) {
                difference += globalNoDeliveryDayBox.height();
            }
            shoppingPriceElement = $('.shopping-price-info');
            if (shoppingPriceElement.length > 0) {
                difference += shoppingPriceElement.height() + 8;
            }
            var newCartHeight = $(window).height();
        }

        var sumsWrapperHeight = $('#cart .sums-wrapper').height();
        var newMaxHeight = parseInt(newCartHeight) - difference - sumsWrapperHeight;
        $('#cart .products').css('max-height', newMaxHeight);

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
            }, 300);
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
            var entityWrappers = $(this).closest('.pw').find('.ew');
            entityWrappers.hide();
            entityWrappers.removeClass('active');
            var id = $(this).attr('id').replace(/attribute-button-/, '');
            var activeEntityWrapper = $('#ew-' + id);
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
        // en-US uses . as decimal separator and not as thousand separator
        if (foodcoopshop.LocalizedJs.helper.defaultLocaleInBCP47 != 'en-US') {
            string = string.replace(/,/, '_comma_');
            string = string.replace(/\./, '_dot_');
            string = string.replace(/_comma_/, '.');
            string = string.replace(/_dot_/, '');
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

    setFullBaseUrl: function (fullBaseUrl) {
        this.fullBaseUrl = fullBaseUrl;
    },

    setIsManufacturer: function (isManufacturer) {
        this.isManufacturer = isManufacturer;
    },

    setIsSelfServiceModeEnabled: function (isSelfServiceModeEnabled) {
        this.isSelfServiceModeEnabled = isSelfServiceModeEnabled;
    },

    setPaymentMethods: function (paymentMethods) {
        this.paymentMethods = paymentMethods;
    },

    initAnystretch: function () {
        $.backstretch(
            '/img/bg-v4.0.jpg',
            {
                positionY: 'top',
                transitionDuration: 400
            }
        );
    },

    initTooltip: function (container) {
        var trigger = 'hover';
        if (this.isMobile()) {
            trigger = 'click';
        }
        $(container).each(function() {
            $(this).not('.tooltipstered').tooltipster({
                contentAsHTML: true,
                interactive: true,
                maxWidth: 450,
                distance: 0,
                trigger: trigger,
                animationDuration: 0,
                delay: 20,
                theme: ['tooltipster-light']
            });
        });
    },

    cutRandomStringOffImageSrc: function (imageSrc) {
        return imageSrc.replace(/\?.{3}/g, '');
    },

    showContent: function () {
        // do not use jquery .animate() or .show() here, if loaded in iframe and firefox, this does not work
        // only css('display') works
        $('body:not(.cake_errors) #container').css('display', 'block');
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
                yearRange: '2014:2030'
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

    removeFlashMessage: function () {
        $('#flashMessage').remove();
    },

    addFlashMessageTools: function () {

        $('#flashMessage').prepend('<a class="closer" title="' + foodcoopshop.LocalizedJs.helper.Close + '" href="javascript:void(0);"><i class="far fa-times-circle"></i></a>');

        $('#flashMessage .progress').remove();

        var progressBarHtml = '<div class="progress">';
        progressBarHtml += '<div class="progress-bar bg-success" style="width:0%;"></div>';
        progressBarHtml += '<div class="progress-bar bg-white" style="background-color:#fff;width:100%;"></div>';
        progressBarHtml += '</div>';
        $('#flashMessage.success').append(progressBarHtml);

        var duration = 5000;
        var flashMessageText = $('#flashMessage.success').text();
        if (flashMessageText.match(/wurde in deine Einkaufstasche gelegt/)) {
            duration = 1500;
        }

        $('#flashMessage.success .progress-bar.bg-success')
            .animate({
                'width': '100%',
            }, {
                duration: duration,
                easing: 'linear',
            }
            );
        $('#flashMessage.success .progress-bar.bg-white')
            .animate({
                'width': '0%',
                'opacity': 0.2,
            }, {
                duration: duration,
                easing: 'linear',
            }
            );

        setTimeout(function() {
            $('#flashMessage.success a.closer').trigger('click');
        }, duration);

    },

    bindFlashMessageCloser: function () {
        $('#flashMessage a.closer').on('click', function () {
            $(this).parent().animate({
                height: 'toggle'
            }, 500, function () {
                $(this).remove();
            });
        });
        $(document).one('keydown', function(event) {
            if (event.keyCode == 27) {
                $('#flashMessage a.closer').trigger('click');
            }
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

        this.addFlashMessageTools();
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

        var csrfToken = $('meta[name="csrfToken"]').attr('content');
        jQuery.ajaxSetup({
            headers:
            { 'X-CSRF-TOKEN': csrfToken }
        });

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
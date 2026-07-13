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

foodcoopshop.Helper = {

    init: function () {
        this.initMenu();
        foodcoopshop.ModalLogout.init();
        this.changeOutgoingLinksTargetToBlank();
        this.initCookieBanner();
        foodcoopshop.ColorMode.init();
        if (!this.isMobile()) {
            this.initFixedHeaderOffset();
            this.initScrolltopButton();
            this.initMenuAutoHide();
            this.showContent();
        }
    },

    // https://stackoverflow.com/questions/2367979/pass-post-data-with-window-location-href
    postFormInNewWindow: function(path, params, method) {
        method = method || 'post';
    
        var form = document.createElement('form');
        form.setAttribute('method', method);
        form.setAttribute('action', path);
        form.setAttribute('target', '_blank');
    
        var csrfToken = $('meta[name="csrfToken"]').attr('content');
        var csrfTokenHiddenField = document.createElement('input');
        csrfTokenHiddenField.setAttribute('type', 'hidden');
        csrfTokenHiddenField.setAttribute('name', '_csrfToken');
        csrfTokenHiddenField.setAttribute('value', csrfToken);
        form.appendChild(csrfTokenHiddenField);

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
        try {
            if (!navigator.clipboard || !navigator.clipboard.writeText) {
                return Promise.reject(new Error('Clipboard API not available'));
            }
            return navigator.clipboard.writeText(String(string));
        } catch (error) {
            return Promise.reject(error);
        }
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
                    regExp = new RegExp(__('Firstname'));
                    newHtml = $(this).html().replace(regExp, __('Company_name'));
                    $(this).html(newHtml);
                });
                lastnameElements.each(function() {
                    regExp = new RegExp(__('Please_enter_your_lastname'));
                    newHtml = $(this).html().replace(regExp, __('Please_enter_the_contact_person'));
                    $(this).html(newHtml);
                    regExp = new RegExp(__('Lastname'));
                    newHtml = $(this).html().replace(regExp, __('Contact_person'));
                    $(this).html(newHtml);
                });
                lastnameWrapper.removeClass('required');
            } else {
                firstnameElements.each(function() {
                    regExp = new RegExp(__('Company_name'));
                    newHtml = $(this).html().replace(regExp, __('Firstname'));
                    $(this).html(newHtml);
                });
                lastnameElements.each(function() {
                    regExp = new RegExp(__('Please_enter_the_contact_person'));
                    newHtml = $(this).html().replace(regExp, __('Please_enter_your_lastname'));
                    $(this).html(newHtml);
                    regExp = new RegExp(__('Contact_person'));
                    newHtml = $(this).html().replace(regExp, __('Lastname'));
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
                var formattedPickupDay = new Date(groupedOrderDetails[productId][i].pickup_day).toLocaleDateString(foodcoopshop.config.defaultLocaleInBCP47, { year:'numeric', month:'2-digit', day:'2-digit'});
                linesHtml = __('You_have_already_ordered_{0}_{1}_times_for_{2}.', '"' + groupedOrderDetails[productId][i].product_name + '"', groupedOrderDetails[productId][i].product_amount, formattedPickupDay);
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

    initFixedHeaderOffset: function () {
        var header = document.getElementById('header');
        if (!header) {
            return;
        }
        var apply = function () {
            document.documentElement.style.setProperty('--header-height', header.offsetHeight + 'px');
        };
        apply();
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(apply).observe(header);
        } else {
            $(window).on('resize load', apply);
        }
    },

    initMenuAutoHide : function() {

        var header = $('#header');
        if (!header.length) {
            return;
        }

        if ($('body').hasClass('has-page-hero')) {
            header.removeClass('off-canvas fixed');
            return;
        }

        var scroll = $(document).scrollTop();
        var headerHeight = header.outerHeight() || 0;
        var minHideOffset = Math.max(220, Math.round(headerHeight * 1.35));
        var pageHeaderBanner = $('.page-header-banner');
        var downDistance = 0;
        var upDistance = 0;

        if (pageHeaderBanner.length) {
            minHideOffset = Math.max(
                minHideOffset,
                Math.round(pageHeaderBanner.offset().top + pageHeaderBanner.outerHeight()),
            );
        }

        $(window).scroll(function() {
            var scrolled = $(document).scrollTop();
            var delta = scrolled - scroll;

            if (scrolled <= headerHeight) {
                header.removeClass('off-canvas fixed');
                downDistance = 0;
                upDistance = 0;
                scroll = scrolled;
                return;
            }

            if (delta > 0) {
                downDistance += delta;
                upDistance = 0;

                if (scrolled > minHideOffset && downDistance >= 24) {
                    header.addClass('off-canvas').removeClass('fixed');
                    downDistance = 0;
                }
            } else if (delta < 0) {
                upDistance += Math.abs(delta);
                downDistance = 0;

                if (upDistance >= 12 || scrolled <= minHideOffset) {
                    header.removeClass('off-canvas').addClass('fixed');
                    upDistance = 0;
                }
            }

            scroll = scrolled;
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

    getMaxVisibleBlogPosts: function () {
        var containerWidth = $('.blog-wrapper').width() || $('#inner-content').width() || 0;
        var slideWidth = 249; // 229px width + 2*10px padding
        var gap = 16;
        return Math.max(1, Math.floor((containerWidth + gap) / (slideWidth + gap)));
    },

    initBlogPostCarousel: function () {

        var selector = '.blog-wrapper';
        $(selector).addClass('swiper');

        var slides = $(selector).find('.blog-post-wrapper');
        var maxVisible = this.getMaxVisibleBlogPosts();

        if (slides.length > maxVisible) {
            $(selector).append('<a href="javascript:void(0);" class="swiper-button-prev"></a>');
            $(selector).append('<a href="javascript:void(0);" class="swiper-button-next"></a>');
        }
        $(selector).append('<div class="swiper-wrapper"></div>');
        $(selector).find('.swiper-wrapper').append(slides);

        var swiperInstance = new Swiper(selector, {
            loop: false,
            speed: 300,
            centeredSlides: false,
            slidesPerView: maxVisible,
            spaceBetween: 16,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });

        var self = this;
        $(window).on('resize', function () {
            var newMaxVisible = self.getMaxVisibleBlogPosts();
            swiperInstance.params.slidesPerView = newMaxVisible;
            swiperInstance.update();

            if (slides.length > newMaxVisible) {
                $(selector).find('.swiper-button-prev, .swiper-button-next').show();
            } else {
                $(selector).find('.swiper-button-prev, .swiper-button-next').hide();
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
        this.initPasswordToggle();
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
        $('.product-search-form-wrapper a.reset').on('click', function () {
            foodcoopshop.Helper.addSpinnerToButton($(this), $(this).find('.fa-times-circle').length ? 'fa-times-circle' : 'fa-backspace');
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

    /**
     * columns property is not rendered correctly in safari
     * so simply turn it off
     */
    applySafariFixForMenu: function() {
        $('#main-menu li ul').css('columns', 'auto');
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
        if (Math.abs(float) < 0.005) {
            float = 0;
        }
        var currency = this.formatFloatAsString(float) + ' ' + foodcoopshop.config.CurrencySymbol;
        if (foodcoopshop.config.defaultLocaleInBCP47 == 'en-US') {
            currency = foodcoopshop.config.CurrencySymbol + this.formatFloatAsString(float);
        }
        return currency;
    },

    getCurrencyAsFloat: function (string) {
        var currencyRegExp = new RegExp(' \\' + foodcoopshop.config.CurrencySymbol);
        if (foodcoopshop.config.defaultLocaleInBCP47 == 'en-US') {
            currencyRegExp = new RegExp('\\' + foodcoopshop.config.CurrencySymbol);
        }
        return this.getStringAsFloat(string.replace(currencyRegExp, ''));
    },

    formatFloatAsString: function(float) {
        var floatAsString = float.toLocaleString(
            foodcoopshop.config.defaultLocaleInBCP47,
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
        return floatAsString;
    },

    getStringAsFloat: function (string) {
        // en-US uses . as decimal separator and not as thousand separator
        if (foodcoopshop.config.defaultLocaleInBCP47 != 'en-US') {
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
                var showMoreRegExp = new RegExp(__('Show_more'));
                $(this).html($(this).html().replace(showMoreRegExp, __('Show_less')));
                $(this).addClass('collapsed');
            } else {
                var showLessRegExp = new RegExp(__('Show_less'));
                $(this).html($(this).html().replace(showLessRegExp, __('Show_more')));
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
            '/img/bg-v4.1.jpg',
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

    initPasswordToggle: function () {
        $('input[type="password"]').each(function() {
            var inputField = $(this);
            
            if (inputField.parent().hasClass('password-toggle-wrapper')) {
                return;
            }
            
            inputField.wrap('<div class="password-toggle-wrapper"></div>');
            
            var toggleIcon = $('<i class="fas fa-eye password-toggle-icon"></i>');
            
            inputField.after(toggleIcon);
            
            toggleIcon.on('click', function() {
                var icon = $(this);
                var $passwordInput = icon.prev('input');
                
                if ($passwordInput.attr('type') === 'password') {
                    $passwordInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $passwordInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
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
                closeText: __('datepicker_close'),
                prevText: '&#x3c;' + __('datepicker_prev'),
                nextText: __('datepicker_next') + '&#x3e;',
                currentText: __('datepicker_today'),
                monthNames: [
                    __('January'),
                    __('February'),
                    __('March'),
                    __('April'),
                    __('May'),
                    __('June'),
                    __('July'),
                    __('August'),
                    __('September'),
                    __('October'),
                    __('November'),
                    __('December')
                ],
                monthNamesShort: [
                    __('JanuaryShort'),
                    __('FebruaryShort'),
                    __('MarchShort'),
                    __('AprilShort'),
                    __('MayShort'),
                    __('JuneShort'),
                    __('JulyShort'),
                    __('AugustShort'),
                    __('SeptemberShort'),
                    __('OctoberShort'),
                    __('NovemberShort'),
                    __('DecemberShort')
                ],
                dayNames: [
                    __('Sunday'),
                    __('Monday'),
                    __('Tuesday'),
                    __('Wednesday'),
                    __('Thursday'),
                    __('Friday'),
                    __('Saturday')
                ],
                dayNamesShort: [
                    __('SundayShort'),
                    __('MondayShort'),
                    __('TuesdayShort'),
                    __('WednesdayShort'),
                    __('ThursdayShort'),
                    __('FridayShort'),
                    __('SaturdayShort')
                ],
                dayNamesMin: [
                    __('SundayShort'),
                    __('MondayShort'),
                    __('TuesdayShort'),
                    __('WednesdayShort'),
                    __('ThursdayShort'),
                    __('FridayShort'),
                    __('SaturdayShort')
                ],
                weekHeader: __('WeekHeader'),
                dateFormat: foodcoopshop.config.dateFormat,
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: '',
                changeYear: true,
                changeMonth: true,
                duration: 'fast',
                yearRange: '2014:2035'
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

        $('#flashMessage').prepend('<a class="closer" title="' + __('Close') + '" href="javascript:void(0);"><i class="far fa-times-circle"></i></a>');

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
            var searchInputField = $('form#product-search-1 input[name="keyword"]');
            if (searchInputField.length > 0) {
                foodcoopshop.SelfService.setFocusToSearchInputField();
            }
        });
        $(document).one('keydown', function(event) {
            if (event.keyCode == 27) {
                $('#flashMessage a.closer').trigger('click');
            }
        });
    },

    showFlashMessage: function (message, type) {

        this.removeFlashMessage();

        const defaultRoot = '#container';
        const fallbackRoot = '#content'; // for self-service
        var root = $(defaultRoot).length > 0 ? defaultRoot : fallbackRoot;

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

    showWarningMessage: function (message) {
        this.showFlashMessage(message, 'warning');
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
                    msg: __('An_error_occurred') + '.',
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
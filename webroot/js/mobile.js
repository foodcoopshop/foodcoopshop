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
foodcoopshop.Mobile = {

    getSlidebarMenu: function (side) {
        var responsiveMenu = $('<ul/>');
        responsiveMenu.addClass('sb-slidebar sb-' + side); // for css
        responsiveMenu.attr('off-canvas', 'sb-' + side + ' ' + side + ' overlay');
        return responsiveMenu;
    },

    getResponsiveMenuButton: function () {
        var showResponsiveMenuButton = $('<a/>');
        showResponsiveMenuButton.addClass('sb-toggle-left');
        showResponsiveMenuButton.html('<i class="fas fa-bars fa-2x"></i>');
        return showResponsiveMenuButton;
    },

    changeToogleIcon : function(isSlidebarVisible) {
        var iconElement = $('.sb-toggle-left').find('i');
        var iconOpen = 'fa-bars';
        var iconClosed = 'fa-times';
        if (isSlidebarVisible) {
            iconElement.removeClass(iconOpen);
            iconElement.addClass(iconClosed);
        } else {
            iconElement.removeClass(iconClosed);
            iconElement.addClass(iconOpen);
        }
    },

    bindToggleLeft : function (controller) {
        $('.sb-toggle-left').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            controller.toggle('sb-left', function () {
                if ($('.sb-left').css('display') == 'block') {
                    $('body').addClass('slidebar-left-visible');
                    foodcoopshop.Mobile.changeToogleIcon(true);
                } else {
                    $('body').removeClass('slidebar-left-visible');
                    foodcoopshop.Mobile.changeToogleIcon(false);
                }
            });
        });
    },

    bindCloseSlidebarsOnCanvasClick : function (controller) {
        $(controller.events).on('opened', function (event, id) {
            $('html').on('click', function () {
                controller.close(id);
                foodcoopshop.Mobile.changeToogleIcon(false);
            });
            $('.sb-slidebar > *').on('click', function (event) {
                event.stopPropagation();
            });
        });
    },

    fixContentScrolling : function () {
        $('body').css('overflow-y', 'auto');
    },

    autoOpenSidebarLeft : function () {
        $('.sb-toggle-left').trigger('click');
    },

    initMenusAdmin: function () {

        $('#container').after(this.getSlidebarMenu('left')).attr('canvas', '');

        var menuItems = [];

        $('#menu > li').each(function () {
            var item = $(this);
            item.find('a').removeClass('btn');
            item.find('a').removeClass('btn-success');
            menuItems.push(item);
        });

        $('.sb-left').html(menuItems);

        var navBarContainer = $('.filter-container');
        navBarContainer.prepend(this.getResponsiveMenuButton());
        $('#container').prepend(navBarContainer);

        $(window).bind('resize', function () {
            foodcoopshop.Admin.adaptContentMargin();
        });

        var controller = new slidebars();
        controller.init();

        this.bindToggleLeft(controller);
        this.bindCloseSlidebarsOnCanvasClick(controller);
        this.fixContentScrolling(controller);

        foodcoopshop.Helper.showContent();

    },

    showSelfServiceCart : function() {
        $('.right-box').show();
        $('#products').hide();
    },

    hideSelfServiceCart : function() {
        $('.right-box').hide();
        $('#products').show();
    },

    initMenusSelfService: function() {

        $('.self-service').after(this.getSlidebarMenu('left')).attr('canvas', '');

        var menuItems = [];
        var ps = $('.product-search-form-wrapper');
        menuItems.push(ps.wrap('<li>').parent());

        var pageItems = [];
        $('.footer a:not(.not-in-moblie-menu)').each(function () {
            $(this).removeClass('btn');
            $(this).removeClass('btn-success');
            $(this).find('i').remove();
            $(this).html($(this).html().trim());
            $(this).prepend($('<i/>').addClass('fa'));
            var newItem = $('<li/>').append($(this));
            pageItems.push(newItem);
        });
        pageItems = pageItems.reverse();
        menuItems = $.merge(menuItems, pageItems);

        $('.sb-left').html(menuItems);

        var headerId = 'responsive-header';
        var responsiveHeader = $('<div/>');
        responsiveHeader.attr('id', headerId);
        responsiveHeader.attr('canvas', '');
        $('body').prepend(responsiveHeader);

        $('#' + headerId).append(this.getResponsiveMenuButton());

        $('#' + headerId).append($('.footer .right-wrapper .btn-add-deposit .self-service-wrapper'));
        $('.footer .left-wrapper').remove();

        var cartButtonHtml = '<a href="javascript:void(0);" class="responsive-cart"><span class="sum">' + foodcoopshop.Helper.formatFloatAsCurrency(0) + '</span><i class="fas fa-shopping-bag fa-2x"></i></a>';
        $('#' + headerId).append(cartButtonHtml);
        $('#' + headerId).find('.responsive-cart').on('click', function() {
            if ($('.right-box').css('display') == 'block') {
                foodcoopshop.Mobile.hideSelfServiceCart();
            } else {
                foodcoopshop.Mobile.showSelfServiceCart();
            }
        });

        // button renaming
        $('.ew .btn').html('<i class="fa fa-lg fa-fw fa-shopping-bag"></i>');

        // move flash message into header
        $('#' + headerId).append($('#flashMessage'));

        var controller = new slidebars();
        controller.init();

        this.bindToggleLeft(controller);
        this.bindCloseSlidebarsOnCanvasClick(controller);
        this.fixContentScrolling();

        $('#content').show();

    },

    initMenusFrontend: function () {

        $('#container').after(this.getSlidebarMenu('left')).attr('canvas', '');

        var menuItems = [];

        var ps = $('.product-search-form-wrapper');
        if (ps.length > 0) {
            menuItems.push(ps.wrap('<li>').parent());
        }

        let homeMenuItemA = $('<a/>').attr('href', '/').html('<i class="fas"></i>' + foodcoopshop.LocalizedJs.mobile.home);
        menuItems.push('<li class="home">' + $('<div>').append(homeMenuItemA.clone()).html()  + $('<div>').append($('.color-mode-toggle')).html() + '</li>');

        $('#user-menu > li').each(function () {
            var item = $(this);
            if (item.find('a').length > 0) {
                item.find('a').removeClass('btn');
                item.find('a').removeClass('btn-success');
                menuItems.push(item);
            }
        });

        // if all manufacturers are disabled / set to private - do not include menu item
        $('.sidebar li.header').each(function () {
            if ($(this).html() == foodcoopshop.LocalizedJs.mobile.manufacturers) {
                menuItems.push('<li><a href="/' + foodcoopshop.LocalizedJs.mobile.routeManufacturerList + '"><i class="fa"></i>' + foodcoopshop.LocalizedJs.mobile.manufacturers + '</a></li>');
            }
        });

        $('.sidebar ul#categories-menu > li').each(function () {
            menuItems.push($(this).clone());
        });

        var pageItems = ['<li class="header">' + foodcoopshop.LocalizedJs.mobile.pages + '</li>'];
        $('#main-menu > li').each(function () {
            // take categories and manufacturers from sidebar and not from main menu
            var mainMenuHref = $(this).find('a').attr('href');
            if ($.inArray(mainMenuHref, ['/' + foodcoopshop.LocalizedJs.mobile.routeManufacturerList, foodcoopshop.LocalizedJs.mobile.routeAllCategories, '/' + foodcoopshop.LocalizedJs.mobile.routeNewsList]) == -1) {
                pageItems.push($(this));
            }
        });

        $('#footer-menu > li').each(function () {
            pageItems.push($(this));
        });

        if (pageItems.length > 1) {
            menuItems = $.merge(menuItems, pageItems);
        }

        $('.sb-left').html(menuItems);

        var headerId = 'responsive-header';
        var responsiveHeader = $('<div/>');
        responsiveHeader.attr('id', headerId);
        responsiveHeader.attr('canvas', '');
        $('body').prepend(responsiveHeader);

        $('#' + headerId).append(this.getResponsiveMenuButton());

        // START info box as modal
        var noGlobalDeliveryBreakHtml = '';
        var noGlobalDeliveryBreakElement = $('#global-no-delivery-day-box');
        if (noGlobalDeliveryBreakElement.length > 0) {
            noGlobalDeliveryBreakHtml = noGlobalDeliveryBreakElement.html();
        }
        var infoBoxContent = (noGlobalDeliveryBreakHtml + $('#info-box').html()).trim();
        if (infoBoxContent != '') {
            var infoBoxHtml = '<div id="right-info-box-text" class="hide">' + infoBoxContent + '</div>';
            infoBoxHtml = infoBoxHtml.replace(/h3/g, 'h1');
            $('#container').append(infoBoxHtml);

            var infoButton = $('<a/>');
            infoButton.addClass('open-with-modal');
            infoButton.attr('href', 'javascript:void(0);');
            infoButton.data('element-selector', '#right-info-box-text');
            infoButton.html('<i class="fas fa-info-circle fa-2x"></i>');
            $('#' + headerId).append(infoButton);
            foodcoopshop.ModalText.init('#' + headerId + ' a.open-with-modal');
        }
        // END info box as modal

        var cartButton = $('#cart .inner .btn-success');
        cartButton.addClass('responsive-cart');
        cartButton.removeClass('btn btn-success');
        cartButton.html('<span class="sum">' + foodcoopshop.Helper.formatFloatAsCurrency(0) + '</span><i class="fas fa-shopping-cart fa-2x fa-fw"></i>');
        $('#' + headerId).append(cartButton);

        $('#' + headerId).append($('#header .logo-wrapper'));

        // button renamings
        var regexp = new RegExp(foodcoopshop.LocalizedJs.mobile.showAllProducts);
        $('.manufacturer-wrapper div.c3 a.btn').each(function () {
            $(this).html($(this).html().replace(regexp, foodcoopshop.LocalizedJs.mobile.show));
        });
        $('.blog-post-wrapper div.c3 a.btn').html(foodcoopshop.LocalizedJs.mobile.show);
        $('.ew .btn').each(function() {
            if (!$(this).find('i').hasClass('fa-times')) { // delivery break?
                $(this).html($(this).find('i').after($(this).text()));
            }
        });
        $('#cart .btn-success').html('<i class="fas fa-shopping-cart"></i>');

        // add special infos to cart page
        var cartPage = $('body.carts #inner-content h1:first');
        cartPage.after($('#cart p.cart-extra-info'));
        cartPage.after($('#cart div.credit-balance-wrapper'));
        cartPage.after($('#cart p.future-orders'));

        var loadLastOrderDetailsDropdown = $('#cart .inner #load-last-order-details');
        if (loadLastOrderDetailsDropdown.length > 0) {
            cartPage.after(loadLastOrderDetailsDropdown.closest('div.input'));
        }

        // move flash message into header
        $('#' + headerId).append($('#flashMessage'));

        var controller = new slidebars();
        controller.init();

        this.bindToggleLeft(controller);
        this.bindCloseSlidebarsOnCanvasClick(controller);
        this.fixContentScrolling();

        foodcoopshop.Helper.showContent();

    }

};
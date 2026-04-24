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

        $('#' + headerId).append($('.footer .right-wrapper .btn-add-deposit'));
        $('.footer .left-wrapper').remove();

        var cartButtonHtml = '<a href="javascript:void(0);" class="responsive-cart modal-link-cart"><i class="fas fa-shopping-bag fa-2x"></i><span class="menu-item-label">' + foodcoopshop.Helper.formatFloatAsCurrency(0) + '</span></a>';
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

        var ps = $('#user-menu > li.user-menu-search');
        if (ps.length > 0) {
            menuItems.push(ps);
        }

        let homeMenuItemA = $('<a/>').attr('href', '/').html('<i class="fas"></i>' + __('Home'));
        menuItems.push('<li class="home">' + $('<div>').append(homeMenuItemA.clone()).html() + $('<div>').append($('a.color-mode-toggle')).html() + '</li>');

        $('#user-menu > li').each(function () {
            var item = $(this);
            if (item.hasClass('user-menu-search')) {
                return;
            }
            let anchor = item.find('a');
            if (!anchor.hasClass('modal-link-info-box') && !anchor.hasClass('modal-link-cart') && !anchor.hasClass('color-mode-toggle') && anchor.length > 0) {
                anchor.removeClass('btn');
                anchor.removeClass('btn-success');
                menuItems.push(item);
            }
        });

        // if all manufacturers are disabled / set to private - do not include menu item
        $('.sidebar li.header').each(function () {
            if ($(this).html() == __('Manufacturers')) {
                menuItems.push('<li><a href="/' + __('route_manufacturer_list') + '"><i class="fa"></i>' + __('Manufacturers') + '</a></li>');
            }
        });

        $('.sidebar ul#filters-menu > li').each(function () {
            menuItems.push($(this).clone());
        });

        $('.sidebar ul#categories-menu > li').each(function () {
            menuItems.push($(this).clone());
        });

        var pageItems = ['<li class="header">' + __('Pages') + '</li>'];
        $('#main-menu > li').each(function () {
            // take categories and manufacturers from sidebar and not from main menu
            var mainMenuHref = $(this).find('a').attr('href');
            if ($.inArray(mainMenuHref, ['/' + __('route_manufacturer_list'), foodcoopshop.config.routeAllCategories, '/' + __('route_news_list')]) == -1) {
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

        $('#user-menu > li').each(function () {
            if ($(this).find('a').hasClass('modal-link-info-box')) {
                let anchor = $(this).find('a');
                let icon = anchor.find('i');
                icon.addClass('fa-2x');
                icon.removeClass('ok');
                anchor.html(icon);
                $('#' + headerId).append($(this));
                foodcoopshop.ModalText.init('#' + headerId + ' a.modal-link-info-box');
            }
            if ($(this).find('a').hasClass('modal-link-cart')) {
                let modifiedCartButton = $(this).clone();
                let anchor = modifiedCartButton.find('a');
                let icon = anchor.find('i');
                anchor.attr('href', foodcoopshop.LocalizedJs.admin.routeCartShow);
                anchor.addClass('responsive-cart');
                icon.addClass('fa-2x');
                icon.removeClass('ok');
                $('#' + headerId).append(modifiedCartButton);
            }
        });

        $('#' + headerId).append($('#header .logo-wrapper'));

        // button renamings
        var regexp = new RegExp(__('Show_all_products'));
        $('.manufacturer-wrapper div.c3 a.btn').each(function () {
            $(this).html($(this).html().replace(regexp, __('Show')));
        });
        $('.blog-post-wrapper div.c3 a.btn').html(__('Show'));
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

        var loadLastOrderDetailsDropdown = $('#cart .inner .load-last-order-details');
        if (loadLastOrderDetailsDropdown.length > 0) {
            cartPage.after(loadLastOrderDetailsDropdown.closest('div.input'));
            foodcoopshop.ModalLoadLastOrderDetails.init();
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
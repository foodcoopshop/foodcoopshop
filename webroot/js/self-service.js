/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.SelfService = {

    autoLogoutTimer : 12000,
    currentLogoutTimer : 0,
        
    init : function() {
        foodcoopshop.Helper.initLogoutButton(document.location.href);
        this.initWindowScroll();
        this.initAutoLogout();
        this.initSearchForm();
    },
    
    initSearchForm : function() {
        var container = $('#self-service');
        foodcoopshop.Helper.initBootstrapSelect(container);
        container.find('select, input[type="text"]').on('change', function() {
            var searchForm = $('#product-search');
            var submitButton = searchForm.find('.btn[type="submit"]');
            foodcoopshop.Helper.addSpinnerToButton(submitButton, 'fa-search');
            foodcoopshop.Helper.disableButton(submitButton);
            searchForm.submit();
        });
    },
    
    initCartErrors: function (cartErrors) {
        cartErrors = $.parseJSON(cartErrors);
        console.log(cartErrors);
        for (var key in cartErrors) {
            var productContainer = $('#cart .product.' + key);
            productContainer.addClass('error');
            productContainer.after('<ul class="error-message ' + key + '"><li>' + cartErrors[key].join('</li><li>') + '</li></ul>');
        }
    },
    
    initWindowScroll: function () {
        $(window).scroll(function () {
            foodcoopshop.SelfService.onWindowScroll();
        });
        foodcoopshop.SelfService.onWindowScroll();
    },
    
    onWindowScroll : function() {
        $('#cart p.products').css('max-height', parseInt($(window).height()) - 220);        
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
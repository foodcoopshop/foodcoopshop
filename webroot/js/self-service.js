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

    autoLogoutTimer : 600,
    currentLogoutTimer : 0,
        
    init : function() {
        foodcoopshop.Helper.initLogoutButton();
        this.initAutoLogout();
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
                    document.location.href = '/' + foodcoopshop.LocalizedJs.helper.routeLogout;
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
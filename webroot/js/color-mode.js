/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

foodcoopshop.ColorMode = {

    init: function() {
        var colorMode = localStorage.getItem('color-mode');
        if (colorMode === 'dark') {
            this.enableDarkMode();
        } else {
            this.enableLightMode();
        }
        this.setBackgroundImage();
    },

    setBackgroundImage: function() {
        var colorMode = this.getColorMode();
        $('body').css('background-image', 'url("' + foodcoopshop.BackgroundImage.getBackgroundImage(colorMode) + '")');
    },

    initToggle: function() {
        $('.color-mode-toggle').on('click', function() {
            if ($('body').hasClass('dark')) {
                localStorage.setItem('color-mode', 'light');
                foodcoopshop.ColorMode.enableLightMode();
            } else {
                localStorage.setItem('color-mode', 'dark');
                foodcoopshop.ColorMode.enableDarkMode();
            }
            foodcoopshop.ColorMode.setBackgroundImage();
        });
    },

    enableLightMode: function() {
        $('body').removeClass('dark');
        var icon = $('.color-mode-toggle').find('i');
        icon.removeClass('fas');
        icon.addClass('far');
    },

    enableDarkMode: function() {
        $('body').addClass('dark');
        var icon = $('.color-mode-toggle').find('i');
        icon.removeClass('far');
        icon.addClass('fas');
    },

    getColorMode: function() {
        return $('body').hasClass('dark') ? 'dark' : 'light';
    }

};

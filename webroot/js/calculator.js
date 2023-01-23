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

foodcoopshop.Calculator = {

    init: function(container) {

        let calculatorToggleButton = $(container).find('.calculator-toggle-button');
        let calculatorInput = $(container).find('.calculator-input');

        $(calculatorToggleButton).on('click', function (e) {
            let calculatorInput = $(this).closest(container).find('.calculator-input');
            if (calculatorInput.css('display') == 'none') {
                calculatorInput.show();
                calculatorInput.focus();
            } else {
                calculatorInput.hide();
            }
        });

        $(calculatorInput).on('keyup', function (e) {
            try {
                let calculatorOutput = $(this).closest(container).find('.calculator-output');
                let inputVal = $(this).val();
                if (foodcoopshop.LocalizedJs.helper.defaultLocale != 'en_US') {
                    inputVal = inputVal.replace(/,/g, '.');
                }
                let newValue = math.evaluate(inputVal);
                newValue = math.format(newValue, {precision: 14}); // 0,7+0,6 = 1,2999999
                calculatorOutput.val(newValue);
            } catch(e) {
                console.log('error in expression');
            }
        });
    }

};

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

/**
 * JavaScript counterparts of CakePHP's __() and __d() translation functions.
 *
 * Translations are looked up against window.foodcoopshop.translations[domain],
 * which is generated server-side from the loaded gettext catalogs.
 *
 * Placeholder substitution supports CakePHP's {0}, {1}, ... numeric tokens.
 * If the msgid is not found, the msgid itself is used as the fallback string,
 * matching CakePHP's behaviour.
 *
 *   __('Save')
 *   __('You_have_already_ordered_{0}_{1}_times_for_{2}.', amount, name, day)
 *   __d('admin', 'Place_order_for')
 */
(function (root) {
    'use strict';

    var ns = root.foodcoopshop = root.foodcoopshop || {};
    ns.translations = ns.translations || {};

    function lookup(domain, msgid) {
        var dict = ns.translations[domain];
        if (dict && Object.prototype.hasOwnProperty.call(dict, msgid)) {
            var value = dict[msgid];
            if (value !== '' && value !== null && value !== undefined) {
                return value;
            }
        }
        return msgid;
    }

    function applyPlaceholders(str, args) {
        if (!args.length) {
            return str;
        }
        for (var i = 0; i < args.length; i++) {
            str = str.split('{' + i + '}').join(String(args[i]));
        }
        return str;
    }

    function translate(domain, msgid, args) {
        return applyPlaceholders(lookup(domain, msgid), args);
    }

    root.__ = function (msgid) {
        var args = Array.prototype.slice.call(arguments, 1);
        return translate('default', msgid, args);
    };

    root.__d = function (domain, msgid) {
        var args = Array.prototype.slice.call(arguments, 2);
        return translate(domain, msgid, args);
    };

})(typeof window !== 'undefined' ? window : this);

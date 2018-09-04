/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.SyncProduct = {

    isAttribute : function (product) {
        return product.row_class.match(/sub-row/);
    },

    hasAttributes : function (product) {
        var hasAttributes = false;
        if (product.product_attributes && product.product_attributes.length > 0) {
            hasAttributes = true;
        }
        return hasAttributes;
    },

    getProductNameWithUnity : function (product, isAttribute, hasAttributes) {
        var productName = product.unchanged_name;
        if (!isAttribute && !hasAttributes && product.unity != '') {
            productName += ': <span class="unity">' + product.unity + '</span>';
        } else {
            productName = product.name;
        }
        return productName;
    }

};
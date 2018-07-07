/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

Object.defineProperty(
    Object.prototype,
    'renameProperty',
    {
        enumerable: false,
        value: function (oldName, newName) {
            // Do nothing if the names are the same
            if (oldName == newName) {
                return this;
            }
            // Check for the old property name to avoid a ReferenceError in strict mode.
            if (this.hasOwnProperty(oldName)) {
                this[newName] = this[oldName];
                delete this[oldName];
            }
            return this;
        }
    }
);

foodcoopshop.SyncLegacyHelper = {

    transformResponse : function (products) {

        for (var product of products) {
            // FCS v1.x returns property Product
            if (product.Product) {
                product.active = product.Product.active;
                product.gross_price = product.Product.gross_price;
                product.id_manufacturer = product.Product.id_manufacturer;
                product.id_product = product.Product.id_product;
                product.id_tax = product.Product.id_tax;
                product.is_new = product.Product.is_new;
                product.row_class = product.Product.rowClass;
                product.renameProperty('StockAvailable', 'stock_available');
                product.renameProperty('Deposit', 'deposit');
                product.renameProperty('ProductAttributes', 'product_attributes');
                product.renameProperty('ProductLang', 'product_lang');
            }
        }

        return products;
    }

};

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ShoppingListPage = {

    isActive: false,

    shoppingListId: null,

    // composite product id (eg. "12" or "12-3") => amount
    amounts: {},

    /**
     * the amount fields are preset with the amounts of the list
     * if multiple attributes of a product are on the list, they are marked and listed in the product card
     */
    init: function (shoppingListId, amountsByProductId) {
        this.isActive = true;
        this.shoppingListId = shoppingListId;
        this.amounts = amountsByProductId;

        var mainProductIds = [];
        $.each(amountsByProductId, function (compositeProductId) {
            var mainProductId = String(compositeProductId).split('-')[0];
            if (mainProductIds.indexOf(mainProductId) === -1) {
                mainProductIds.push(mainProductId);
            }
        });
        $.each(mainProductIds, function (index, mainProductId) {
            foodcoopshop.ShoppingListPage.renderProduct(mainProductId, true);
        });
        this.initDeleteButton();

        // heart buttons depend on the selected attribute
        foodcoopshop.ModalShoppingList.updateButtons();
    },

    initDeleteButton: function () {
        $('#modal-shopping-list-edit .btn-delete-shopping-list').on('click', function () {
            if (confirm($(this).data('confirm-message'))) {
                $('#shopping-list-delete-form').trigger('submit');
            }
        });
    },

    getProductWrapper: function (mainProductId) {
        return $('.pw .ew input[name="productId"]').filter(function () {
            return $(this).val().split('-')[0] === String(mainProductId);
        }).first().closest('.pw');
    },

    /**
     * @param selectFirstSavedAttribute only on page load, later the attribute selected by the user is kept
     */
    renderProduct: function (mainProductId, selectFirstSavedAttribute) {

        var productWrapper = this.getProductWrapper(mainProductId);
        if (productWrapper.length === 0) {
            return;
        }

        var savedAttributes = [];
        $.each(this.amounts, function (compositeProductId, amount) {
            var ids = String(compositeProductId).split('-');
            if (ids[0] !== String(mainProductId)) {
                return;
            }
            // every attribute has its own amount field
            productWrapper.find('.ew input[name="productId"]').filter(function () {
                return $(this).val() === String(compositeProductId);
            }).closest('.ew').find('input[name="amount"]').val(amount);
            if (ids.length === 2 && $('#attribute-button-' + ids[1]).length > 0) {
                savedAttributes.push({ attributeId: ids[1], amount: amount });
            }
        });

        productWrapper.find('.shopping-list-variants-info').remove();
        productWrapper.find('.attribute-button').removeClass('is-on-shopping-list');

        if (savedAttributes.length === 0) {
            return;
        }

        if (selectFirstSavedAttribute) {
            var firstAttributeButton = $('#attribute-button-' + savedAttributes[0].attributeId);
            firstAttributeButton.find('input[type="radio"]').prop('checked', true);
            firstAttributeButton.trigger('click');
        }

        var infoItems = [];
        $.each(savedAttributes, function (index, savedAttribute) {
            var attributeButton = $('#attribute-button-' + savedAttribute.attributeId);
            attributeButton.addClass('is-on-shopping-list');
            infoItems.push(
                $('<span class="shopping-list-variant"></span>')
                    .append($('<b></b>').text(attributeButton.text().trim()))
                    .append(document.createTextNode(' (' + savedAttribute.amount + 'x)'))
                    .prop('outerHTML')
            );
        });

        var info = $('<div class="shopping-list-variants-info"></div>')
            .append('<i class="fas fa-heart"></i> ')
            .append(document.createTextNode(__('On_this_shopping_list') + ': '))
            .append(infoItems.join(', '));
        productWrapper.find('.radio').last().after(info);
    },

    /**
     * updates the product card after saving in the modal (no page reload)
     */
    update: function (compositeProductId, shoppingLists) {

        var currentList = shoppingLists.find(function (shoppingList) {
            return shoppingList.id === foodcoopshop.ShoppingListPage.shoppingListId;
        });
        if (currentList === undefined) {
            return;
        }

        if (currentList.amount === null) {
            delete this.amounts[compositeProductId];
        } else {
            this.amounts[compositeProductId] = currentList.amount;
        }

        var mainProductId = String(compositeProductId).split('-')[0];
        var isProductStillOnList = Object.keys(this.amounts).some(function (id) {
            return id.split('-')[0] === mainProductId;
        });

        if (isProductStillOnList) {
            this.renderProduct(mainProductId, false);
        } else {
            this.getProductWrapper(mainProductId).fadeOut(300, function () {
                $(this).remove();
            });
        }

        if (Object.keys(this.amounts).length === 0) {
            $('.shopping-list-empty').removeClass('hide');
            $('.btn-load-shopping-list-into-cart').addClass('hide');
        }
    }

};

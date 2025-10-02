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
foodcoopshop.Cart = {

    orderButtons: '.cart .btn-success.btn-order, .responsive-cart',
    disabledButtonsDuringUpdateCartRequest: '.btn-cart-detail, .btn-order, .btn-cart:not(.disabled), .delete .btn, .amount .btn',
    cartButtonIcon : '',
    productIdSelector : 'input[name="productId"]',

    initAttributesDropdown: function () {
        
        $('.pw .units-wrapper select').on('click', function (e) {
            e.stopPropagation();
        });

        $('.pw .units-wrapper select').on('change', function (e) {
            var selectedCompositeProductAttributeId = $(this).val();
            var productWrapper = $(this).closest('.pw');
            var attributeWrappers = productWrapper.find('.attribute-wrapper');
            attributeWrappers.removeClass('active');
            attributeWrappers.filter('.attribute-wrapper-' + selectedCompositeProductAttributeId).addClass('active');
            productWrapper.find(foodcoopshop.Cart.productIdSelector).val(selectedCompositeProductAttributeId);
        });
    },

    getPickupDayHeaderSelector : function(pickupDay) {
        return '.cart p.pickup-day-header:contains("' + pickupDay + '")';
    },

    setCartButtonIcon : function(cartButtonIcon) {
        this.cartButtonIcon = cartButtonIcon;
    },

    initCartFinish: function () {
        $('button.btn-order').on('click', function () {
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
            $(this).closest('form').submit();
        });
    },

    scrollToCartFinishButton: function() {
        setTimeout(function() {
            $('body,html').animate({
                scrollTop: $('button.btn-order').offset().top - $(window).height() + 50
            }, 400);
        }, 500);
    },

    initCartErrors: function (cartErrors) {
        cartErrors = $.parseJSON(cartErrors);
        for (var key in cartErrors) {
            var container;
            var errorMessageString = '<ul class="error-message ' + key + '"><li>' + cartErrors[key].join('</li><li>') + '</li></ul>';
            if (key == 'global') {
                container = $('.carts .cart:not(#cart) #CartsDetailForm');
                container.addClass('error');
                container.prepend(errorMessageString);
            } else {
                container = $('.carts .cart:not(#cart) .product.' + key);
                container.addClass('error');
                container.after(errorMessageString);
            }
        }
    },

    updateExistingProduct: function (productContainer, amount, price, deposit, tax, orderedQuantityInUnits, unitName) {

        // update amount, but not if a product with price per unit is added
        if (orderedQuantityInUnits === undefined) {
            var oldAmount = productContainer.find('span.amount span.value');
            var oldAmountValue = parseInt(oldAmount.html());
            var newAmount = oldAmountValue + parseInt(amount);
            oldAmount.html(newAmount);
            foodcoopshop.Helper.applyBlinkEffect(oldAmount);
        }

        // update unity
        var oldUnity = productContainer.find('span.unity');
        var newUnityHtml = oldUnity.html();

        if (newAmount > 1 && oldAmountValue == 1) {
            var approxRegExp = new RegExp(foodcoopshop.LocalizedJs.cart.approx);
            newUnityHtml = newUnityHtml.replace(approxRegExp, foodcoopshop.LocalizedJs.cart.forEach + ' ' + foodcoopshop.LocalizedJs.cart.approx);
        }
        if (newAmount == 1 && oldAmountValue > 1) {
            var forEachApproxRegExp = new RegExp(foodcoopshop.LocalizedJs.cart.forEach + ' ' + foodcoopshop.LocalizedJs.cart.approx);
            newUnityHtml = newUnityHtml.replace(forEachApproxRegExp, foodcoopshop.LocalizedJs.cart.approx);
        }

        if (orderedQuantityInUnits > 0) {
            newUnityHtml = foodcoopshop.Helper.getStringAsFloat(oldUnity.html()) + orderedQuantityInUnits;
            newUnityHtml = newUnityHtml.toLocaleString();
            newUnityHtml += ' ' + unitName;
        }

        if (newUnityHtml != oldUnity.html()) {
            oldUnity.html(newUnityHtml);
            foodcoopshop.Helper.applyBlinkEffect(oldUnity);
        }

        // update price
        var oldPrice = productContainer.find('span.price');
        var tmpNewPrice = price;

        if (orderedQuantityInUnits > 0) {
            tmpNewPrice = foodcoopshop.Cart.getPriceBasedOnPricePerUnit(
                foodcoopshop.Helper.getCurrencyAsFloat(productContainer.find('span.price-incl-per-unit').html()),
                orderedQuantityInUnits,
                foodcoopshop.Helper.getStringAsFloat(productContainer.find('span.unit-amount').html())
            );
        }

        var newPrice = foodcoopshop.Helper.getCurrencyAsFloat(oldPrice.html()) + tmpNewPrice;
        oldPrice.html(foodcoopshop.Helper.formatFloatAsCurrency(newPrice));
        foodcoopshop.Helper.applyBlinkEffect(oldPrice);

        // update deposit
        var oldDeposit = productContainer.find('.deposit span');
        if (oldDeposit.length > 0) {
            var newDeposit = foodcoopshop.Helper.getCurrencyAsFloat(oldDeposit.html()) + deposit;
            oldDeposit.html(foodcoopshop.Helper.formatFloatAsCurrency(newDeposit));
            foodcoopshop.Helper.applyBlinkEffect(oldDeposit);
        }

        // update tax
        var oldTax = productContainer.find('span.tax');
        var newTax = (
            foodcoopshop.Helper.getCurrencyAsFloat(oldTax.html()) +
            (tax * amount)
        );
        oldTax.html(foodcoopshop.Helper.formatFloatAsCurrency(newTax));

    },

    getPriceBasedOnPricePerUnit : function(priceInclPerUnit, orderedQuantityInUnits, unitAmount) {
        return priceInclPerUnit * orderedQuantityInUnits / unitAmount;
    },

    renderCart: function(cart) {
    
        if (typeof cart === 'string') {
            cart = $.parseJSON(cart);
        }
        
        let cartProductsHtml = '';

        Object.entries(cart.CartProducts).map(function([formattedPickupDay, cartProducts]) {
            cartProductsHtml += '<p class="pickup-day-header">';
            cartProductsHtml += '<span class="label">' + foodcoopshop.LocalizedJs.cart.PickupDay + ': <b>' + formattedPickupDay + '</b></span>';
            cartProducts.Products.map(function(product) {
                cartProductsHtml += foodcoopshop.Cart.getCartProductHtml(product.productId, product.amount, product.price, product.productName, product.unity_with_unit, product.manufacturerLink, product.image, product.deposit, product.tax, product.orderedQuantityInUnits, product.unitName, product.unitAmount, product.priceInclPerUnit, formattedPickupDay);
            });
            cartProductsHtml += '</p>';
        });
        $('.cart p.products').html(cartProductsHtml);
        foodcoopshop.Cart.initRemoveFromCartLinks();

        let cartSumsHtml = '';
        if (cart.CartDepositSum != 0) {
            cartSumsHtml += '<p class="product-sum-wrapper" style="display: block;"><b>' + foodcoopshop.LocalizedJs.cart.valueOfGoods + '</b><span class="sum">' + foodcoopshop.Helper.formatFloatAsCurrency(cart.CartProductSum) + '</span></p>';
            cartSumsHtml += '<p class="deposit-sum-wrapper" style="display: block;"><b>+ ' + foodcoopshop.LocalizedJs.cart.depositSum + '</b><span class="sum">' + foodcoopshop.Helper.formatFloatAsCurrency(cart.CartDepositSum) + '</span></p>';
        }
        
        cartSumsHtml += '<p class="total-sum-wrapper">';
            cartSumsHtml += '<b class="amount-sum-wrapper" style="display: inline;"><span class="sum"><span class="value">' + cart.CartAmountSum + '</span>x</span></b>';
            cartSumsHtml += '<b> ' + foodcoopshop.LocalizedJs.cart.total + '</b><span class="sum">' + foodcoopshop.Helper.formatFloatAsCurrency(cart.CartProductSum + cart.CartDepositSum) + '</span>';
        cartSumsHtml += '</p>';

        cartSumsHtml += '<p class="tax-sum-wrapper">' + foodcoopshop.LocalizedJs.cart.includingVat + ': <span class="sum">' + foodcoopshop.Helper.formatFloatAsCurrency(cart.CartTaxSum) + '</span></p>';

        $('.cart div.sums-wrapper').html(cartSumsHtml);

        if (cart.CartProducts.length == 0) {
            $('.cart p.no-products').show();
        } else {
            $('.cart p.products').show();
        }

        foodcoopshop.ModalImage.init('.cart .products a.image');
        foodcoopshop.Helper.onWindowResize();

    },

    initAddToCartButton: function () {

        // if delivery break is enabled, disable button
        $('.pw a.btn.btn-cart').each(function() {
            if($(this).hasClass('disabled')) {
                foodcoopshop.Helper.disableButton($(this));
            }
        });

        $('.pw a.btn.btn-cart').on('click', function (e) {

            e.stopPropagation();

            foodcoopshop.Helper.removeFlashMessage();
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), foodcoopshop.Cart.cartButtonIcon);
            foodcoopshop.Helper.disableButtonWithoutChangingStyle($(foodcoopshop.Cart.orderButtons));

            $('#cart p.no-products').hide();
            $('#cart p.products').show();

            const productWrapper = $(this).closest('.pw');
            const productId = productWrapper.find(foodcoopshop.Cart.productIdSelector).val();
            const amount = 1;
            const orderedQuantityInUnits = 0;
            var button = productWrapper.find('.btn-cart');

            var disabledButtonsDuringUpdateCartRequest = $(foodcoopshop.Cart.disabledButtonsDuringUpdateCartRequest);
            foodcoopshop.Helper.disableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);

            foodcoopshop.Helper.ajaxCall(
                '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/ajaxAdd/',
                {
                    productId: productId,
                    amount: amount,
                    orderedQuantityInUnits: orderedQuantityInUnits > 0 ? orderedQuantityInUnits : -1
                },
                {
                    onOk: function (data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.enableButtonWithoutChangingStyle($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.removeSpinnerFromButton(button, foodcoopshop.Cart.cartButtonIcon);
                        foodcoopshop.Helper.enableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);
                        foodcoopshop.Cart.renderCart(data.updatedCart);
                        if (data.callback) {
                            eval(data.callback);
                        }
                        foodcoopshop.Helper.onWindowResize();
                    },
                    onError: function (data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.enableButtonWithoutChangingStyle($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.removeSpinnerFromButton(button, foodcoopshop.Cart.cartButtonIcon);
                        foodcoopshop.Helper.enableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);
                        foodcoopshop.Cart.initRemoveFromCartLinks();
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                        if (data.callback) {
                            eval(data.callback);
                        }
                        foodcoopshop.Helper.onWindowResize();
                    }
                }
            );

        });

    },

    initChangeAmountLinks: function () {

        var cartInPageAmountWrapper = $('#inner-content .cart .products span.amount');

        cartInPageAmountWrapper.each(function () {
            $(this).append(
                $('<a />').html('<i class="fas fa-plus-circle"></i>').attr('class', 'btn').attr('href', 'javascript:void(0);')
            ).prepend(
                $('<a />').html('<i class="fas fa-minus-circle"></i>').attr('class', 'btn').attr('href', 'javascript:void(0);')
            );
            var amount = parseInt($(this).find('.value').html());
            if (amount < 2) {
                foodcoopshop.Helper.disableButton($(this).find('.fa-minus-circle').parent());
            }
        });

        cartInPageAmountWrapper.find('a').on('click', function () {

            var productId = $(this).closest('.product').data('product-id');
            var productContainer = $('.product.' + productId);
            var price = foodcoopshop.Helper.getCurrencyAsFloat(productContainer.find('.price').html());
            var tax = foodcoopshop.Helper.getCurrencyAsFloat(productContainer.find('.tax').html());
            var oldAmount = parseInt(productContainer.find('.amount span.value').html());
            var newTax = tax / oldAmount;

            var elementClass = $(this).find('i').attr('class');
            var amount = 1;
            if (elementClass.match(/minus/)) {
                amount = -1;
            }

            var depositContainer = productContainer.find('.deposit span');
            var newDeposit = 0;
            if (depositContainer.length > 0) {
                var deposit = foodcoopshop.Helper.getCurrencyAsFloat(depositContainer.html());
                newDeposit = deposit / oldAmount * amount;
            }

            var newPrice = price / oldAmount * amount;

            var button = $(this);
            foodcoopshop.Helper.disableButton(button);
            foodcoopshop.Helper.addSpinnerToButton(button, elementClass.replace(/fas /, ''));
            foodcoopshop.Helper.disableButton($(foodcoopshop.Cart.orderButtons));

            var disabledButtonsDuringUpdateCartRequest = $(foodcoopshop.Cart.disabledButtonsDuringUpdateCartRequest);
            foodcoopshop.Helper.disableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);

            foodcoopshop.Helper.ajaxCall(
                '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/ajaxAdd/',
                {
                    productId: productId,
                    amount: amount
                },
                {
                    onOk: function (data) {
                        foodcoopshop.Helper.removeSpinnerFromButton(button, elementClass.replace(/fas /, ''));
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.enableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);
                        foodcoopshop.Cart.updateExistingProduct(productContainer, amount, newPrice, newDeposit, newTax);
                        var minusButton = productContainer.find('.fa-minus-circle').parent();
                        if (oldAmount == 2 && amount == -1) {
                            foodcoopshop.Helper.disableButton(minusButton);
                        } else {
                            foodcoopshop.Helper.enableButton(minusButton);
                        }
                    },
                    onError: function (data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.removeSpinnerFromButton(button, elementClass.replace(/fas /, ''));
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.enableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                    }
                }
            );

        });
    },

    getCartProductHtml: function (productId, amount, price, productName, unity, manufacturerLink, image, deposit, tax, orderedQuantityInUnits, unitName, unitAmount, priceInclPerUnit, pickupDay) {

        priceInclPerUnit = parseFloat(priceInclPerUnit);

        var imgHtml = '<span class="image">' + image + '</span>';
        if (!$(image).attr('src').match(/de-default-large/)) {
            imgHtml = '<a href="javascript:void(0);" data-modal-title="' + productName + '" data-modal-image="'  + $(image).attr('src').replace(/-large_/, '-thickbox_') +  '" class="image">' + image + '</a>';
        }
        var unityHtml = '<span class="unity">';
        if (orderedQuantityInUnits) {
            unityHtml += parseFloat(orderedQuantityInUnits).toLocaleString() + ' ' + unitName;
        } else {
            unityHtml += unity;
        }
        unityHtml += '</span>';
        var pricePerUnitHtml = '<span class="price-per-unit">';
        pricePerUnitHtml += '<span class="price-incl-per-unit">' + foodcoopshop.Helper.formatFloatAsCurrency(priceInclPerUnit) + '</span>';
        pricePerUnitHtml += '<span class="unit-amount">' + unitAmount + '</span>';
        pricePerUnitHtml += '<span class="unit-name">' + unitName + '</span>';
        pricePerUnitHtml += '</span>';
        return '<span data-product-id="' + productId + '" class="product' + ' ' + productId + '">' +
                imgHtml +
                '<span class="amount"><span class="value">' + amount + '</span>x</span>' +
                pricePerUnitHtml +
                '<span class="product-name-wrapper">' +
                    '<span class="product-name">' + productName + '</span>'+
                    unityHtml +
                    '<span class="pickup-day hide">' + pickupDay + '</span>' +
            '</span>' +
            '<span class="manufacturer-link">' + manufacturerLink + '</span>' +
            '<span class="right">' +
                '<span class="delete"><a class="btn" title="' + foodcoopshop.LocalizedJs.cart.removeFromCart + '" href="javascript:void(0);"><i class="fa-fw fas fa-times-circle"></i></a></span>' +
                '<span class="price">' + foodcoopshop.Helper.formatFloatAsCurrency(price) + '</span>' +
                (deposit != 0 ? '<span class="deposit">' + foodcoopshop.LocalizedJs.cart.deposit + ' + <span>' + foodcoopshop.Helper.formatFloatAsCurrency(deposit) + '</span></span>' : '') +
                '<span class="tax">' + foodcoopshop.Helper.formatFloatAsCurrency(tax) + '</span>' +
            '</span>' +
        '</span>';
    },

    /**
     * if current page is cart page, this function needs to update both #cart and cart in content area
     */
    initRemoveFromCartLinks: function () {

        $('.cart span.delete a').off('click').on('click', function () {

            var productId = $(this).closest('.product').data('product-id');
            foodcoopshop.Helper.disableButton($(foodcoopshop.Cart.orderButtons));

            var button = $(this);
            foodcoopshop.Helper.disableButton(button);
            foodcoopshop.Helper.addSpinnerToButton(button, 'fa-times-circle');

            var disabledButtonsDuringUpdateCartRequest = $(foodcoopshop.Cart.disabledButtonsDuringUpdateCartRequest);
            foodcoopshop.Helper.disableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);

            foodcoopshop.Helper.ajaxCall(
                '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/ajaxRemove/',
                {
                    productId: productId
                },
                {
                    onOk: function (data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.removeSpinnerFromButton(button);
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.enableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);
                        foodcoopshop.Cart.renderCart(data.updatedCart);
                        if (data.callback) {
                            eval(data.callback);
                        }
                    },
                    onError: function (data) {
                        $('.cart p.products .product.' + productId).addClass('error').remove();
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.removeSpinnerFromButton(button);
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                        foodcoopshop.Helper.enableButtonWithoutChangingStyle(disabledButtonsDuringUpdateCartRequest);
                        if (data.callback) {
                            eval(data.callback);
                        }
                    }
                }
            );

        });

    }

};

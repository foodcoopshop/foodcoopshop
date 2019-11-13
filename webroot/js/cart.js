/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.Cart = {

    orderButtons: '.cart .btn-success.btn-order, .responsive-cart',
    
    cartButtonIcon : '',
    
    getPickupDayHeaderSelector : function(pickupDay) {
        return '.cart p.pickup-day-header:contains("' + pickupDay + '")';
    },
    
    addOrAppendProductToPickupDay : function(productId, amount, price, productName, unity, manufacturerLink, image, deposit, tax, timebasedCurrencyHours, orderedQuantityInUnits, unitName, unitAmount, priceInclPerUnit, pickupDay) {
        var pickupDayHeader = $(this.getPickupDayHeaderSelector(pickupDay));
        if (pickupDayHeader.length == 0) {
            $('.cart p.products').append('<p class="pickup-day-header">' + foodcoopshop.LocalizedJs.cart.PickupDay + ': <b>' + pickupDay + '</b></p>');
            pickupDayHeader = $(this.getPickupDayHeaderSelector(pickupDay)); // re-init after append
        }
        pickupDayHeader.append(
            foodcoopshop.Cart.getCartProductHtml(productId, amount, price, productName, unity, manufacturerLink, image, deposit, tax, timebasedCurrencyHours, orderedQuantityInUnits, unitName, unitAmount, priceInclPerUnit, pickupDay)
        );
    },
    
    setCartButtonIcon : function(cartButtonIcon) {
        this.cartButtonIcon = cartButtonIcon;
    },

    /**
     * cart products already existed in database
     */
    initCartProducts: function (cartProducts) {

        cartProducts = $.parseJSON(cartProducts).reverse();
        if (cartProducts.length == 0) {
            return;
        }

        $('.cart p.no-products').hide();
        var sum = 0;
        var depositSum = 0;
        var taxSum = 0;
        var timebasedCurrencyHoursSum = 0;
        
        for (var i = 0; i < cartProducts.length; i++) {
            var cp = cartProducts[i];
            var timebasedCurrencyHours = parseFloat(cp.timebasedCurrencySeconds / 3600);
            this.addOrAppendProductToPickupDay(cp.productId, cp.amount, cp.price, cp.productName, cp.unity_with_unit, cp.manufacturerLink, cp.image, cp.deposit, cp.tax, timebasedCurrencyHours, cp.orderedQuantityInUnits, cp.unitName, cp.unitAmount, cp.priceInclPerUnit, cp.nextDeliveryDay);
            sum += cp.price;
            depositSum += cp.deposit;
            taxSum += cp.tax;
        }
        this.updateCartSum(sum);
        this.updateCartDepositSum(depositSum);
        this.updateCartTaxSum(taxSum);
        this.updateCartTimebasedCurrencySum(timebasedCurrencyHoursSum);

        foodcoopshop.Cart.initRemoveFromCartLinks(); // bind click event
        $('.cart p.products').show();

        foodcoopshop.AppFeatherlight.initLightboxForImages('.cart .products a.image');
        foodcoopshop.Helper.onWindowResize();

    },

    initCartFinish: function () {
        $('button.btn-order').on('click', function () {
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
            $(this).closest('form').submit();
        });
    },

    initCartErrors: function (cartErrors) {
        cartErrors = $.parseJSON(cartErrors);
        for (var key in cartErrors) {
            var productContainer = $('.carts.detail .cart:not(#cart) .product.' + key);
            productContainer.addClass('error');
            productContainer.after('<ul class="error-message ' + key + '"><li>' + cartErrors[key].join('</li><li>') + '</li></ul>');
        }
    },

    updateExistingProduct: function (productContainer, amount, price, deposit, tax, timebasedCurrencyHours, orderedQuantityInUnits, unitName, unitAmount, priceInclPerUnit) {

        // update amount
        var oldAmount = productContainer.find('span.amount span.value');
        var oldAmountValue = parseInt(oldAmount.html());
        var newAmount = oldAmountValue + parseInt(amount);
        oldAmount.html(newAmount);
        foodcoopshop.Helper.applyBlinkEffect(oldAmount);

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
            var newDeposit = (
                foodcoopshop.Helper.getCurrencyAsFloat(oldDeposit.html()) +
                (deposit * amount)
            );
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

        // update timebasedCurrencyHours
        var oldTimebasedCurrencyHours = productContainer.find('.timebasedCurrencySeconds');
        if (oldTimebasedCurrencyHours.length > 0) {
            var newTimebasedCurrencyHours =  (
                foodcoopshop.TimebasedCurrency.getTimebasedCurrencyAsFloat(oldTimebasedCurrencyHours.html()) +
                    (timebasedCurrencyHours * amount)
            );
            oldTimebasedCurrencyHours.html(foodcoopshop.TimebasedCurrency.formatFloatAsTimebasedCurrency(newTimebasedCurrencyHours));
        }
    },
    
    getPriceBasedOnPricePerUnit : function(priceInclPerUnit, orderedQuantityInUnits, unitAmount) {
        return priceInclPerUnit * orderedQuantityInUnits / unitAmount;
    },

    initAddToCartButton: function () {

        $('.product-wrapper a.btn.btn-cart').on('click', function () {

            foodcoopshop.Helper.removeFlashMessage();
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), foodcoopshop.Cart.cartButtonIcon);
            foodcoopshop.Helper.disableButton($(foodcoopshop.Cart.orderButtons));

            $('#cart p.no-products').hide();
            $('#cart p.products').show();

            var productWrapper = $(this).closest('.product-wrapper');
            var productName = '';
            // self service mode does not include product name as link
            var productAsLink = productWrapper.find('.heading h4 a');
            if (productAsLink.length > 0) {
                productName = productAsLink.html();
            } else {
                productName = productWrapper.find('.heading h4').html();
            }
            var amount = parseInt(productWrapper.find('.entity-wrapper.active input[name="amount"]').val());
            var price = foodcoopshop.Helper.getCurrencyAsFloat(productWrapper.find('.entity-wrapper.active .price').html());
            price = price * amount;
            
            var tax = foodcoopshop.Helper.getCurrencyAsFloat(productWrapper.find('.entity-wrapper.active .tax').html());
            var image = productWrapper.find('.first-column img');
            var deposit = 0;
            if (productWrapper.find('.entity-wrapper.active .deposit b').length > 0) {
                deposit = foodcoopshop.Helper.getCurrencyAsFloat(productWrapper.find('.entity-wrapper.active .deposit b').html());
            }
            var productId = productWrapper.find('.entity-wrapper.active input[name="productId"]').val();
            var unity = productWrapper.find('div.unity span.value').html();
            if (unity === undefined) {
                // use attribute label as unity
                unity = productWrapper.find('input[type="radio"]:checked').parent().text().trim();
            }

            if (amount > 1) {
                var forEachApproxRegExp = new RegExp(foodcoopshop.LocalizedJs.cart.forEach + ' ' + foodcoopshop.LocalizedJs.cart.approx);
                unity = unity.replace(forEachApproxRegExp, foodcoopshop.LocalizedJs.cart.approx);
            }

            var orderedQuantityInUnits;
            var orderedQuantityInUnitsWrapper = productWrapper.find('.entity-wrapper.active .quantity-in-units-input-field-wrapper');
            if (orderedQuantityInUnitsWrapper.length > 0) {
                orderedQuantityInUnitsWrapper.removeClass('error');
                orderedQuantityInUnits = foodcoopshop.Helper.getStringAsFloat(orderedQuantityInUnitsWrapper.find('input').val()) * amount;
            }

            var unitName = '';
            var unitNameElement = productWrapper.find('.entity-wrapper.active .unit-name'); 
            if (unitNameElement.length > 0) {
                unitName = unitNameElement.html();
            }

            var unitAmount = 1;
            var unitAmountElement = productWrapper.find('.entity-wrapper.active .unit-amount'); 
            if (unitAmountElement.length > 0 && unitAmountElement.html() != '') {
                unitAmount = foodcoopshop.Helper.getStringAsFloat(unitAmountElement.html());
            }
            
            var priceInclPerUnit = '';
            var priceInclPerUnitElement = productWrapper.find('.entity-wrapper.active .price-incl-per-unit'); 
            if (priceInclPerUnitElement.length > 0) {
                priceInclPerUnit = foodcoopshop.Helper.getCurrencyAsFloat(priceInclPerUnitElement.html());
            }

            if (orderedQuantityInUnitsWrapper.length > 0 && unitName != '' && priceInclPerUnit != '' && isNaN(orderedQuantityInUnits)) {
                foodcoopshop.Helper.enableButton($(this));
                foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                foodcoopshop.Helper.removeSpinnerFromButton($(this), foodcoopshop.Cart.cartButtonIcon);
                productWrapper.find('.entity-wrapper.active .quantity-in-units-input-field-wrapper').addClass('error');
            }
            
            if (orderedQuantityInUnits > 0) {
                price = foodcoopshop.Cart.getPriceBasedOnPricePerUnit(priceInclPerUnit, orderedQuantityInUnits, unitAmount);
            }
            
            var timebasedCurrencyElement = productWrapper.find('.entity-wrapper.active .timebasedCurrencySeconds');
            var timebasedCurrencyHours = 0;
            if (timebasedCurrencyElement.length > 0) {
                timebasedCurrencyHours = foodcoopshop.TimebasedCurrency.getTimebasedCurrencyAsFloat(
                    timebasedCurrencyElement.html()
                );
            }
            var pickupDay = productWrapper.find('.pickup-day').html();
            var productContainer = $('#cart p.products .product.' + productId);

            // restore last state after eventuall error after in ajax request
            var productContainerTmp = productContainer.clone();
            var cartSumTmp = $('.cart p.sum-wrapper span.sum').clone();

            $('#cart p.tmp-wrapper').empty();
            $('#cart p.tmp-wrapper').append(productContainerTmp);
            $('#cart p.tmp-wrapper').append(cartSumTmp);

            if (productContainer.length > 0) {
                // product already in cart
                foodcoopshop.Cart.updateExistingProduct(productContainer, amount, price, deposit, tax, timebasedCurrencyHours, orderedQuantityInUnits, unitName, unitAmount, priceInclPerUnit);
            } else {
                // product not yet in cart
                foodcoopshop.Cart.addOrAppendProductToPickupDay(productId, amount, price, productName, unity, '', image, deposit, tax, timebasedCurrencyHours, orderedQuantityInUnits, unitName, unitAmount, priceInclPerUnit, pickupDay);
                foodcoopshop.Helper.applyBlinkEffect($('#cart .product.' + productId), function () {
                    foodcoopshop.Cart.initRemoveFromCartLinks(); // bind click event
                });
            }

            // update cart sum
            foodcoopshop.Cart.updateCartSum(price);
            foodcoopshop.Cart.updateCartDepositSum(deposit * amount);
            foodcoopshop.Cart.updateCartTaxSum(tax * amount);
            foodcoopshop.Cart.updateCartTimebasedCurrencySum(timebasedCurrencyHours * amount);
            var button = productWrapper.find('.entity-wrapper.active .btn-success');

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
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.removeSpinnerFromButton(button, foodcoopshop.Cart.cartButtonIcon);
                        if (data.callback) {
                            eval(data.callback);
                        }
                    },
                    onError: function (data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.removeSpinnerFromButton(button, foodcoopshop.Cart.cartButtonIcon);
                        foodcoopshop.Cart.initRemoveFromCartLinks();
                        foodcoopshop.Cart.restoreOldStateOfProductAndSum(data.productId, data.msg);
                    }
                }
            );

        });

    },

    restoreOldStateOfProductAndSum : function (productId, msg) {

        var productTmpPlaceholder = '#cart p.tmp-wrapper .product.' + productId;
        var productElement = $('#cart p.products .product.' + productId);

        // product might not have been in cart...
        if ($(productTmpPlaceholder).length > 0) {
            productElement.replaceWith($(productTmpPlaceholder));
        } else {
            productElement.remove();
        }

        var tmpCartSum = $('#cart p.tmp-wrapper span.sum');
        $('#cart p.sum-wrapper span.sum').html(tmpCartSum.html());
        if (foodcoopshop.Helper.isMobile()) {
            $('.responsive-cart span.sum').html(tmpCartSum.html());
        }
        foodcoopshop.Helper.showErrorMessage(msg);
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
            
            var timebasedCurrencyHoursContainer = productContainer.find('.timebasedCurrencySeconds');
            var newTimebasedCurrencyHours = 0;
            if (timebasedCurrencyHoursContainer.length > 0) {
                var timebasedCurrencyHours = foodcoopshop.TimebasedCurrency.getTimebasedCurrencyAsFloat(timebasedCurrencyHoursContainer.html());
                newTimebasedCurrencyHours = timebasedCurrencyHours / oldAmount;
            }

            var depositContainer = productContainer.find('.deposit span');
            var newDeposit = 0;
            if (depositContainer.length > 0) {
                var deposit = foodcoopshop.Helper.getCurrencyAsFloat(depositContainer.html());
                newDeposit = deposit / oldAmount;
            }

            var elementClass = $(this).find('i').attr('class');
            var amount = 1;
            if (elementClass.match(/minus/)) {
                amount = -1;
            }

            var newPrice = price / oldAmount * amount;

            var button = $(this);
            foodcoopshop.Helper.disableButton(button);
            foodcoopshop.Helper.addSpinnerToButton(button, elementClass.replace(/fas /, ''));
            foodcoopshop.Helper.disableButton($(foodcoopshop.Cart.orderButtons));

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
                        foodcoopshop.Cart.updateExistingProduct(productContainer, amount, newPrice, newDeposit, newTax, newTimebasedCurrencyHours);
                        foodcoopshop.Cart.updateCartSum(newPrice);
                        foodcoopshop.Cart.updateCartTaxSum(newTax * amount);
                        foodcoopshop.Cart.updateCartTimebasedCurrencySum(newTimebasedCurrencyHours * amount);
                        if (depositContainer.length > 0) {
                            foodcoopshop.Cart.updateCartDepositSum(newDeposit * amount);
                        }
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
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                    }
                }
            );

        });
    },

    getCartProductHtml: function (productId, amount, price, productName, unity, manufacturerLink, image, deposit, tax, timebasedCurrencyHours, orderedQuantityInUnits, unitName, unitAmount, priceInclPerUnit, pickupDay) {
        var imgHtml = '<span class="image">' + image + '</span>';
        if (!$(image).attr('src').match(/de-default-home/)) {
            imgHtml = '<a href="'  + $(image).attr('src').replace(/-home_/, '-thickbox_') +  '" class="image">' + image + '</a>';
        }
        var unityHtml = '<span class="unity">';
        if (orderedQuantityInUnits) {
            unityHtml += orderedQuantityInUnits.toLocaleString() + ' ' + unitName;
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
                '<span class="delete"><a class="btn" title="' + foodcoopshop.LocalizedJs.cart.removeFromCart + '" href="javascript:void(0);"><i class="fas fa-times-circle"></i></a></span>' +
                '<span class="price">' + foodcoopshop.Helper.formatFloatAsCurrency(price) + '</span>' +
                (deposit > 0 ? '<span class="deposit">' + foodcoopshop.LocalizedJs.cart.deposit + ' + <span>' + foodcoopshop.Helper.formatFloatAsCurrency(deposit) + '</span></span>' : '') +
                (timebasedCurrencyHours ? '<span class="timebasedCurrencySeconds">' + foodcoopshop.TimebasedCurrency.formatFloatAsTimebasedCurrency(timebasedCurrencyHours) + '</span>'  : '') +
                '<span class="tax">' + foodcoopshop.Helper.formatFloatAsCurrency(tax) + '</span>' +
            '</span>' +
        '</span>';
    },

    updateCartSum: function (amount) {

        var cartSum = $('.cart p.sum-wrapper span.sum');
        if (cartSum.length == 0) {
            return;
        }
        var newCartSumHtml = foodcoopshop.Helper.formatFloatAsCurrency(
            foodcoopshop.Helper.getCurrencyAsFloat(cartSum.html()) + amount
        );
        cartSum.html(newCartSumHtml);

        if (foodcoopshop.Helper.isMobile()) {
            $('.responsive-cart span.sum').html(newCartSumHtml);
        }

    },

    updateCartDepositSum: function (amount) {
        var cartDepositSum = $('.cart p.deposit-sum-wrapper span.sum');
        if (cartDepositSum.length == 0) {
            return;
        }
        cartDepositSum.html(
            foodcoopshop.Helper.formatFloatAsCurrency(
                foodcoopshop.Helper.getCurrencyAsFloat(cartDepositSum.html()) + amount
            )
        );
    },

    updateCartTaxSum: function (amount) {
        var cartTaxSum = $('.cart p.tax-sum-wrapper span.sum');
        if (cartTaxSum.length == 0) {
            return;
        }
        cartTaxSum.html(
            foodcoopshop.Helper.formatFloatAsCurrency(
                foodcoopshop.Helper.getCurrencyAsFloat(cartTaxSum.html()) + amount
            )
        );
    },

    updateCartTimebasedCurrencySum: function (amount) {
        var cartTimebasedCurrencySum = $('.cart p.timebased-currency-sum-wrapper span.sum');
        if (cartTimebasedCurrencySum.length > 0) {
            var newHours = foodcoopshop.TimebasedCurrency.getTimebasedCurrencyAsFloat(cartTimebasedCurrencySum.html()) + amount;
            cartTimebasedCurrencySum.html(
                foodcoopshop.TimebasedCurrency.formatFloatAsTimebasedCurrency(
                    newHours
                )
            );
            foodcoopshop.TimebasedCurrency.updateHoursSumDropdown(newHours, $('#carts-timebased-currency-seconds-sum-tmp').find(':selected').val());
        }
    },

    /**
     * if current page is cart page, this function needs to update both #cart and cart in content area
     */
    initRemoveFromCartLinks: function () {

        $('.cart span.delete a').off('click').on('click', function () {

            var productId = $(this).closest('.product').data('product-id');
            var productContainer = $('.product.' + productId);
            foodcoopshop.Helper.disableButton($(foodcoopshop.Cart.orderButtons));

            productContainer.each(function (index) {
                var p = $(this);
                var pickupDayHeader = $(foodcoopshop.Cart.getPickupDayHeaderSelector(p.find('.pickup-day').html()));
                if (pickupDayHeader.first().find('span.product').length == 1) {
                    foodcoopshop.Helper.applyBlinkEffect(pickupDayHeader, function() {
                        pickupDayHeader.remove();
                    });
                }
                foodcoopshop.Helper.applyBlinkEffect(p, function () {
                    if (index == 0) {
                        foodcoopshop.Cart.updateCartSum(
                            foodcoopshop.Helper.getCurrencyAsFloat(p.find('span.price').html()) * -1
                        );
                        var deposit = p.find('.deposit span');
                        if (deposit.length > 0) {
                            foodcoopshop.Cart.updateCartDepositSum(
                                foodcoopshop.Helper.getCurrencyAsFloat(deposit.html()) * -1
                            );
                        }
                        foodcoopshop.Cart.updateCartTaxSum(
                            foodcoopshop.Helper.getCurrencyAsFloat(p.find('span.tax').html()) * -1
                        );
                        var timebasedCurrencyHours = p.find('.timebasedCurrencySeconds');
                        if (timebasedCurrencyHours.length > 0) {
                            foodcoopshop.Cart.updateCartTimebasedCurrencySum(
                                foodcoopshop.TimebasedCurrency.getTimebasedCurrencyAsFloat(timebasedCurrencyHours.html()) * -1
                            );
                        }
                    }
                    p.remove();
                    $('.error-message.' + productId).remove();
                });
            });

            var button = $(this);
            foodcoopshop.Helper.disableButton(button);
            foodcoopshop.Helper.addSpinnerToButton(button, 'fa-times-circle');
            
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
                    },
                    onError: function (data) {
                        $('.cart p.products .product.' + productId).addClass('error').remove();
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.removeSpinnerFromButton(button);
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                    }
                }
            );

        });

    },

    initLoadLastOrderDetailsDropdown : function() {
        $('#load-last-order-details').on('change', function() {
            var selectedValue = $(this).val();
            if (selectedValue != '') {
                var title = '';
                var dialogHtml = '';
                var redirectUrl = '';
                if (selectedValue == 'remove-all-products-from-cart') {
                    title = foodcoopshop.LocalizedJs.cart.emptyCart + '?';
                    dialogHtml = '<p>' + foodcoopshop.LocalizedJs.cart.reallyEmptyCart + '</p>';
                    redirectUrl = '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/emptyCart/';
                } else {
                    title = foodcoopshop.LocalizedJs.cart.loadPastOrder;
                    dialogHtml = foodcoopshop.LocalizedJs.cart.loadPastOrderDescriptionHtml;
                    redirectUrl = '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/addOrderToCart?deliveryDate=' + selectedValue;
                }
                dialogHtml += '<img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';

                var buttons = {};
                buttons['cancel'] = foodcoopshop.Helper.getJqueryUiCancelButton();
                buttons['yes'] = {
                    text: foodcoopshop.LocalizedJs.helper.yes,
                    click: function() {
                        $('.ui-dialog .ajax-loader').show();
                        $('.ui-dialog button').attr('disabled', 'disabled');
                        document.location.href = redirectUrl;
                    }
                };
                $('<div></div>').appendTo('body')
                    .html(dialogHtml)
                    .dialog({
                        modal: true,
                        title: title,
                        autoOpen: true,
                        width: 400,
                        resizable: false,
                        buttons: buttons,
                        close: function (event, ui) {
                            $(this).remove();
                        }
                    });
            }
        });
    }

};
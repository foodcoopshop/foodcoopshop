/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.Cart = {

    orderButtons: '.cart .btn-success.btn-order, .responsive-cart',
    
    getPickupDayHeaderSelector : function(pickupDay) {
        return '.cart p.pickup-day-header:contains("' + pickupDay + '")';
    },
    
    addOrAppendProductToPickupDay : function(productId, amount, price, productLink, unity, manufacturerLink, image, deposit, tax, timebasedCurrencyHours, pickupDay) {
        var pickupDayHeader = $(this.getPickupDayHeaderSelector(pickupDay));
        if (pickupDayHeader.length == 0) {
            $('.cart p.products').append('<p class="pickup-day-header">' + foodcoopshop.LocalizedJs.cart.PickupDay + ': <b>' + pickupDay + '</b></p>');
            pickupDayHeader = $(this.getPickupDayHeaderSelector(pickupDay)); // re-init after append
        }
        pickupDayHeader.append(
            foodcoopshop.Cart.getCartProductHtml(productId, amount, price, productLink, unity, manufacturerLink, image, deposit, tax, timebasedCurrencyHours, pickupDay)
        );
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
            this.addOrAppendProductToPickupDay(cp.productId, cp.amount, cp.price, cp.productLink, cp.unity_with_unit, cp.manufacturerLink, cp.image, cp.deposit, cp.tax, timebasedCurrencyHours, cp.nextDeliveryDay);
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
        foodcoopshop.Helper.onWindowScroll();

    },

    initCartFinish: function () {
        $('#inner-content button.btn-success').on('click', function () {
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

    updateExistingProduct: function (productContainer, amount, price, deposit, tax, timebasedCurrencyHours) {

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
        if (newUnityHtml != oldUnity.html()) {
            oldUnity.html(newUnityHtml);
            foodcoopshop.Helper.applyBlinkEffect(oldUnity);
        }

        // update price
        var oldPrice = productContainer.find('span.price');
        var newPrice = (
            foodcoopshop.Helper.getCurrencyAsFloat(oldPrice.html()) +
            (price * amount)
        );
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

    initAddToCartButton: function () {

        $('.product-wrapper a.btn.btn-cart').on('click', function () {

            foodcoopshop.Helper.removeFlashMessage();
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-cart-plus');
            foodcoopshop.Helper.disableButton($(foodcoopshop.Cart.orderButtons));

            $('#cart p.no-products').hide();
            $('#cart p.products').show();

            var productWrapper = $(this).closest('.product-wrapper');
            var productLink = productWrapper.find('.heading h4').html();
            var amount = parseInt(productWrapper.find('.entity-wrapper.active input[name="amount"]').val());
            var price = foodcoopshop.Helper.getCurrencyAsFloat(productWrapper.find('.entity-wrapper.active .price').html());
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
                foodcoopshop.Cart.updateExistingProduct(productContainer, amount, price, deposit, tax, timebasedCurrencyHours);
            } else {
                // product not yet in cart
                foodcoopshop.Cart.addOrAppendProductToPickupDay(productId, amount, amount * price, productLink, unity, '', image, deposit, tax, timebasedCurrencyHours, pickupDay);
                foodcoopshop.Helper.applyBlinkEffect($('#cart .product.' + productId), function () {
                    foodcoopshop.Cart.initRemoveFromCartLinks(); // bind click event
                });
            }

            // update cart sum
            foodcoopshop.Cart.updateCartSum(price * amount);
            foodcoopshop.Cart.updateCartDepositSum(deposit * amount);
            foodcoopshop.Cart.updateCartTaxSum(tax * amount);
            foodcoopshop.Cart.updateCartTimebasedCurrencySum(timebasedCurrencyHours * amount);
            var button = productWrapper.find('.entity-wrapper.active .btn-success');

            foodcoopshop.Helper.ajaxCall(
                '/' + foodcoopshop.LocalizedJs.cart.routeCart + '/ajaxAdd/',
                {
                    productId: productId,
                    amount: amount
                },
                {
                    onOk: function (data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.removeSpinnerFromButton(button, 'fa-cart-plus');
                    },
                    onError: function (data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.enableButton($(foodcoopshop.Cart.orderButtons));
                        foodcoopshop.Helper.removeSpinnerFromButton(button, 'fa-cart-plus');
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
            var newPrice = price / oldAmount;
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
                        foodcoopshop.Cart.updateCartSum(newPrice * amount);
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

    getCartProductHtml: function (productId, amount, price, productLink, unity, manufacturerLink, image, deposit, tax, timebasedCurrencyHours, pickupDay) {
        var imgHtml = '<span class="image">' + image + '</span>';
        if (!$(image).attr('src').match(/de-default-home/)) {
            imgHtml = '<a href="'  + $(image).attr('src').replace(/-home_/, '-thickbox_') +  '" class="image">' + image + '</a>';
        }
        return '<span data-product-id="' + productId + '" class="product' + ' ' + productId + '">' +
                imgHtml +
                '<span class="amount"><span class="value">' + amount + '</span>x</span>' +
                '<span class="product-name-wrapper">' +
                    productLink +
                    '<span class="unity">' + unity + '</span>' +
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
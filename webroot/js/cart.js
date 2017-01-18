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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.Cart = {

    /**
     * cart products already existed in database
     */
    initCartProducts: function(cartProducts) {

        var cartProducts = $.parseJSON(cartProducts);
        if (cartProducts.length == 0) return;

        $('.cart p.no-products').hide();
        var sum = 0;
        var depositSum = 0;
        var taxSum = 0;
        for (var i = 0; i < cartProducts.length; i++) {
            var cp = cartProducts[i];
            $('.cart p.products').append(
                this.getCartProductHtml(cp.productId, cp.amount, cp.price, cp.productLink, cp.unity, cp.manufacturerLink, cp.image, cp.deposit, cp.tax)
            );
            sum += cp.price;
            depositSum += cp.deposit;
            taxSum += cp.tax;
        }
        this.updateCartSum(sum);
        this.updateCartDepositSum(depositSum);
        this.updateCartTaxSum(taxSum);

        foodcoopshop.Cart.initRemoveFromCartLinks(); // bind click event
        $('.cart p.products').show();
        
        foodcoopshop.AppFeatherlight.initLightboxForImages('.cart .products a.image');

    },

    initCartFinish: function() {
        $('#inner-content button.btn-success').on('click', function() {
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
            $(this).closest('form').submit();
        });
    },

    initCartErrors: function(cartErrors) {
        var cartErrors = $.parseJSON(cartErrors);
        for (key in cartErrors) {
            var productContainer = $('.carts.detail .cart:not(#cart) .product.' + key);
            productContainer.addClass('error');
            productContainer.after('<ul class="error-message ' + key + '"><li>' + cartErrors[key].join('</li><li>') + '</li></ul>');
        }
    },

    updateExistingProduct: function(productContainer, amount, price, deposit, tax) {

        // update amount
        var oldAmount = productContainer.find('span.amount span.value');
        oldAmount.html(parseInt(oldAmount.html()) + parseInt(amount));
        foodcoopshop.Helper.applyBlinkEffect(oldAmount);

        // update price
        var oldPrice = productContainer.find('span.price');
        var newPrice = (
            foodcoopshop.Helper.getEuroAsFloat(oldPrice.html()) +
            (price * amount)
        );
        oldPrice.html(foodcoopshop.Helper.formatFloatAsEuro(newPrice));
        foodcoopshop.Helper.applyBlinkEffect(oldPrice);

        // update deposit
        var oldDeposit = productContainer.find('.deposit span');
        if (oldDeposit.length > 0) {
            var newDeposit = (
                foodcoopshop.Helper.getEuroAsFloat(oldDeposit.html()) +
                (deposit * amount)
            );
            oldDeposit.html(foodcoopshop.Helper.formatFloatAsEuro(newDeposit));
            foodcoopshop.Helper.applyBlinkEffect(oldDeposit);
        }
        
        // update tax
        var oldTax = productContainer.find('span.tax');
        var newTax = (
            foodcoopshop.Helper.getEuroAsFloat(oldTax.html()) +
            (tax * amount)
        );
        oldTax.html(foodcoopshop.Helper.formatFloatAsEuro(newTax));
        
    },

    initAddToCartButton: function() {

        $('.product-wrapper a.btn.btn-cart').on('click', function() {

        	foodcoopshop.Helper.removeFlashMessage();
            foodcoopshop.Helper.disableButton($(this));
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-cart');

            $('#cart p.no-products').hide();
            $('#cart p.products').show();

            var productWrapper = $(this).closest('.product-wrapper');
            var productLink = productWrapper.find('.heading h4').html();
            var amount = parseInt(productWrapper.find('.entity-wrapper.active input[name="amount"]').val());
            var price = foodcoopshop.Helper.getEuroAsFloat(productWrapper.find('.entity-wrapper.active .price').html());
            var tax = foodcoopshop.Helper.getEuroAsFloat(productWrapper.find('.entity-wrapper.active .tax').html());
            var image = productWrapper.find('.first-column img');
            var deposit = 0;
            if (productWrapper.find('.entity-wrapper.active .deposit b').length > 0) {
                deposit = foodcoopshop.Helper.getEuroAsFloat(productWrapper.find('.entity-wrapper.active .deposit b').html());
            }
            var productId = productWrapper.find('.entity-wrapper.active input[name="productId"]').val();
            var unity = productWrapper.find('div.unity span.value').html();
            if (unity === undefined) {
                // use attribute label as unity
                unity = productWrapper.find('input[type="radio"]:checked').parent().text().trim();
            }

            var productContainer = $('#cart p.products .product.' + productId);

            // restore last state after eventuall error after in ajax request
            var productContainerTmp = productContainer.clone();
            var cartSumTmp = $('.cart p.sum-wrapper span.sum').clone();

            $('#cart p.tmp-wrapper').empty();
            $('#cart p.tmp-wrapper').append(productContainerTmp);
            $('#cart p.tmp-wrapper').append(cartSumTmp);

            if (productContainer.length > 0) {
                // product already in cart
                foodcoopshop.Cart.updateExistingProduct(productContainer, amount, price, deposit);
            } else {
                // product not yet in cart
                $('#cart p.products').append(
                    foodcoopshop.Cart.getCartProductHtml(productId, amount, amount * price, productLink, unity, '', image, 0, tax)
                );
                foodcoopshop.Helper.applyBlinkEffect($('#cart .product.' + productId), function() {
                    foodcoopshop.Cart.initRemoveFromCartLinks(); // bind click event
                });
            }

            // update cart sum
            foodcoopshop.Cart.updateCartSum(price * amount);
            foodcoopshop.Cart.updateCartDepositSum(deposit * amount);
            foodcoopshop.Cart.updateCartTaxSum(tax * amount);
            var button = productWrapper.find('.entity-wrapper.active .btn-success');

            foodcoopshop.Helper.ajaxCall(
                '/warenkorb/ajaxAdd/', {
                    productId: productId,
                    amount: amount
                }, {
                    onOk: function(data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.removeSpinnerFromButton(button, 'fa-cart');
                    },
                    onError: function(data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.removeSpinnerFromButton(button, 'fa-cart');
                        foodcoopshop.Cart.initRemoveFromCartLinks();
                        foodcoopshop.Cart.restoreOldStateOfProductAndSum(data.productId, data.msg);
                    }
                }
            );

        });

    },
    
    restoreOldStateOfProductAndSum : function(productId, msg) {
        $('#cart p.products .product.' + productId).replaceWith(
            $('#cart p.tmp-wrapper .product.' + productId)
        );
        var tmpCartSum = $('#cart p.tmp-wrapper span.sum');
        $('#cart p.sum-wrapper span.sum').html(tmpCartSum.html());
        if (foodcoopshop.Helper.isMobile()) {
            $('.responsive-cart span.sum').html(tmpCartSum.html());
        }
        foodcoopshop.Helper.showErrorMessage(msg);
    },

    initChangeAmountLinks: function() {

        var cartInPageAmountWrapper = $('#inner-content .cart .products span.amount');

        cartInPageAmountWrapper.each(function() {
            $(this).append(
                $('<a />').html('<i class="fa fa-plus-circle"></i>').attr('class', 'btn').attr('href', 'javascript:void(0);')
            ).prepend(
                $('<a />').html('<i class="fa fa-minus-circle"></i>').attr('class', 'btn').attr('href', 'javascript:void(0);')
            );
            var amount = parseInt($(this).find('.value').html());
            if (amount < 2) {
                foodcoopshop.Helper.disableButton($(this).find('.fa-minus-circle').parent());
            }
        });


        cartInPageAmountWrapper.find('a').on('click', function() {

            var productId = $(this).closest('.product').data('product-id');
            var productContainer = $('.product.' + productId);
            var price = foodcoopshop.Helper.getEuroAsFloat(productContainer.find('.price').html());
            var tax = foodcoopshop.Helper.getEuroAsFloat(productContainer.find('.tax').html());
            var oldAmount = parseInt(productContainer.find('.amount span.value').html());
            var newPrice = price / oldAmount;
            var newTax = tax / oldAmount;
            var depositContainer = productContainer.find('.deposit span');
            var newDeposit = 0;
            if (depositContainer.length > 0) {
                var deposit = foodcoopshop.Helper.getEuroAsFloat(depositContainer.html());
                newDeposit = deposit / oldAmount;
            }

            var elementClass = $(this).find('i').attr('class');
            var amount = 1;
            if (elementClass.match(/minus/)) {
                var amount = -1;
            }

            var button = $(this);
            foodcoopshop.Helper.disableButton(button);
            foodcoopshop.Helper.addSpinnerToButton(button, elementClass.replace(/fa /, ''));

            foodcoopshop.Helper.ajaxCall(
                '/warenkorb/ajaxAdd/', {
                    productId: productId,
                    amount: amount
                }, {
                    onOk: function(data) {
                        foodcoopshop.Helper.removeSpinnerFromButton(button, elementClass.replace(/fa /, ''));
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Cart.updateExistingProduct(productContainer, amount, newPrice, newDeposit, newTax);
                        foodcoopshop.Cart.updateCartSum(newPrice * amount);
                        foodcoopshop.Cart.updateCartTaxSum(newTax * amount);
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
                    onError: function(data) {
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.removeSpinnerFromButton(button, elementClass.replace(/fa /, ''));
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                    }
                }
            );

        });
    },

    getCartProductHtml: function(productId, amount, price, productLink, unity, manufacturerLink, image, deposit, tax) {
    	
    	var imgHtml = '<span class="image">' + image + '</span>';
    	if (!$(image).attr('src').match(/de\-default\-home/)) {
    		imgHtml = '<a href="'  + $(image).attr('src').replace(/\-home_/, '-thickbox_') +  '" class="image">' + image + '</a>';
    	}
        return '<span data-product-id="' + productId + '" class="product' + ' ' + productId + '">' +
                imgHtml + 
                '<span class="amount"><span class="value">' + amount + '</span>x</span>' +
                '<span class="product-name-wrapper">' + 
                	productLink + 
                	'<span class="unity">' + unity + '</span>' +
            '</span>' + 
            '<span class="manufacturer-link">' + manufacturerLink + '</span>' +
            '<span class="right">' +
                '<span class="delete"><a class="btn" title="Aus dem Warenkorb löschen?" href="javascript:void(0);"><i class="fa fa-times-circle"></i></a></span>' +
                '<span class="price">' + foodcoopshop.Helper.formatFloatAsEuro(price) + '</span>' +
                (deposit > 0 ? '<span class="deposit">Pfand + <span>' + foodcoopshop.Helper.formatFloatAsEuro(deposit) + '</span></span>' : '') +
                '<span class="tax">' + foodcoopshop.Helper.formatFloatAsEuro(tax) + '</span>' +
            '</span>' +
        '</span>';
    },

    updateCartSum: function(amount) {

        var cartSum = $('.cart p.sum-wrapper span.sum');
        var newCartSumHtml = foodcoopshop.Helper.formatFloatAsEuro(
            foodcoopshop.Helper.getEuroAsFloat(cartSum.html()) + amount
        );
        cartSum.html(newCartSumHtml);

        if (foodcoopshop.Helper.isMobile()) {
            $('.responsive-cart span.sum').html(newCartSumHtml);
        }

    },

    updateCartDepositSum: function(amount) {
        var cartDepositSum = $('.cart p.deposit-sum-wrapper span.sum');
        cartDepositSum.html(
            foodcoopshop.Helper.formatFloatAsEuro(
                foodcoopshop.Helper.getEuroAsFloat(cartDepositSum.html()) + amount
            )
        );
    },

    updateCartTaxSum: function(amount) {
        var cartTaxSum = $('.cart p.tax-sum-wrapper span.sum');
        cartTaxSum.html(
            foodcoopshop.Helper.formatFloatAsEuro(
                foodcoopshop.Helper.getEuroAsFloat(cartTaxSum.html()) + amount
            )
        );
    },

    /**
     * if current page is cart page, this function needs to update both #cart and cart in content area
     */
    initRemoveFromCartLinks: function() {

        $('.cart span.delete a').off('click').on('click', function() {

            var productId = $(this).closest('.product').data('product-id');
            var productContainer = $('.product.' + productId);

            productContainer.each(function(index) {
                var p = $(this);
                foodcoopshop.Helper.applyBlinkEffect(p, function() {
                    if (index == 0) {
                        foodcoopshop.Cart.updateCartSum(
                            foodcoopshop.Helper.getEuroAsFloat(p.find('span.price').html()) * -1
                        );
                        var deposit = p.find('.deposit span');
                        if (deposit.length > 0) {
                            foodcoopshop.Cart.updateCartDepositSum(
                                foodcoopshop.Helper.getEuroAsFloat(deposit.html()) * -1
                            );
                        }
                        foodcoopshop.Cart.updateCartTaxSum(
                            foodcoopshop.Helper.getEuroAsFloat(p.find('span.tax').html()) * -1
                        );
                    }
                    p.slideUp('slow', function() {
                        p.remove();
                    });
                    $('.error-message.' + productId).remove();
                });
            });

            var button = $(this);
            foodcoopshop.Helper.disableButton(button);
            foodcoopshop.Helper.ajaxCall(
                '/warenkorb/ajaxRemove/', {
                    productId: productId
                }, {
                    onOk: function(data) {
                        foodcoopshop.Helper.enableButton(button);
                    },
                    onError: function(data) {
                        $('.cart p.products .product.' + productId).addClass('error').remove();
                        foodcoopshop.Helper.showErrorMessage(data.msg);
                        foodcoopshop.Helper.enableButton(button);
                        foodcoopshop.Helper.showErrorMessage(data.msg);

                    }
                }
            );

        });

    }

}
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.TimebasedCurrency = {

    shortcode: '',

    setShortcode: function(shortcode) {
        this.shortcode = shortcode;
    },

    getTimebasedCurrencyPrice: function(originalPrice, percentage) {
        return (originalPrice * (100 - percentage) / 100).toFixed(2);
    },

    getTimebasedCurrencyOriginalPrice: function(timebasedCurrencyPrice, percentage) {
        return (timebasedCurrencyPrice / (100 - percentage) * 100).toFixed(2);
    },

    bindOrderDetailProductPriceField: function(productPriceField, timebasedCurrencyData, productTimebasedCurrencyPriceField) {
        var appliedPercentage = timebasedCurrencyData.money_incl / productPriceField.val() * 100;
        productPriceField.off('keyup mouseup');
        productPriceField.on('keyup mouseup', function() {
            var currentPrice = $(this).val();
            var updatedTimebasedCurrencyPrice = foodcoopshop.TimebasedCurrency.getTimebasedCurrencyPrice(
                currentPrice,
                appliedPercentage
            );
            productTimebasedCurrencyPriceField.val(updatedTimebasedCurrencyPrice);
        });
    },

    bindOrderDetailProductTimebasedCurrencyPriceField : function(productTimebasedCurrencyPriceField, timebasedCurrencyData, productPriceField) {
        var appliedPercentage = timebasedCurrencyData.money_incl / productPriceField.val() * 100;
        productTimebasedCurrencyPriceField.off('keyup mouseup');
        productTimebasedCurrencyPriceField.on('keyup mouseup', function() {
            var currentTimebasedCurrencyPrice = $(this).val();
            var updatedPrice = foodcoopshop.TimebasedCurrency.getTimebasedCurrencyOriginalPrice(
                currentTimebasedCurrencyPrice,
                appliedPercentage
            );
            productPriceField.val(updatedPrice);
        });
    },

    formatFloatAsTimebasedCurrency: function(float) {
        return foodcoopshop.Helper.formatFloatAsString(float) + '&nbsp;' + this.shortcode;
    },

    getTimebasedCurrencyAsFloat: function(timebasedCurrency) {
        var regexAsString = '&nbsp;' + this.shortcode;
        var regex = new RegExp(regexAsString, 'g');
        var result = foodcoopshop.Helper.getStringAsFloat(timebasedCurrency.replace(regex, ''));
        return result;
    },

    updateHoursSumDropdown: function(maxSeconds, selectedIndex) {

        maxSeconds = Math.floor(parseFloat(maxSeconds) * 3600);
        var dropdown = $('#carts-timebased-currency-seconds-sum-tmp');

        if (selectedIndex === undefined || selectedIndex > maxSeconds) {
            selectedIndex = maxSeconds;
        }
        foodcoopshop.Helper.disableButton(dropdown);

        foodcoopshop.Helper.ajaxCall('/' + foodcoopshop.LocalizedJs.timebasedCurrency.routeCart + '/ajaxGetTimebasedCurrencyHoursDropdown/' + maxSeconds, {
        }, {
            onOk: function (data) {
                dropdown.empty();
                var selectedIndexFound = false;
                $.each(data.options, function(key, value) {
                    if (key == selectedIndex) {
                        selectedIndexFound = true;
                    }
                    dropdown.prepend($('<option></option>').attr('value', key).text(value));
                });
                if (!selectedIndexFound) {
                    selectedIndex = maxSeconds;
                }
                dropdown.val(selectedIndex);
                foodcoopshop.Helper.enableButton(dropdown);
            },
            onError: function (data) {
                alert(data.msg);
            }
        });

    },

    initDeletePayment: function () {

        $('.delete-payment-button').on('click',function () {

            var dataRow = $(this).closest('tr');

            var dialogHtml = '<p>Willst du die Eintragung wirklich löschen?<br />';
            var dateElement = dataRow.find('td:nth-child(3)');
            var dateHtml = 'Kein Datum eingetragen';
            if (dateElement.html() != '') {
                dateHtml = dateElement.html();
            }
            dialogHtml += 'Arbeitstag: <b>' + dateHtml + '</b> <br />';
            dialogHtml += 'Stunden: <b>' + dataRow.find('td:nth-child(6)').html();
            dialogHtml += '</b>';
            dialogHtml += '</p><img class="ajax-loader" src="/img/ajax-loader.gif" height="32" width="32" />';

            $('<div></div>')
                .appendTo('body')
                .html(dialogHtml)
                .dialog({
                    modal: true,
                    title: 'Zahlung löschen?',
                    autoOpen: true,
                    width: 400,
                    resizable: false,
                    buttons: {

                        'Abbrechen': function () {
                            $(this).dialog('close');
                        },

                        'Ja': function () {

                            $('.ui-dialog .ajax-loader').show();
                            $('.ui-dialog button').attr('disabled', 'disabled');

                            var paymentId = dataRow.data('payment-id');

                            foodcoopshop.Helper.ajaxCall(
                                '/admin/timebased-currency-payments/delete/',
                                {
                                    paymentId: paymentId
                                },
                                {
                                    onOk: function (data) {
                                        document.location.reload();
                                    },
                                    onError: function (data) {
                                        alert(data.msg);
                                    }
                                }
                            );

                        }

                    },
                    close: function (event, ui) {
                        $(this).remove();
                    }
                });
        });

    }

};

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.TimebasedCurrency = {

	updateSecondsSumDropdown : function(maxSeconds) {
		
		var dropdown = $('#timebased-currency-order-seconds-sum-tmp');
		var selectedIndex = dropdown.find(':selected').val();
		if (selectedIndex === undefined) {
			selectedIndex = maxSeconds;
		}
		
		foodcoopshop.Helper.disableButton(dropdown);
		
		foodcoopshop.Helper.ajaxCall('/warenkorb/ajaxGetTimebasedCurrencyHoursAndMinutesDropdown/' + maxSeconds, {
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
		
    initPaymentAdd: function (button) {

        $(button).featherlight(
            foodcoopshop.AppFeatherlight.initLightboxForForms(
                foodcoopshop.TimebasedCurrency.addPaymentFormSave,
                null,
                foodcoopshop.AppFeatherlight.closeLightbox,
                $('.add-payment-form')
            )
        );

    },

    addPaymentFormSave: function () {

        var hours = $('.featherlight-content #timebasedcurrencypayments-hours').val();
        var minutes = $('.featherlight-content #timebasedcurrencypayments-minutes').val();
        
        if (hours == 0 && minutes == 0) {
            alert('Bitte wähle deine geleistete Zeit aus.');
            foodcoopshop.AppFeatherlight.enableSaveButton();
            return;
        }

        var customerId = $('.featherlight-content input[name="TimebasedCurrencyPayments[customerId]"]').val();
        var manufacturerId = $('.featherlight-content #timebasedcurrencypayments-manufacturerid').val();

        var text = '';
        var textField = $('.featherlight-content #timebasedcurrencypayments-text');
        if (textField.length > 0) {
            text = textField.val().trim();
        }

        foodcoopshop.Helper.ajaxCall('/admin/timebased-currency-payments/add/', {
            hours: hours,
            minutes: minutes,
            text: text,
            customerId: customerId,
            manufacturerId: manufacturerId,
        }, {
            onOk: function (data) {
                document.location.reload();
            },
            onError: function (data) {
                alert(data.msg);
                document.location.reload();
            }
        });

    },
    
    initDeletePayment: function () {

        $('.delete-payment-button').on('click',function () {

            var dataRow = $(this).closest('tr');

            var dialogHtml = '<p>Willst du deine Eintragung wirklich löschen?<br />';
            dialogHtml += 'Datum: <b>' + dataRow.find('td:nth-child(2)').html() + '</b> <br />';
            dialogHtml += 'Stunden: <b>' + dataRow.find('td:nth-child(5)').html()
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

}

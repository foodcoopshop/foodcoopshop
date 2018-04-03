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

        var seconds = $('.featherlight-content #timebasedcurrencypayments-seconds').val();
        if (isNaN(parseFloat(seconds.replace(/,/, '.')))) {
            alert('Bitte gib eine gültige Zeit ein.');
            foodcoopshop.AppFeatherlight.enableSaveButton();
            return;
        }

        var customerId = $('.featherlight-content input[name="TimebasedCurrencyPayments[customerId]"]').val();
        var manufacturerId = $('.featherlight-content #timebasedcurrencypayments-manufacturerid').val();

        var text = '';
        if ($('.featherlight-content input[name="TimebasedCurrencyPayments[text]"]').length > 0) {
            text = $('.featherlight-content input[name="TimebasedCurrencyPayments[text]"]').val().trim();
        }

        foodcoopshop.Helper.ajaxCall('/admin/timebased-currency-payments/add/', {
            seconds: seconds,
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

}

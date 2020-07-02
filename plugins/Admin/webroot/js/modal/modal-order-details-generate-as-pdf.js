/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalOrderDetailsGenerateAsPdf = {

    init : function() {

        var modalSelector = '#order-details-generate-as-pdf';

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], foodcoopshop.LocalizedJs.helper.yes, 'fa fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.no, null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            foodcoopshop.LocalizedJs.admin.GenerateOrdersAsPdf,
            this.getHtml(),
            buttons
        );

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalOrderDetailsGenerateAsPdf.getSuccessHandler(modalSelector);
        });

        $('button.generate-order-details-as-pdf').on('click', function () {
            foodcoopshop.ModalOrderDetailsGenerateAsPdf.getOpenHandler(modalSelector);
        });

    },

    getHtml : function() {
        return '<p>' + foodcoopshop.LocalizedJs.admin.ReallyGenerateOrdersAsPdf + '</p>';
    },

    getSuccessHandler : function(modalSelector) {
        var pickupDay = $('input[name="pickupDay[]"]').val(); // filter-dropdown!
        window.open('/admin/order-details/orderDetailsAsPdf.pdf?pickupDay=' + pickupDay);
        $(modalSelector).remove();
    },

    getOpenHandler : function(modalSelector) {
        $(modalSelector).modal();
    }

};
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
foodcoopshop.ModalInstantOrderAdd = {

    init : function(button) {
        
        var modalSelector = '#instant-order-add';
        
        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            '',
            foodcoopshop.ModalInstantOrderAdd.getHtml(),
            []
        );
        
        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalInstantOrderAdd.getSuccessHandler(modalSelector);
        });
        
        $(modalSelector).on('hidden.bs.modal', function (e) {
            foodcoopshop.ModalInstantOrderAdd.getCloseHandler($(this));
        });

        $(button).on('click', function() {
            foodcoopshop.ModalInstantOrderAdd.getOpenHandler(button, modalSelector);
        });

    },
    
    getHtml : function() {
         return '';
    },
    
    getCloseHandler : function(modal) {
        modal.find('.modal-title').text('');
        modal.find('.modal-body').text('');
        foodcoopshop.Helper.ajaxCall(
            '/carts/ajaxDeleteInstantOrderCustomer',
            {},
            {
                onOk: function (data) {},
                onError: function (data) {}
            }
        );
    },
    
    getSuccessHandler : function(modalSelector) {
        //??
    },
    
    getOpenHandler : function(button, modalSelector) {
        
        $(modalSelector).modal();
        var iframe = $('<iframe></iframe>');
        iframe.attr('src', foodcoopshop.Helper.cakeServerName + '/admin/order-details/iframeStartPage');
        iframe.css('width', '100%');
        iframe.css('height', '100%');
        iframe.css('border', 'none');
        $(modalSelector + ' .modal-body').append(iframe)

        var header = $('<div class="message-container"><span class="start">' + foodcoopshop.LocalizedJs.admin.PlaceInstantOrderFor + ': </span></div>');
        $(modalSelector + ' .modal-title').append(header);

        // only clone dropdown once
        if ($(modalSelector + ' .modal-title span.start select').length == 0) {
            var customersDropdown = $('#add-instant-order-button-wrapper select').clone(true);
            customersDropdown.attr('id', 'customersDropdown');
            customersDropdown.on('change', function () {
                var newSrc = foodcoopshop.Helper.cakeServerName + '/admin/order-details/initInstantOrder/' + $(this).val();
                $(modalSelector + ' iframe').attr('src', newSrc);
            });

            $(modalSelector + ' iframe').on('load', function () {
                // called after each url change in iframe!
                var currentUrl = $(this).get(0).contentWindow.document.URL;
                var cartFinishedRegExp = new RegExp(foodcoopshop.LocalizedJs.admin.routeCartFinished);
                if (currentUrl.match(cartFinishedRegExp)) {
                    var message = button.contents().find('#flashMessage').html().replace(/<(a|i|b)[^>]*>/g,'');
                    document.location.href = foodcoopshop.Admin.addParameterToURL(
                        foodcoopshop.Admin.getParentLocation(),
                        'message=' + encodeURIComponent(message)
                    );
                }
            });
            customersDropdown.show();
            customersDropdown.removeClass('hide');
            customersDropdown.appendTo(modalSelector + ' .modal-title span.start');

            // always preselect user if there is a dropdown called #customerId (for call from order detail)
            var customerId = $('#customerid').val();
            if (customerId > 0) {
                customersDropdown.val(customerId);
                customersDropdown.trigger('change');
            }
        }

        
    }

};
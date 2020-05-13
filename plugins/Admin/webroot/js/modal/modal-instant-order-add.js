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
        
        $(button).on('click', function() {
            
            var modalSelector = '#instant-order-add';
            
            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                '',
                '',
                []
            );

            $(modalSelector).on('hidden.bs.modal', function (e) {
                console.log('hidden');
                console.log(e);
                foodcoopshop.ModalInstantOrderAdd.getCloseHandler($(this));
            });
            
            foodcoopshop.ModalInstantOrderAdd.getOpenHandler(button, modalSelector);
        });

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
    
    getOpenHandler : function(button, modalSelector) {
        
        $(modalSelector).modal();
        
        // START DROPDOWN
        var customerDropdownId = 'customerDropdown';
        var header = $('<div class="message-container"><span class="start">' + foodcoopshop.LocalizedJs.admin.PlaceInstantOrderFor + ': <select id="' + customerDropdownId + '"><option value="0">' + foodcoopshop.LocalizedJs.admin.PleaseSelect + '</option></select></span></div>');
        $(modalSelector + ' .modal-title').append(header);

        var customerDropdownSelector = '#' + customerDropdownId;

        $(customerDropdownSelector).selectpicker({
            liveSearch: true,
            size: 7,
            title: foodcoopshop.LocalizedJs.admin.PleaseSelectMember
        });

        // always preselect user if there is a dropdown called #customerId (for call from order detail)
        var customerId = $('#customerid').val();
        foodcoopshop.Admin.initCustomerDropdown(customerId, false, customerDropdownSelector, function () {
            var newSrc = foodcoopshop.Helper.cakeServerName + '/admin/order-details/initInstantOrder/' + $(customerDropdownSelector).val();
            $(modalSelector + ' iframe').attr('src', newSrc);
        });
        
        $(customerDropdownSelector).show();
        $(customerDropdownSelector).removeClass('hide');
        
        // START IFRAME
        var iframe = $('<iframe></iframe>');
        iframe.attr('src', foodcoopshop.Helper.cakeServerName + '/admin/order-details/iframeStartPage');
        iframe.css('width', '100%');
        iframe.css('height', '100%');
        iframe.css('border', 'none');
        $(modalSelector + ' .modal-body').append(iframe);

        $(modalSelector + ' iframe').on('load', function () {
            // called after each url change in iframe!
            var currentUrl = $(this).get(0).contentWindow.document.URL;
            var cartFinishedRegExp = new RegExp(foodcoopshop.LocalizedJs.admin.routeCartFinished);
            if (currentUrl.match(cartFinishedRegExp)) {
                var message = $(this).contents().find('#flashMessage').html().replace(/<(a|i)[^>]*>/g,'');
                document.location.href = foodcoopshop.Admin.addParameterToURL(
                    foodcoopshop.Admin.getParentLocation(),
                    'message=' + encodeURIComponent(message)
                );
            }
        });
        
        
    }

};
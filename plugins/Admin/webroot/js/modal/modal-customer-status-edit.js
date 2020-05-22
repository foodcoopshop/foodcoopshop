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
foodcoopshop.ModalCustomerStatusEdit = {

    init : function() {
        
        var modalSelector = '#customer-status-edit-form';
        
        $('.change-active-state').on('click', function () {
            foodcoopshop.ModalCustomerStatusEdit.getOpenHandler($(this), modalSelector);
        });
        
    },
    
    getSuccessHandler: function(customerId, newState, sendMail) {
        document.location.href = '/admin/customers/changeStatus/' + customerId + '/' + newState + '/' + sendMail;
    },
    
    getOpenHandler : function(button, modalSelector) {
        
        var customerId = button.attr('id').split('-');
        customerId = customerId[customerId.length - 1];

        var newState = 1;
        var newStateText = foodcoopshop.LocalizedJs.admin.ReallyActivateMember0;
        var newStateTextShort = foodcoopshop.LocalizedJs.admin.ActivateMember;
        var yesButtonText = foodcoopshop.LocalizedJs.admin.YesInfoMailWillBeSent;
        var sendMail = 1;
        if (button.hasClass('set-state-to-inactive')) {
            newState = 0;
            sendMail = 0;
            newStateText = foodcoopshop.LocalizedJs.admin.ReallyDeactivateMember0;
            newStateTextShort = foodcoopshop.LocalizedJs.admin.DeactivateMember;
            yesButtonText = foodcoopshop.LocalizedJs.helper.yes;
        }

        var dataRow = $('#change-active-state-' + customerId).closest('tr');
        
        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], yesButtonText, 'fa fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.no, null, true)
        ];

        var html = '<p>' + newStateText.replaceI18n(0, '<b>' + dataRow.find('td:nth-child(3) span.name a').text() + '</b>') + '</p>';
        
        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            newStateTextShort,
            html,
            buttons
        );
        
        $(modalSelector).modal();

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
            foodcoopshop.ModalCustomerStatusEdit.getSuccessHandler(customerId, newState, sendMail);
        });

    }

};
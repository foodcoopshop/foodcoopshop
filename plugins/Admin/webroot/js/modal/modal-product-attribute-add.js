/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalProductAttributeAdd = {

    init : function() {

        $('.add-product-attribute-button').on('click', function () {

            var modalSelector = '#modal-product-attribute-add';

            var dataRow = $(this).closest('tr');
            var productId = dataRow.attr('id').replace(/product-/, '').split('-');
            productId = productId[productId.length - 1];

            var html = '<p>' + __('Please_chose_the_new_attribute_for_product_{0}.', '<b> ' + dataRow.find('td.cell-name span.name-for-dialog').html() + '</b>') + '</p>';
            html += '<p>' + __('Attention_attributes_are_shown_in_same_order_as_added_and_this_cannot_be_changed_afterwards.') + '</p>';
            var productAttributesDropdown = $('#productattributeid').clone(true);

            if (productAttributesDropdown.find('option').length == 0) {
                foodcoopshop.Modal.appendFlashMessageError(modalSelector, __('This_function_can_only_be_used_if_attributes_exist.'));
                return;
            }

            productAttributesDropdown.show();
            productAttributesDropdown.removeClass('hide');
            html += '<select class="product-attributes-dropdown">' + productAttributesDropdown.html() + '</select>';

            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                __('Add_new_attribute_for_product'),
                html
            );

            foodcoopshop.TomSelectCustom.init('.product-attributes-dropdown');

            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductAttributeAdd.getSuccessHandler(modalSelector, productId);
            });

            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductAttributeAdd.getCloseHandler(modalSelector);
            });

            foodcoopshop.ModalProductAttributeAdd.getOpenHandler(modalSelector);

        });

    },

    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector, productId) {
        document.location.href = '/admin/products/addProductAttribute/' + productId + '/' + $(modalSelector + ' select.product-attributes-dropdown').val();
    },

    getOpenHandler : function(modalSelector) {
        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    }

};
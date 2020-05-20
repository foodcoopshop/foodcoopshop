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
foodcoopshop.ModalProductCategoriesEdit = {

    init : function() {
        
        var modalSelector = '#product-categories-edit-form';

        $('.product-categories-edit-button').on('click', function() {
            foodcoopshop.Modal.appendModalToDom(
                modalSelector,
                '',
                ''
            );
            
            foodcoopshop.ModalProductCategoriesEdit.getOpenHandler($(this), modalSelector);
            
            foodcoopshop.Modal.bindSuccessButton(modalSelector, function() {
                foodcoopshop.ModalProductCategoriesEdit.getSuccessHandler(modalSelector);
            });
            
            $(modalSelector).on('hidden.bs.modal', function (e) {
                foodcoopshop.ModalProductCategoriesEdit.getCloseHandler(modalSelector);
            });
            
        });

    },
    
    getCloseHandler : function(modalSelector) {
        $(modalSelector).remove();
    },

    getSuccessHandler : function(modalSelector) {
        
        var productId = $(modalSelector + ' .product-id').val();
        var selectedCategories = [];
        $(modalSelector + ' .categories-checkboxes input:checked').each(function () {
            selectedCategories.push($(this).val());
        });

        foodcoopshop.Helper.ajaxCall(
            '/admin/products/editCategories/',
            {
                productId: productId,
                selectedCategories: selectedCategories
            },
            {
                onOk: function (data) {
                    document.location.reload();
                },
                onError: function (data) {
                    document.location.reload();
                    alert(data.msg);
                }
            }
        );        
    },

    syncHigherParentElements: function(element) {
        
        var parentIdContainer = element.find('span.parent-id').first();
        if (parentIdContainer.length > 0) {
            var parentId = parentIdContainer.text();
            var higherParentElement = $('input[value="' + parentId + '"]');
            higherParentElement.prop('checked', true);
            this.syncHigherParentElements(higherParentElement.closest('label'));
        }
    },
    
    syncLowerParentElements: function(element) {
        var nextLabel = element.closest('.checkbox').next().find('label');
        if (nextLabel.text().match(/^-/)) {
            this.syncLowerParentElements(nextLabel);
            nextLabel.find('input').prop('checked', false);
        }
    },

    getOpenHandler : function(button, modalSelector) {
        
        $(modalSelector).modal();

        var productId = button.data('objectId');
        var formHtml = $('.categories-checkboxes').clone();

        $(modalSelector + ' .modal-body').append(formHtml);

        var productName = $('#product-' + productId + ' span.name-for-dialog').html();
        $(modalSelector + ' .modal-title').html(
            foodcoopshop.LocalizedJs.admin.ChangeCategories + ': ' + productName
        );

        $(modalSelector + ' .categories-checkboxes input[type="checkbox"]').on('click', function() {
            var label = $(this).closest('label');
            if ($(this).prop('checked')) {
                foodcoopshop.ModalProductCategoriesEdit.syncHigherParentElements(label);
            } else {
                foodcoopshop.ModalProductCategoriesEdit.syncLowerParentElements(label);
            }
        });

        // ids and for attribute needs to be unique - because of clone() it still exists...
        $(modalSelector + ' .categories-checkboxes label').each(function() {
            $(this).attr('for', $(this).attr('for') + '-' + productId);
            $(this).find('input').attr('id', $(this).find('input').attr('id') + '-' + productId);
        })

        var selectedCategories = $('#selected-categories-' + productId).val().split(',');
        $(modalSelector + ' .categories-checkboxes input[type="checkbox"]').each(function () {
            if ($.inArray($(this).val(), selectedCategories) != -1) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
        
        $(modalSelector + ' .product-id').val(productId);

    }

};
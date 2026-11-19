/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.ModalShoppingList = {

    modalSelector: '#modal-shopping-list',

    productIds: [],

    init: function (productIds) {
        this.productIds = productIds.map(String);
        this.updateButtons();
        $(document).on('click', '.btn-shopping-list', function () {
            foodcoopshop.ModalShoppingList.open($(this));
        });
        // the button always refers to the selected attribute
        $(document).on('click', '.attribute-button', function () {
            foodcoopshop.ModalShoppingList.updateButtons();
        });
    },

    /**
     * main product (eg. "12") or selected attribute (eg. "12-3")
     */
    getProductId: function (button) {
        return button.closest('.pw').find('.ew.active input[name="productId"]').val();
    },

    updateButtons: function () {
        var productIds = this.productIds;
        $('.btn-shopping-list').each(function () {
            var productId = foodcoopshop.ModalShoppingList.getProductId($(this));
            if (productId === undefined) {
                $(this).addClass('hide');
                return;
            }
            var isOnList = productIds.indexOf(String(productId)) !== -1;
            var title = isOnList ? __('Saved_on_shopping_list') : __('Add_to_shopping_list');
            $(this)
                .removeClass('hide')
                .toggleClass('is-on-shopping-list', isOnList)
                .attr('title', title)
                .attr('aria-label', title);
        });
    },

    updateSidebarMenu: function (sidebarMenu) {
        var existingMenu = $('.sidebar #shopping-lists-menu');
        if (existingMenu.length > 0) {
            existingMenu.replaceWith(sidebarMenu);
            return;
        }
        // first list was created: shopping lists are the first element of the sidebar
        $('.sidebar').prepend(sidebarMenu);
    },

    getDefaultAmount: function (button) {
        var amount = parseInt(button.closest('.pw').find('.ew.active input[name="amount"]').val());
        return isNaN(amount) || amount < 1 ? 1 : amount;
    },

    getProductName: function (button) {
        var productWrapper = button.closest('.pw');
        var productName = productWrapper.find('.heading h4').first().text().trim();
        var attributeName = productWrapper.find('input[type="radio"]:checked').parent().text().trim();
        if (attributeName !== '') {
            productName += ': ' + attributeName;
        }
        return productName;
    },

    open: function (button) {

        var productId = String(this.getProductId(button));
        foodcoopshop.Helper.removeFlashMessage();

        foodcoopshop.Helper.ajaxCall(
            '/' + __('route_shopping_lists') + '/ajaxGetShoppingListsForProduct',
            { productId: productId },
            {
                method: 'GET',
                onOk: function (data) {
                    foodcoopshop.ModalShoppingList.render(button, productId, data);
                },
                onError: function (data) {
                    foodcoopshop.Helper.showErrorMessage(data.msg);
                }
            }
        );
    },

    getAmountInput: function (className, value, maxAmount, disabled) {
        var input = $('<input type="number" min="1" class="form-control" />')
            .addClass(className)
            .attr('max', maxAmount)
            .attr('value', value)
            .attr('aria-label', __('Amount'));
        if (disabled) {
            input.attr('disabled', 'disabled');
        }
        return $('<span class="amount-wrapper"></span>')
            .append($('<span class="amount-label"></span>').text(__('Amount')))
            .append(input);
    },

    getBody: function (button, data) {

        var shoppingLists = data.shoppingLists;
        var defaultAmount = this.getDefaultAmount(button);
        var isOnAnyList = shoppingLists.some(function (shoppingList) {
            return shoppingList.amount !== null;
        });

        var body = $('<div class="shopping-list-modal-body"></div>');
        body.append($('<p class="product-name"></p>').text(this.getProductName(button)));

        // if the product is not on any list, the default list (or the first list) is suggested
        var suggestedListId = null;
        if (!isOnAnyList && shoppingLists.length > 0) {
            var defaultList = shoppingLists.find(function (shoppingList) {
                return shoppingList.isDefault;
            });
            suggestedListId = (defaultList || shoppingLists[0]).id;
        }

        if (shoppingLists.length > 0) {
            var rows = $('<div class="shopping-list-rows"></div>');
            $.each(shoppingLists, function (index, shoppingList) {
                var isSaved = shoppingList.amount !== null;
                // checked = saved on this list or suggested list
                var checked = isSaved || shoppingList.id === suggestedListId;
                var checkboxId = 'shopping-list-checkbox-' + shoppingList.id;

                var checkbox = $('<input type="checkbox" class="shopping-list-checkbox" />')
                    .attr('id', checkboxId)
                    .attr('value', shoppingList.id);
                if (checked) {
                    checkbox.attr('checked', 'checked');
                }

                var label = $('<label></label>').attr('for', checkboxId)
                    .append($('<span class="name"></span>').text(shoppingList.name))
                    .append('<span class="status"></span>');

                var row = $('<div class="shopping-list-row"></div>')
                    .attr('data-saved-amount', isSaved ? shoppingList.amount : '')
                    .append(checkbox)
                    .append(label)
                    .append(foodcoopshop.ModalShoppingList.getAmountInput('shopping-list-amount', isSaved ? shoppingList.amount : defaultAmount, data.maxAmount, !checked));
                rows.append(row);
            });
            body.append(rows);
        }

        // new list is preselected if there is no list yet
        var newListChecked = shoppingLists.length === 0;
        var newListCheckbox = $('<input type="checkbox" class="shopping-list-new-checkbox" />')
            .attr('aria-label', __('Create_new_shopping_list_and_add_product'));
        if (newListChecked) {
            newListCheckbox.attr('checked', 'checked');
        }
        var newListInput = $('<input type="text" id="shopping-list-new-name" class="form-control shopping-list-new-name" maxlength="255" />')
            .attr('placeholder', __('Shopping_list_new_name_placeholder'))
            .attr('value', data.defaultName);

        // if lists already exist, creating a new list is collapsed
        var isCollapsible = shoppingLists.length > 0;
        var newListSection = $('<div class="shopping-list-new"></div>').toggleClass('is-collapsible is-collapsed', isCollapsible);
        if (isCollapsible) {
            newListSection.append(
                $('<a href="javascript:void(0);" class="shopping-list-new-toggle"></a>')
                    .append('<i class="fas fa-fw fa-chevron-right"></i> ')
                    .append(document.createTextNode(__('Create_another_shopping_list_and_add_product')))
            );
        } else {
            newListSection.append($('<label for="shopping-list-new-name"></label>').text(__('Create_new_shopping_list_and_add_product')));
        }
        newListSection.append(
            $('<div class="shopping-list-row shopping-list-new-row"></div>')
                .append(newListCheckbox)
                .append(newListInput)
                .append(this.getAmountInput('shopping-list-new-amount', defaultAmount, data.maxAmount, !newListChecked))
        );
        body.append(newListSection);

        return body.prop('outerHTML');
    },

    /**
     * shows the pending change of every list and enables the save button if anything changed
     */
    updateState: function (updateSaveButton = true) {

        var modalSelector = this.modalSelector;
        var hasAddOrChange = false;
        var hasRemove = false;

        $(modalSelector + ' .shopping-list-rows .shopping-list-row').each(function () {
            var row = $(this);
            var savedAmount = row.attr('data-saved-amount') === '' ? null : parseInt(row.attr('data-saved-amount'));
            var checked = row.find('.shopping-list-checkbox').is(':checked');
            var amount = parseInt(row.find('.shopping-list-amount').val());
            var status = row.find('.status');
            var state = '';
            var statusHtml = '';

            row.find('.shopping-list-amount').prop('disabled', !checked);

            if (savedAmount !== null && checked && amount === savedAmount) {
                state = 'saved';
                statusHtml = '<i class="fas fa-check-circle"></i> ' + foodcoopshop.ModalShoppingList.escape(__('Product_is_already_saved_in_this_shopping_list'));
            } else if (savedAmount !== null && checked) {
                state = 'changed';
                hasAddOrChange = true;
                statusHtml = '<i class="fas fa-pencil-alt"></i> ' + foodcoopshop.ModalShoppingList.escape(__('Amount_will_be_changed_from_{0}_to_{1}', savedAmount, isNaN(amount) ? '?' : amount));
            } else if (savedAmount !== null) {
                state = 'removed';
                hasRemove = true;
                statusHtml = '<i class="fas fa-minus-circle"></i> ' + foodcoopshop.ModalShoppingList.escape(__('Will_be_removed'));
            } else if (checked) {
                state = 'added';
                hasAddOrChange = true;
                statusHtml = '<i class="fas fa-plus-circle"></i> ' + foodcoopshop.ModalShoppingList.escape(__('Will_be_added'));
            }

            row.removeClass('state-saved state-changed state-removed state-added').addClass(state !== '' ? 'state-' + state : '');
            row.toggleClass('is-checked', checked);
            status.html(statusHtml);
        });

        var newListCheckbox = $(modalSelector + ' .shopping-list-new-checkbox');
        var newListChecked = newListCheckbox.is(':checked');
        newListCheckbox.closest('.shopping-list-row').toggleClass('is-checked', newListChecked);
        $(modalSelector + ' .shopping-list-new-amount').prop('disabled', !newListChecked);
        if (newListChecked && $(modalSelector + ' .shopping-list-new-name').val().trim() !== '') {
            hasAddOrChange = true;
        }

        if (!updateSaveButton) {
            return hasAddOrChange || hasRemove;
        }

        foodcoopshop.Modal.getSuccessButton(modalSelector).prop('disabled', !hasAddOrChange && !hasRemove);

        return hasAddOrChange || hasRemove;
    },

    escape: function (text) {
        return $('<span></span>').text(text).html();
    },

    bindRowEvents: function (modalSelector) {

        $(modalSelector + ' .shopping-list-checkbox, ' + modalSelector + ' .shopping-list-new-checkbox').on('change', function () {
            foodcoopshop.ModalShoppingList.updateState();
        });
        $(modalSelector + ' .shopping-list-amount, ' + modalSelector + ' .shopping-list-new-amount').on('input', function () {
            foodcoopshop.ModalShoppingList.updateState();
        });

        // typing a name selects the new list automatically
        var newListCheckbox = $(modalSelector + ' .shopping-list-new-checkbox');

        // expanding selects the new list, collapsing deselects it (no list is created by mistake)
        $(modalSelector + ' .shopping-list-new-toggle').on('click', function () {
            var section = $(this).closest('.shopping-list-new');
            var expand = section.hasClass('is-collapsed');
            section.toggleClass('is-collapsed', !expand);
            $(this).find('i').toggleClass('fa-chevron-right', !expand).toggleClass('fa-chevron-down', expand);
            newListCheckbox.prop('checked', expand);
            foodcoopshop.ModalShoppingList.updateState();
            if (expand) {
                section.find('.shopping-list-new-name').trigger('focus').trigger('select');
            }
        });

        $(modalSelector + ' .shopping-list-new-name').on('input', function () {
            if ($(this).val().trim() !== '') {
                newListCheckbox.prop('checked', true);
            }
            foodcoopshop.ModalShoppingList.updateState();
        });

        this.updateState();
    },

    render: function (button, productId, data) {

        var modalSelector = this.modalSelector;
        $(modalSelector).remove();

        var buttons = [
            foodcoopshop.Modal.createButton(['btn-success'], __('Save'), 'fa-fw fas fa-check'),
            foodcoopshop.Modal.createButton(['btn-outline-light'], __('Cancel'), null, true)
        ];

        foodcoopshop.Modal.appendModalToDom(
            modalSelector,
            '<i class="fas fa-heart"></i> ' + __('Add_to_shopping_list'),
            this.getBody(button, data),
            buttons
        );

        this.bindRowEvents(modalSelector);

        foodcoopshop.Modal.bindSuccessButton(modalSelector, function () {
            foodcoopshop.ModalShoppingList.save(productId);
        });

        $(modalSelector).on('hidden.bs.modal', function () {
            $(modalSelector).remove();
        });

        new bootstrap.Modal(document.getElementById(modalSelector.replace(/#/, ''))).show();
    },

    save: function (productId) {

        var modalSelector = this.modalSelector;

        // enter key triggers the (disabled) save button too
        if (!this.updateState(false)) {
            foodcoopshop.Modal.resetButtons(modalSelector);
            this.updateState();
            return;
        }

        var amounts = {};
        $(modalSelector + ' .shopping-list-checkbox:checked').each(function () {
            amounts[$(this).val()] = $(this).closest('.shopping-list-row').find('.shopping-list-amount').val();
        });

        foodcoopshop.Helper.ajaxCall(
            '/' + __('route_shopping_lists') + '/ajaxSaveProduct',
            {
                productId: productId,
                amounts: amounts,
                newShoppingListName: $(modalSelector + ' .shopping-list-new-checkbox').is(':checked') ? $(modalSelector + ' .shopping-list-new-name').val() : '',
                newShoppingListAmount: $(modalSelector + ' .shopping-list-new-amount').val()
            },
            {
                onOk: function (data) {
                    if (foodcoopshop.ShoppingListPage.isActive) {
                        foodcoopshop.ShoppingListPage.update(productId, data.shoppingLists);
                    }
                    foodcoopshop.ModalShoppingList.productIds = data.productIds.map(String);
                    foodcoopshop.ModalShoppingList.updateButtons();
                    foodcoopshop.ModalShoppingList.updateSidebarMenu(data.sidebarMenu);
                    bootstrap.Modal.getInstance(document.getElementById(modalSelector.replace(/#/, ''))).hide();
                    foodcoopshop.Helper.showSuccessMessage(data.msg);
                },
                onError: function (data) {
                    foodcoopshop.Modal.resetButtons(modalSelector);
                    foodcoopshop.ModalShoppingList.updateState();
                    foodcoopshop.Modal.appendFlashMessageError(modalSelector, data.msg);
                }
            }
        );
    }

};

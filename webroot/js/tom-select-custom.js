/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

foodcoopshop.TomSelectCustom = {

    /**
     * Initialize Tom Select on all eligible <select> elements inside container.
     * Skip selects marked with .no-tom-select.
     */
    initAll : function(container) {
        container.find('select:not(.no-tom-select)').each(function () {
            var settings = {};
            if ($(this).attr('multiple') == 'multiple') {
                var emptyElement = $(this).find('option').first();
                if (emptyElement.val() == '') {
                    settings.placeholder = emptyElement.html();
                    emptyElement.remove();
                }
            }
            foodcoopshop.TomSelectCustom.init(this, settings);
        });
    },

    /**
     * Creates (or re-creates) a Tom Select instance for the given native <select> element.
     * The instance is available on the element itself (element.tomselect), so other code
     * (disabling options, setting values, lazy AJAX population, ...) can retrieve it later
     * with foodcoopshop.TomSelectCustom.get().
     *
     * events: object with Tom Select event names as keys (e.g. {dropdown_open: fn}).
     */
    init : function(selectElement, settings, events) {
        var $select = $(selectElement);
        var element = $select.get(0);
        var existingInstance = element.tomselect;
        if (existingInstance) {
            existingInstance.destroy();
        }
        var isMultiple = $select.prop('multiple');
        var defaultSettings = {
            // keep selected options visible in the dropdown (marked with a tick via CSS)
            hideSelected: false,
            // single selects must be able to (re-)select the empty "all ..." option
            allowEmptyOption: true,
            // render all options (default 50 cuts off large product/customer dropdowns)
            maxOptions: 10000,
            // search field lives inside the dropdown (not in the closed control)
            plugins: {
                dropdown_input: {}
            },
            render: {
                no_results: function() {
                    return $('<div class="no-results"></div>').text(__('No results found')).get(0);
                }
            }
        };
        if (isMultiple) {
            defaultSettings.placeholder = __('Nothing selected');
            // multi-selects stay open after selecting an option
            defaultSettings.closeAfterSelect = false;
            defaultSettings.plugins.no_backspace_delete = {};
            // multi: show clear (x) to remove all selections
            defaultSettings.plugins.clear_button = {
                title: __('Clear')
            };
            // multi: never render removable chips - always show "{0} selected"
            defaultSettings.render.item = function() {
                var item = document.createElement('div');
                item.className = 'ts-hidden-item';
                return item;
            };
        }
        var instance = new TomSelect(element, $.extend({}, defaultSettings, settings || {}));
        // the search input inside the dropdown gets settings.placeholder copied at setup -
        // clear it, "Nothing selected" is no useful placeholder for a search field
        if (instance.control_input) {
            instance.control_input.placeholder = '';
        }
        if (events) {
            for (var eventName in events) {
                instance.on(eventName, events[eventName]);
            }
        }
        if (isMultiple) {
            var updateMultipleCount = function() {
                foodcoopshop.TomSelectCustom.updateMultipleCount(instance);
            };
            // item_add and item_remove are triggered even for silent setValue() calls
            instance.on('item_add', updateMultipleCount);
            instance.on('item_remove', updateMultipleCount);
            updateMultipleCount();
        }
        return instance;
    },

    /**
     * Shows "{0} selected" in the control of a multi select (instead of chips).
     * The dropdown_input plugin's items-placeholder input covers the empty state.
     */
    updateMultipleCount : function(instance) {
        var control = instance.control;
        var countElement = control.querySelector('.ts-count');
        if (!countElement) {
            countElement = document.createElement('span');
            countElement.className = 'ts-count';
            var placeholderInput = control.querySelector('.items-placeholder');
            control.insertBefore(countElement, placeholderInput || null);
        }
        var count = instance.items.length;
        if (count > 0) {
            countElement.textContent = __('{0} selected', count);
            countElement.style.display = '';
        } else {
            countElement.style.display = 'none';
        }
    },

    get : function(selectElement) {
        var element = $(selectElement).get(0);
        return element ? element.tomselect || null : null;
    },

    /**
     * Replaces all options of the instance with the given structured data.
     * Items with an "options" array are registered as optgroups (Tom Select's
     * addOptions does not handle nested structures itself).
     */
    setData : function(instance, data) {
        instance.clear(true);
        instance.clearOptions();
        instance.clearOptionGroups();
        $.each(data, function (index, item) {
            if (item.options) {
                instance.addOptionGroup(item.label, {
                    label: item.label
                });
                $.each(item.options, function (i, option) {
                    option.optgroup = item.label;
                    instance.addOption(option);
                });
            } else {
                instance.addOption(item);
            }
        });
        instance.refreshOptions(false);
    },

    setMultipleDropdowns : function(selector) {
        $(selector).each(function () {
            var val = $(this).data('val');
            if (val) {
                var valAsArray = val.toString().split(',');
                var instance = foodcoopshop.TomSelectCustom.get(this);
                if (instance) {
                    // silent: do not trigger change (page already reflects the selection)
                    instance.setValue(valAsArray, true);
                }
            }
        });
    },

};

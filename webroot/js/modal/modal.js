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

foodcoopshop.Modal = {

    getSuccessButton : function(selector) {
        return $(selector + ' .modal-footer .btn-success:not(.no-auto-bind');
    },

    bindSuccessButton: function(selector, callback) {

        this.getSuccessButton(selector).on('click', function() {
            foodcoopshop.Helper.addSpinnerToButton( $(this), 'fa-check');
            foodcoopshop.Helper.disableButton($(this));
            callback();
        });

        $(selector).on('keypress', function(e) {
            if (e.which === 13) {
                foodcoopshop.Modal.getSuccessButton(selector).trigger('click');
            }
        });

    },

    resetButtons: function(selector) {
        var successButton = this.getSuccessButton(selector);
        foodcoopshop.Helper.removeSpinnerFromButton(successButton, 'fa-check');
        foodcoopshop.Helper.enableButton(successButton);
    },

    appendFlashMessage : function(selector, message) {
        foodcoopshop.Helper.showErrorMessage(message);
        $(selector + ' .modal-header').after($('#flashMessage'));
    },

    /**
     * on mobile tooltipster is triggered on click - interferes with editor
     */
    removeTooltipster : function() {
        $('.tooltipster-base ').remove();
    },

    createButton: function(classes, title, faIcon, isCloseButton, value=null) {
        var buttonHtml = '<button type="button" class="btn ' + classes.join(' ') + '"';
        if (isCloseButton) {
            buttonHtml += ' data-bs-dismiss="modal"';
        }
        if(value){
            buttonHtml += ' value="' + value + '"';
        }
        buttonHtml += '>';
        if (faIcon) {
            buttonHtml += '<i class="' + faIcon + '"></i> ';
        }
        buttonHtml += title;
        buttonHtml += '</button>';
        return buttonHtml;
    },

    appendModalToDom: function(elementId, title, body, buttons) {

        buttons = buttons ||
            [
                this.createButton(['btn-success'], foodcoopshop.LocalizedJs.helper.save, 'fa-fw fas fa-check'),
                this.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
            ];

        var html = '<div id="' + elementId.replace(/#/, '') + '" class="modal" tabindex="-1" role="dialog">';
        html += '<div class="modal-dialog" role="document">';
        html += '<div class="modal-content">';
        html += '<div class="modal-header">';
        html += '<h5 class="modal-title">' + title + '</h5>';
        html += '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' + foodcoopshop.LocalizedJs.helper.Close + '">';
        html += '<i class="fas fa-2x fa-times"></i>';
        html += '</button>';
        html += '</div>';
        html += '<div class="modal-body">' + body + '</div>';
        html += '<div class="modal-footer">';
        html += buttons.join('');
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        $('body').append(html);

        this.makeDraggable(elementId);
    },

    /**
     * necessary for removing modals from parent document (instant order)
     * sometimes (upload.js) native function "remove" does not work
     */
    destroy : function(modalId, parentDocument) {
        $(modalId, parentDocument).remove();
        $('.modal-backdrop', parentDocument).remove();
        $('body', parentDocument).removeClass('modal-open');
        $('body', parentDocument).css('overflow', 'auto');
    },

    makeDraggable : function(elementId) {
        $(elementId + ' .modal-header').on('mousedown', function(mousedownEvt) {
            var $draggable = $(this);
            var x = mousedownEvt.pageX - $draggable.offset().left,
                y = mousedownEvt.pageY - $draggable.offset().top;
            $('body').on('mousemove.draggable', function(mousemoveEvt) {
                $draggable.closest('.modal-content').offset({
                    'left': mousemoveEvt.pageX - x,
                    'top': mousemoveEvt.pageY - y
                });
            });
            $('body').one('mouseup', function() {
                $('body').off('mousemove.draggable');
            });
            $draggable.closest('.modal').one('hidden.bs.modal', function(e) {
                $('body').off('mousemove.draggable');
                $(elementId + ' .modal-content').css({
                    'left': 0,
                    'top': 0
                });
            });
        });
    }

};
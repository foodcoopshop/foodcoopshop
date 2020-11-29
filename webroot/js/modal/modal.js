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

foodcoopshop.Modal = {

    getSuccessButton : function(selector) {
        return $(selector + ' .modal-footer .btn-success');
    },

    bindSuccessButton: function(selector, callback) {

        this.getSuccessButton(selector).on('click', function() {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
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
     * on mobile tooltipster is triggered on click - interferes with ckeditor
     */
    removeTooltipster : function() {
        $('.tooltipster-base ').remove();
    },

    createButton: function(classes, title, faIcon, isCloseButton) {
        var buttonHtml = '<button type="button" class="btn ' + classes.join(' ') + '"';
        if (isCloseButton) {
            buttonHtml += ' data-dismiss="modal"';
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
                this.createButton(['btn-success'], foodcoopshop.LocalizedJs.helper.save, 'fa fa-check'),
                this.createButton(['btn-outline-light'], foodcoopshop.LocalizedJs.helper.cancel, null, true)
            ];

        var html = '<div id="' + elementId.replace(/#/, '') + '" class="modal" tabindex="-1" role="dialog">';
        html += '<div class="modal-dialog" role="document">';
        html += '<div class="modal-content">';
        html += '<div class="modal-header">';
        html += '<h5 class="modal-title">' + title + '</h5>';
        html += '<button type="button" class="close" data-dismiss="modal" aria-label="' + foodcoopshop.LocalizedJs.helper.Close + '">';
        html += '<span aria-hidden="true">&times;</span>';
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
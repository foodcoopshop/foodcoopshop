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

    bindSaveButton: function(selector, callback) {
        $(selector + ' .modal-footer .btn-success').on('click', function() {
            foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-check');
            foodcoopshop.Helper.disableButton($(this));
            callback();
        });
    },

    appendModalToDom: function(elementId, title, body) {
        var html = `
            <div id="` + elementId.replace(/#/, '') + `" class="modal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">` + title + `</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="` + foodcoopshop.LocalizedJs.helper.close + `">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">` + body + `</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success"><i class="fa fa-check"></i> ` + foodcoopshop.LocalizedJs.helper.save + `</button>
                            <button type="button" class="btn btn-outline-light" data-dismiss="modal">` + foodcoopshop.LocalizedJs.helper.cancel + `</button>
                        </div>
                    </div>
                </div>
            </div>`;
        $('body').append(html);
    }

};
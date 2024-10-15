<?php
declare(strict_types=1);

namespace App\Assets;

use Cake\Core\Configure;
use Cake\I18n\I18n;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class AssetsProvider
{

    public static function getCssFilesBase()
    {
        return [
            'bootstrap/dist/css/bootstrap.css',
            'theme-color.css',
            'reset.css',
            'jquery-ui/dist/themes/smoothness/jquery-ui.css',
            'table.css',
            'bootstrap-select/dist/css/bootstrap-select.css',
            '@fortawesome/fontawesome-free/css/all.css',
            'dark-mode.css',
            'global.css',
            'modal.css',
            'fonts.css',
            'tooltipster/dist/css/tooltipster.bundle.css',
            'tooltipster/src/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.css',
        ];
    }

    public static function getJsFilesBase()
    {

        $result = [];

        // if file does not exist, run `bin/cake SavedLocalizedJsAsStaticFile`
        // and then run `bin/cake asset_compress build`
        if (!Configure::read('debug')) {
            $result[] = 'localized-javascript-static.js';
        }

        $result = array_merge($result, [
            'jquery/dist/jquery.js',
            '@popperjs/core/dist/umd/popper.js',
            'bootstrap/dist/js/bootstrap.min.js',
            'bootstrap-select/dist/js/bootstrap-select.js',
            'jquery-ui/dist/jquery-ui.js',
            'blueimp-file-upload/js/jquery.fileupload.js',
            'bootstrap-select/dist/js/i18n/defaults-'.I18n::getLocale().'.js',
            'jquery-backstretch/jquery.backstretch.js',
            'tooltipster/dist/js/tooltipster.bundle.js',
            'jquery.scrollto/jquery.scrollTo.js',
            'background-image.js',
            'modal/modal.js',
            'modal/modal-logout.js',
            'modal/modal-order-for-different-customer-cancel.js',
            'modal/modal-payment-add.js',
            'modal/modal-image.js',
            'modal/modal-text.js',
            'modal/modal-self-service-confirm-dialog.js',
            'modal/modal-self-service-confirm-dialog-paymenttype-details.js',
            'modal/modal-load-last-order-details.js',
            'mobile.js',
            'mathjs/lib/browser/math.js',
            'calculator.js',
        ]);

        return $result;

    }

}

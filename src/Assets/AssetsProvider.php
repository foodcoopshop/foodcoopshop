<?php
namespace App\Assets;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class AssetsProvider
{

    public static function getCssFilesBase()
    {
        return [
            'reset.css',
            'components-jqueryui/themes/smoothness/jquery-ui.css',
            'table.css',
            'bootstrap-select/dist/css/bootstrap-select.css',
            'bootstrap/dist/css/bootstrap.css',
            '@fortawesome/fontawesome-free/css/all.css',
            'global.css',
            'modal.css',
            'fonts.css',
            'tooltipster/dist/css/tooltipster.bundle.css',
            'tooltipster/src/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.css',
        ];
    }

    public static function getJsFilesBase()
    {
        return [
            'jquery/dist/jquery.js',
            '@popperjs/core/dist/umd/popper.js',
            'jquery-backstretch/jquery.backstretch.js',
            'tooltipster/dist/js/tooltipster.bundle.js',
            'jquery.scrollto/jquery.scrollTo.js',
            'modal/modal.js',
            'modal/modal-logout.js',
            'modal/modal-instant-order-cancel.js',
            'modal/modal-payment-add.js',
            'modal/modal-image.js',
            'modal/modal-text.js',
            'modal/modal-load-last-order-details.js',
            'mobile.js',
        ];
    }

}

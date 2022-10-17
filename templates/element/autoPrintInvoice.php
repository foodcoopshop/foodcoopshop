<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!$isMobile && $this->request->getSession()->read('invoiceRouteForAutoPrint') != '') {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Helper.openPrintDialogForFile('".Configure::read('App.fullBaseUrl') . $this->request->getSession()->read('invoiceRouteForAutoPrint') . "');"
    ]);
    $this->request->getSession()->delete('invoiceRouteForAutoPrint');
}

?>
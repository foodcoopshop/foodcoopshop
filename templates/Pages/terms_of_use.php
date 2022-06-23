<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;
use Cake\I18n\I18n;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
if ($appAuth->isManufacturer()) {
    echo $this->element('legal/'.I18n::getLocale().'/' . $this->Html->getLegalTextsSubfolder() . '/termsOfUseForManufacturers');
} else {
    echo $this->element('legal/'.I18n::getLocale().'/' . $this->Html->getLegalTextsSubfolder() . '/termsOfUse');
}

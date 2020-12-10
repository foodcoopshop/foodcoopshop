<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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

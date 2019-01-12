<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

echo '<th style="width:20px;">';
if ($enabled) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.initRowMarkerAll();"
    ]);
    echo '<input type="checkbox" id="row-marker-all" />';
}
echo '</th>';
?>
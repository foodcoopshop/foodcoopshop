<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$raw = false;
if (Configure::read('debug') > 0) {
    $raw = true;
}

echo $this->AssetCompress->css('base', ['raw' => $raw]);
foreach ($configs as $config) {
    echo $this->AssetCompress->css($config, ['raw' => $raw]);
}

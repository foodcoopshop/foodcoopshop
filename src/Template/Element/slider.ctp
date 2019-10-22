<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!empty($sliders)) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Helper.initSlider();"
    ]);
    echo '<div id="slider">';
        foreach ($sliders as $slider) {
            echo '<img width="908" src="'.$this->Html->getSliderImageSrc($slider->image).'" />';
        }
    echo '</div>';
}

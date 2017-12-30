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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if (!empty($sliders)) {
    $this->element('addScript', array('script' =>
        Configure::read('app.jsNamespace').".Helper.initSlider();"
    ));
    echo '<div id="slider">';
    foreach ($sliders as $slider) {
        echo '<img width="905" src="'.$this->Html->getSliderImageSrc($slider['Slider']['image']).'" />';
    }
    if (count($sliders) > 1) {
        echo '<div class=cycle-pager></div>';
    }
    echo '</div>';
}

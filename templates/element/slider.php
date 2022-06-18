<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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
        echo '<div class="swiper-wrapper">';
        foreach ($sliders as $slider) {
            $class = ' class="swiper-slide"';
            if ($slider->link != '') {
                echo '<a ' . $class . ' href="' . h($slider->link) . '">';
            }
            echo '<img ' . ($slider->link == '' ? $class : '') . ' width="908" src="'.$this->Html->getSliderImageSrc($slider->image).'" />';
            if ($slider->link != '') {
                echo '</a>';
            }
        }
        echo '</div>';
    echo '</div>';
}

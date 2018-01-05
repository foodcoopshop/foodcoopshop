<?php
/**
 * Slider
 *
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
class Slider extends AppModel
{

    public $primaryKey = 'id_slider';

    public $validate = array(
        'position' => array(
            'number' => array(
                'rule' => array(
                    'range',
                    - 1,
                    101
                ),
                'message' => 'Bitte gibt eine Zahl von 0 bis 100 an'
            )
        ),
        'image' => array(
            'notBlank' => array(
                'rule' => array(
                    'notBlank'
                ),
                'message' => 'Bitte lade ein Bild hoch.'
            )
        )
    );

    public function getForHome()
    {
        $slides = $this->find('all', array(
            'conditions' => array(
                'Slider.active' => APP_ON
            ),
            'order' => array(
                'Slider.position' => 'ASC'
            )
        ));
        return $slides;
    }
}

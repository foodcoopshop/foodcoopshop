<?php

namespace App\Model\Table;

use Cake\Validation\Validator;

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
class SlidersTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_slider');
    }

    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('image', __('Please_upload_an_image.'));
        $validator->notEmpty('position', __('Please_enter_a_number_between_{0}_and_{1}.', [0,100]));
        $validator->range('position', [-1, 101], __('Please_enter_a_number_between_{0}_and_{1}.', [0,100]));
        return $validator;
    }

    public function getForHome()
    {
        $slides = $this->find('all', [
            'conditions' => [
                'Sliders.active' => APP_ON
            ],
            'order' => [
                'Sliders.position' => 'ASC'
            ]
        ]);
        return $slides;
    }
}

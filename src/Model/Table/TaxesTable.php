<?php

namespace App\Model\Table;

use Cake\Core\Configure;
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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TaxesTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('tax');
        parent::initialize($config);
        $this->setPrimaryKey('id_tax');
    }

    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('rate', __('Please_enter_a_tax_rate.'));
        $validator->range('rate', [0.01, 99.99], __('Please_enter_a_number_between_{0}_and_{1}.', [0.01,99.99]));
        $validator->add('rate', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => __('This_tax_rate_is_already_being_used.')
        ]);
        return $validator;
    }

    public function getForDropdown()
    {
        $taxes = $this->find('all', [
            'conditions' => [
                'Taxes.active' => APP_ON
            ],
            'order' => [
                'Taxes.rate' => 'ASC'
            ]
        ]);
        $preparedTaxes = [
            0 => '0 %'
        ];
        foreach ($taxes as $tax) {
            $preparedTaxes[$tax->id_tax] = Configure::read('app.numberHelper')->formatAsPercent($tax->rate);
        }
        return $preparedTaxes;
    }
}

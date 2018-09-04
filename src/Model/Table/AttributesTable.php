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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AttributesTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('attribute');
        parent::initialize($config);
        $this->setPrimaryKey('id_attribute');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('name', __('Please_enter_a_name.'));
        $validator->add('name', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => __('An_attribute_with_this_name_already_exists.')
        ]);
        return $validator;
    }

    public function getForDropdown()
    {
        $attributes = $this->find('all', [
            'order' => [
                'Attributes.name' => 'ASC'
            ]
        ]);

        $attributesForDropdown = [];
        foreach ($attributes as $attribute) {
            $attributesForDropdown[$attribute->id_attribute] = $attribute->name;
        }

        return $attributesForDropdown;
    }
}

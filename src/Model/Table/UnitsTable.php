<?php
namespace App\Model\Table;

use Cake\Validation\Validator;
use App\Lib\Error\Exception\InvalidParameterException;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class UnitsTable extends AppTable
{

    public function validationDefault(Validator $validator)
    {
        $validator->numeric('price_incl_per_unit', __('The_price_per_unit_needs_to_be_a_number.'));
        $validator->greaterThan('price_incl_per_unit', 0, __('The_price_per_unit_needs_to_be_greater_than_0.'));
        $validator
            ->add('name', 'validName', [
                'rule' => 'isValidName',
                'message' => __('The_name_is_not_valid.'),
                'provider' => 'table',
            ]);
        $validator->notEmpty('name', __('Please_enter_a_name.'));
        $validator->numeric('amount', __('The_amount_(quantity)_needs_to_be_a_number.'));
        $validator->greaterThan('amount', 0, __('The_amount_(quantity)_needs_to_be_greater_than_0.'));
        $validator->numeric('quantity_in_units', __('The_approximate_weight_needs_to_be_a_number.'));
        $validator->greaterThanOrEqual('quantity_in_units', 0, __('The_approximate_weight_needs_to_be_greater_or_equal_than_0.'));
        return $validator;
    }

    public function isValidName($value, array $context)
    {
        return in_array($value, ['kg', 'g'], true);
    }

    /**
     * @param int $productId
     * @param int $productAttributeId
     * @param boolean $pricePerUnitEnabled
     * @param double $priceInclPerUnit
     * @param string $name
     * @param int $amount
     * @param double $quantityInUnits
     * @throws InvalidParameterException
     * @return $result
     */
    public function saveUnits($productId, $productAttributeId, $pricePerUnitEnabled, $priceInclPerUnit, $name, $amount, $quantityInUnits) {

        if ($productAttributeId > 0) {
            $productId = 0;
        }

        $idCondition = [
            'id_product' => $productId,
            'id_product_attribute' => $productAttributeId
        ];

        $entity = $this->find('all', [
            'conditions' => $idCondition
        ])->first();

        if (empty($entity)) {
            $entity = $this->newEntity($idCondition);
        }

        $pricePerUnitEnabled = (int) $pricePerUnitEnabled;

        $patchedEntity = $this->patchEntity($entity, [
            'price_per_unit_enabled' => $pricePerUnitEnabled,
            'price_incl_per_unit' => $priceInclPerUnit,
            'name' => $name,
            'amount' => $amount,
            'quantity_in_units' => $quantityInUnits,
            ],
            [
                'validate' => $pricePerUnitEnabled ? 'default' : false
            ]
        );

        if ($entity->hasErrors()) {
            throw new InvalidParameterException(join(' ', $this->getAllValidationErrors($entity)));
        }

        $result = $this->save($patchedEntity);

        return $result;
    }

}

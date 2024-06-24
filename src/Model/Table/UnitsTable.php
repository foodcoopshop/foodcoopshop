<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Validation\Validator;
use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class UnitsTable extends AppTable
{

    use ProductCacheClearAfterSaveAndDeleteTrait;

    public function validationDefault(Validator $validator): Validator
    {
        $validator->numeric('price_incl_per_unit', __('The_price_per_unit_needs_to_be_a_number.'));
        $validator->greaterThan('price_incl_per_unit', 0, __('The_price_per_unit_needs_to_be_greater_than_0.'));
        $validator
            ->add('name', 'validName', [
                'rule' => 'isValidName',
                'message' => __('The_name_is_not_valid.'),
                'provider' => 'table',
            ]);
        $validator->notEmptyString('name', __('Please_enter_a_name.'));
        $validator->numeric('amount', __('The_amount_(quantity)_needs_to_be_a_number.'));
        $validator->greaterThan('amount', 0, __('The_amount_(quantity)_needs_to_be_greater_than_0.'));
        $validator->numeric('quantity_in_units', __('The_approximate_weight_needs_to_be_a_number.'));
        $validator->greaterThan('quantity_in_units', 0, __('The_approximate_weight_needs_to_be_greater_than_0.'));
        return $validator;
    }

    public function isValidName($value, array $context)
    {
        return in_array($value, ['kg', 'g', 'l'], true);
    }

    public function saveUnits($productId, $productAttributeId, $pricePerUnitEnabled, $priceInclPerUnit, $name, $amount, $quantityInUnits, $useWeightAsAmount) {

        if ($productAttributeId > 0) {
            $productId = 0;
        }

        $idCondition = [
            'id_product' => $productId,
            'id_product_attribute' => $productAttributeId
        ];

        $entity = $this->find('all', conditions: $idCondition)->first();

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
            'use_weight_as_amount' => $useWeightAsAmount,
            ],
            [
                'validate' => $pricePerUnitEnabled ? 'default' : false
            ]
        );

        if ($entity->hasErrors()) {
            throw new \Exception(join(' ', $this->getAllValidationErrors($entity)));
        }

        $result = $this->save($patchedEntity);

        return $result;
    }

}

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class UnitsTable extends AppTable
{

    public function validationDefault(Validator $validator)
    {
        $validator->numeric('price_incl_per_unit', 'Der Preis nach Gewicht muss eine Zahl sein.');
        $validator->greaterThan('price_incl_per_unit', 0, 'Der Preis nach Gewicht muss größer als 0 sein.');
        $validator
            ->add('name', 'validName', [
                'rule' => 'isValidName',
                'message' => 'Der Name ist nicht erlaubt.',
                'provider' => 'table',
            ]);
        $validator->notEmpty('name', 'Der Name muss angegeben sein.');
        $validator->numeric('amount', 'Die Anzahl muss eine Zahl sein.');
        $validator->greaterThan('amount', 0, 'Die Anzahl muss größer als 0 sein.');
        $validator->numeric('quantity_in_units', 'Das ungefähre Liefergewicht muss eine Zahl sein.');
        $validator->greaterThanOrEqual('quantity_in_units', 0, 'Das ungefähre Liefergewicht muss eine positive Zahl sein.');
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

        if (!empty($entity->getErrors())) {
            throw new InvalidParameterException(join(' ', $this->getAllValidationErrors($entity)));
        }

        $result = $this->save($patchedEntity);

        return $result;
    }

}

<?php
declare(strict_types=1);

namespace App\Model\Traits;

use Cake\Datasource\FactoryLocator;
use App\Lib\DeliveryRhythm\DeliveryRhythm;
use Cake\Validation\Validator;
use App\Model\Entity\Product;
use App\Model\Traits\NumberRangeValidatorTrait;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait ProductImportTrait
{

    use NumberRangeValidatorTrait;

    public function validationImport(Validator $validator)
    {
        $validator = $this->validationName($validator);
        $validator->inList('active', Product::ALLOWED_STATUSES, __('The_following_values_are_valid:') . ' ' . implode(', ', Product::ALLOWED_STATUSES));
        $validator = $this->getNumberRangeValidator($validator, 'price', 0, 2000);
        $taxesTable = FactoryLocator::get('Table')->get('Taxes');
        $allowedTaxIds = $taxesTable->getValidTaxIds();
        $validator->inList('id_tax', $allowedTaxIds, __('The_following_values_are_valid:') . ' ' . implode(', ', $allowedTaxIds));
        return $validator;
    }

    public function getValidatedEntity(
        $manufacturerId,
        $productName,
        $descriptionShort,
        $description,
        $unity,
        $isDeclarationOk,
        $idStorageLocation,
        $status,
        $grossPrice,
        $taxRate,
        $deposit,
        $barcode,
        $quantity,
    ) {

        $taxesTable = FactoryLocator::get('Table')->get('Taxes');
        $netPriceAndTaxId = $taxesTable->getNetPriceAndTaxId($grossPrice, $taxRate);

        $productEntity = $this->newEntity(
            [
                'id_manufacturer' => $manufacturerId,
                'name' => $productName,
                'delivery_rhythm_send_order_list_weekday' => DeliveryRhythm::getSendOrderListsWeekday(),
                'description_short' => $descriptionShort,
                'description' => $description,
                'unity' => $unity,
                'is_declaration_ok' => $isDeclarationOk,
                'id_storage_location' => $idStorageLocation,
                'active' => $status,
                'id_tax' => $netPriceAndTaxId['taxId'],
                'price' => $netPriceAndTaxId['netPrice'],
                'deposit_product' => [
                    'deposit' => $deposit,
                ],
                'barcode_product' => [
                    'barcode' => $barcode,
                ],
                'stock_available' => [
                    'quantity' => $quantity,
                ],
                'category_products' => [
                    [
                        'id_category' => Configure::read('app.categoryAllProducts'),
                    ],
                ],
            ],
            [
                'validate' => 'import',
            ]
        );

        $manufacturerTable = FactoryLocator::get('Table')->get('Manufacturers');
        $manufacturer = $manufacturerTable->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId,
            ]
        ])->first();
        if (empty($manufacturer)) {
            $productEntity->setError('id_manufacturer', __('Manufacturer_not_found.'));
        }

        return $productEntity;

    }

}

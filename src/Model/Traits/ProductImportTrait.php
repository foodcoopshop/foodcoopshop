<?php
declare(strict_types=1);

namespace App\Model\Traits;

use Cake\Datasource\FactoryLocator;
use App\Lib\DeliveryRhythm\DeliveryRhythm;
use Cake\Validation\Validator;
use App\Model\Entity\Product;

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

    public function validationImport(Validator $validator)
    {
        $validator = $this->validationName($validator);
        $validator->inList('active', Product::ALLOWED_STATUSES, __('The_following_values_are_valid:') . ' ' . implode(', ', Product::ALLOWED_STATUSES));
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
        $priceGross,
        $taxRate,
        $barcode,
    ) {

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
                'barcode_product' => [
                    'barcode' => $barcode,
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

    public function createFromCsv($productEntity) {

        $productEntity = $this->save($productEntity);
        return $productEntity;

        $taxRate = $this->Taxes->find('all', [
            'conditions' => [
                'Taxes.active' => APP_ON,
                'Taxes.rate' => $taxRate,
            ],
        ])->first();

        $newProduct->id_tax = $taxRate->id_tax ?? 0;
        $this->save($newProduct);

        $this->changePrice([
            [$newProduct->id_product => ['gross_price' => $priceGross]],
        ]);

        $newProduct = $this->find('all', [
            'conditions' => [
                'Products.id_product' => $newProduct->id_product,
            ],
            'contain' => [
                'BarcodeProducts',
            ]
        ])->first();

        return $newProduct;

    }

}

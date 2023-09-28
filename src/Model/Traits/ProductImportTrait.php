<?php
declare(strict_types=1);

namespace App\Model\Traits;

use Cake\Datasource\FactoryLocator;
use Cake\Datasource\Exception\RecordNotFoundException;

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

    public function createFromCsv(
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

        $manufacturerTable = FactoryLocator::get('Table')->get('Manufacturers');
        $manufacturer = $manufacturerTable->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();

        if (empty($manufacturer)) {
            throw new RecordNotFoundException('manufacturer not found: ' . $manufacturerId);
        }

        $newProduct = $this->add($manufacturer, $productName, $descriptionShort, $description, $unity, $isDeclarationOk, $idStorageLocation, $barcode);

        $this->changeStatus([
            [$newProduct->id_product => $status],
        ]);

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

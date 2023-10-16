<?php
declare(strict_types=1);

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
namespace App\Lib\Csv;

use League\Csv\Reader;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;

class ProductReader extends Reader {

    public function configureType(): void
    {
        $this->setDelimiter(';');
        $this->setHeaderOffset(0);
    }

    public function getPreparedRecords(): array
    {
        $records = $this->getRecords();
        $records = iterator_to_array($records);
        $records = array_values($records); // reindex array as 0 is dropped by iterator_to_array
        $preparedRecords = array_map([$this, 'formatColumns'], $records);
        return $preparedRecords;
    }

    private function formatColumns($record) {
        $record['PriceGross'] = Configure::read('app.numberHelper')->parseFloatRespectingLocale($record['PriceGross']);
        $record['TaxRate'] = Configure::read('app.numberHelper')->parseFloatRespectingLocale($record['TaxRate']);
        return $record;
    }

    public function import($manufacturerId)
    {
        $records = $this->getPreparedRecords();
        $productTable = FactoryLocator::get('Table')->get('Products');

        $validatedProductEntities = [];
        foreach($records as $record) {
            $validatedProductEntities[] = $productTable->getValidatedEntity(
                $manufacturerId,
                $record['ProductName'],
                $record['DescriptionShort'],
                $record['Description'],
                $record['Unity'],
                $record['IsDeclarationOk'],
                $record['StorageLocationId'],
                $record['Status'],
                $record['PriceGross'],
                $record['TaxRate'],
                $record['Barcode'],
            );
        }

        $allProductEntitiesValid = true;
        foreach($validatedProductEntities as $validatedProductEntity) {
            if ($validatedProductEntity->hasErrors()) {
                $allProductEntitiesValid = false;
            }
        }

        if ($allProductEntitiesValid) {
            $savedProductEntities = [];
            foreach($validatedProductEntities as $validatedProductEntity) {
                $savedProductEntities[] = $productTable->save($validatedProductEntity);
            }
            return $savedProductEntities;
        }

        return $validatedProductEntities;
    }

}

?>
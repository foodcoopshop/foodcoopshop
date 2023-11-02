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

    public const COLUMN_COUNT = 12;

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
        $record['Deposit'] = Configure::read('app.numberHelper')->parseFloatRespectingLocale($record['Deposit']);
        return $record;
    }

    public function getAllErrors($entities)
    {
        $errors = [];
        foreach($entities as $entity) {
            if ($entity->hasErrors()) {
                $errors[] = $entity->getErrors();
            }
        }
        return $errors;
    }

    public function areAllEntitiesValid($entities)
    {
        $allEntitiesValid = true;
        foreach($entities as $entity) {
            if ($entity->hasErrors()) {
                $allEntitiesValid = false;
            }
        }
        return $allEntitiesValid;
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
                $record['StorageLocation'],
                $record['Status'],
                $record['PriceGross'],
                $record['TaxRate'],
                $record['Deposit'],
                $record['Barcode'],
                $record['Quantity'],
            );
        }

        $allProductEntitiesValid = $this->areAllEntitiesValid($validatedProductEntities);
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
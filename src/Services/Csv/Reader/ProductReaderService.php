<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Csv\Reader;

use League\Csv\Reader;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class ProductReaderService extends Reader {

    public const ALLOWED_UPLOAD_MIME_TYPES = ['text/csv'];
    public const COLUMN_COUNT = 11;

    public function configureType(): void
    {
        $this->setHeaderOffset(0);
    }

    public function getPreparedRecords(): array
    {
        $records = $this->getRecords();
        $records = iterator_to_array($records);
        $records = array_values($records); // reindex array as 0 is dropped by iterator_to_array
        $preparedRecords = array_map([$this, 'formatColumnsAndSetDefaultValues'], $records);
        return $preparedRecords;
    }

    private function formatColumnsAndSetDefaultValues($record) {
        $record[__('Gross_price')] = Configure::read('app.numberHelper')->parseFloatRespectingLocale($record[__('Gross_price')]);
        $record[__('Tax_rate')] = $record[__('Tax_rate')] ? Configure::read('app.numberHelper')->parseFloatRespectingLocale($record[__('Tax_rate')]) : 0;
        $record[__('Deposit')] = $record[__('Deposit')] ? Configure::read('app.numberHelper')->parseFloatRespectingLocale($record[__('Deposit')]) : 0;
        $record[__('Product_declaration')] = $record[__('Product_declaration')] != '' ? $record[__('Product_declaration')] : 0;
        return $record;
    }

    public function getAllErrors($entities)
    {
        $errors = [];
        foreach($entities as $entity) {
            if ($entity->hasErrors()) {
                $errors[] = $entity->getErrors();
            } else {
                $errors[] = [];
            }
        }
        return $errors;
    }

    public function areAllEntitiesValid($entities)
    {
        $allEntitiesValid = true;
        if (empty($entities)) {
            return false;
        }
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
        $productsTable = TableRegistry::getTableLocator()->get('Products');

        $validatedProductEntities = [];
        foreach($records as $record) {
            $validatedProductEntities[] = $productsTable->getValidatedEntity(
                $manufacturerId,
                $record[__('Name')],
                $record[__('Description_short')],
                $record[__('Description')],
                $record[__('Unit')],
                $record[__('Gross_price')],
                $record[__('Tax_rate')],
                $record[__('Deposit')],
                $record[__('Amount')],
                $record[__('Status')],
                $record[__('Product_declaration')],
                $record[__('Storage_location')],
            );
        }

        $allProductEntitiesValid = $this->areAllEntitiesValid($validatedProductEntities);
        if ($allProductEntitiesValid) {
            $savedProductEntities = [];
            foreach($validatedProductEntities as $validatedProductEntity) {
                $savedProductEntities[] = $productsTable->save($validatedProductEntity);
            }
            return $savedProductEntities;
        }

        return $validatedProductEntities;
    }

}

?>
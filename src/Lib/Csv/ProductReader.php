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

class ProductReader extends Reader {

    public function configureType(): void
    {
        $this->setDelimiter(';');
        $this->setHeaderOffset(0);
    }

    public function import($manufacturerId) {
        $records = $this->getRecords();
        $productTable = FactoryLocator::get('Table')->get('Products');
        $productEntities = [];
        foreach($records as $record) {
            $productEntities[] = $productTable->addWithManufacturerId(
                $manufacturerId,
                $record['ProductName'],
                $record['DescriptionShort'],
                $record['Description'],
                $record['Unity'],
                $record['IsDeclarationOk'],
                $record['StorageLocationId'],
                (int) $record['Status'],
                $record['Barcode'],
            );
        }
        return $productEntities;
    }

}

?>
<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\Csv;

use Cake\Core\Configure;

class RaiffeisenBankingReader extends BankingReader {

    public $dataContainsHeadline = false;

    public function configureType(): void
    {
        $this->setDelimiter(';');
    }

    public function checkStructureForRecord($record): bool
    {

        $result = false;

        if (in_array(count($record), [6,7]) &&
            strlen($record[0]) == 10 &&
            strlen($record[2]) == 10 &&
            is_numeric(Configure::read('app.numberHelper')->getStringAsFloat($record[3])) &&
            $record[4] == 'EUR' &&
            strlen($record[5]) == 23 &&
            (empty($record[6]) || !isset($record[6]))
            ) {
            $result = true;
        }

        return $result;
    }

    public function equalizeStructure(array $records): array
    {

        $preparedRecords = [];
        foreach($records as $record){

            // remove empty array elements
            $record = array_filter($record);

            $record['content'] = $record[1];

            $record['amount'] = $record[3];

            // 01.02.2019 02:51:14:563 =>
            // 01.02.2019 02:51:14.563
            $record['date'] =  substr_replace($record[5], '.', 19, 1);

            $preparedRecords[] = $record;
        }

        return $preparedRecords;
    }


}

?>
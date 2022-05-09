<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\Csv;

use Cake\Core\Configure;

class GlsBankBankingReader extends BankingReader {

    public function configureType(): void
    {
        $this->setDelimiter(';');
        $this->setHeaderOffset(0);
    }

    public function checkStructureForRecord($record): bool
    {

        $result = false;

        if (count($record) == 19 &&
            strlen($record['Valutadatum']) == 10 &&
            $record['Waehrung'] == 'EUR' &&
            is_numeric(Configure::read('app.numberHelper')->getStringAsFloat($record['Betrag'])) &&
            !empty($record['Verwendungszweck'])
            ) {
                $result = true;
            }

            return $result;
    }

    public function equalizeStructure(array $records): array
    {

        $preparedRecords = [];
        foreach($records as $record){

            $contentFields = [
                $record['Name Zahlungsbeteiligter'],
                $record['IBAN Zahlungsbeteiligter'],
                $record['Verwendungszweck'],
            ];

            $record['content'] = join(' ', $contentFields);
            $record['amount'] = $record['Betrag'];
            $record['date'] =  $record['Valutadatum'];

            $preparedRecords[] = $record;
        }

        return $preparedRecords;
    }


}

?>
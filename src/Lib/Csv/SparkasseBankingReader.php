<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\Csv;

use Cake\Core\Configure;

class SparkasseBankingReader extends BankingReader {

    public function configureType(): void
    {
        $this->setDelimiter(';');
        $this->setHeaderOffset(0);
    }

    public function checkStructureForRecord($record): bool
    {

        $result = false;
        if (strlen($record['Buchungsdatum']) == 10 &&
            $record['Währung'] == 'EUR' &&
            is_numeric(Configure::read('app.numberHelper')->getStringAsFloat($record['Betrag'])) &&
            isset($record['Partnername']) &&
            isset($record['Partner IBAN']) &&
            isset($record['Buchungs-Info']) &&
            isset($record['Buchungsreferenz']) &&
            isset($record['Zahlungsreferenz'])
            ) {
            $result = true;
        }

        return $result;
    }

    public function equalizeStructure(array $records): array
    {

        $preparedRecords = [];

        foreach($records as $record) {

            $contentFields = [
                $record['Buchungs-Info'],
                $record['Zahlungsreferenz'],
                $record['Partnername'],
                $record['Partner IBAN'],
                $record['Buchungsreferenz'],
            ];

            $contentFields = array_filter($contentFields);

            $record['content'] = join('<br />', $contentFields);
            $record['amount'] = $record['Betrag'];
            $record['date'] =  $record['Buchungsdatum'];

            $preparedRecords[] = $record;
        }

        return $preparedRecords;
    }

}

?>
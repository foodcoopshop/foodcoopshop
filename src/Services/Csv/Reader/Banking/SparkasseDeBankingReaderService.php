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
namespace App\Services\Csv\Reader\Banking;

use Cake\Core\Configure;

class SparkasseDeBankingReaderService extends BankingReaderService {

    public bool $csvHasUTF16Format = false;

    public function configureType(): void
    {
        $this->setDelimiter(';');
        $this->setHeaderOffset(0);
    }

    /**
     * Checks structure of one record. If structure is correct, it returns true, otherwise false.
     * @param array<string, mixed> $record
     */
    public function checkStructureForRecord(array $record): bool
    {
        // Check if all required fields are present
        $required = [ 'Buchungstag', 'Beguenstigter/Zahlungspflichtiger', 'Kontonummer/IBAN', 'BIC (SWIFT-Code)', 'Betrag', 'Waehrung', 'Buchungstext', 'Verwendungszweck', ];
        foreach($required as $field) {
            if (!isset($record[$field])) {
                return false;
            }
        }

        // Check if date is in correct format (dd.mm.yyyy)
        if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $record['Buchungstag'])) { return false; }

        // Check if amount is numeric 
        $amount = Configure::read('app.numberHelper')->getStringAsFloat($record['Betrag']); 
        if (!is_numeric($amount)) { 
            return false; 
        }

        // Check currency
        if ($record['Waehrung'] !== 'EUR') { 
            return false; 
        } 
        
        return true;
    }

    /**
     * Unifying structure of records. This is necessary because different banks have different structures. 
     * After unifying structure, all records have the same structure and can be processed in the same way.
     * @param array<int, array<string, mixed>> $records
     * @return array<int, array<string, mixed>>
     */
    public function equalizeStructure(array $records): array
    {
        $preparedRecords = [];

        foreach($records as $record) {

            $contentFields = [
                $record['Buchungstext'],
                $record['Verwendungszweck'],
                $record['Beguenstigter/Zahlungspflichtiger'],
                $record['Kontonummer/IBAN'],
                $record['BIC (SWIFT-Code)'],
            ];

            /*
            $contentFields = array_filter($contentFields);

            $record['content'] = join('<br />', $contentFields);
            $record['amount'] = $record['Betrag'];
            $record['date'] =  $record['Buchungstag'];
            */

            // Only remove empty content (no removal of fields)
            $contentFields = array_filter($contentFields, fn($v) => trim((string)$v) !== '');
            
            // Build content for displaying in frontend (with line breaks)
            $record['content'] = implode('<br />', $contentFields);
            
            $record['amount'] = $record['Betrag'];
            
            // Normalize date (dd.mm.yyyy → yyyy-mm-dd 00:00:00.000000)
            $date = \DateTime::createFromFormat('d.m.Y', $record['Buchungstag']);
            $record['date'] = $date ? $date->format('Y-m-d 00:00:00.000000') : null;

            $preparedRecords[] = $record;
        }

        return $preparedRecords;
    }

}

?>
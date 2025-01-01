<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Csv\Reader\Banking;

use Cake\Core\Configure;

class VolksbankBankingReaderService extends BankingReaderService {

    public bool $csvHasIsoFormat = true;

    public function configureType(): void
    {
        $this->setDelimiter(';');
        $this->setHeaderOffset(0);
    }

    public function checkStructureForRecord($record): bool
    {

        $result = false;
        if (count($record) == 27 &&
            strlen($record['Valutadatum']) == 10 &&
            strlen($record['Umsatzzeit']) == 26 &&
            $record['Waehrung'] == 'EUR' &&
            is_numeric(Configure::read('app.numberHelper')->getStringAsFloat($record['Betrag'])) &&
            !empty($record['Umsatztext'])
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

            $record['content'] = $record['Umsatztext'];

            $record['amount'] = $record['Betrag'];

            // 2021-07-06-07.54.26.789861 =>
            // 2021-07-06 07.54.26.789861
            $record['date'] =  substr_replace($record['Umsatzzeit'], ' ', 10, 1);

            $preparedRecords[] = $record;
        }

        return $preparedRecords;
    }

}

?>
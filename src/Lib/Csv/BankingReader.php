<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\Csv;
use League\Csv\Reader;

class BankingReader extends Reader {

    const TYPE_RAIFFEISEN = 1;
    
    private $type;
    
    private function configureType1(): void
    {
        $this->setDelimiter(';');
    }
    
    private function prepareRecords1($record): array
    {
        return array_filter($record); // remove empty array elements
    }
    
    public function setType($type): void
    {
        $this->type = $type;
        $method = 'configureType' . $this->type;
        if (!method_exists($this, $method)) {
            throw new \Exception('method does not exist: ' . $method);
        }
        $this->$method();
    }
    
    public function getPreparedRecords(): array
    {
        $records = $this->getRecords();
        $records = iterator_to_array($records);
        
        $method = 'prepareRecords' . $this->type;
        if (!method_exists($this, $method)) {
            throw new \Exception('method does not exist: ' . $method);
        }
        
        $records = array_map([$this, $method], $records);
        
        $preparedRecords = [];
        foreach($records as $record) {
            $preparedRecords[] = [
                'text' => $record[1],
                'amount' => $record[3],
                'data' => $record[5]
            ];
        }
        
        return $preparedRecords;
    }
    
}

?>
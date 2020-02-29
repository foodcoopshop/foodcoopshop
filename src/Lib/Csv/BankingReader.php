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

use App\Model\Entity\Customer;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
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
        // 1) remove empty array elements
        $record = array_filter($record);
        
        // 2) 01.02.2019 02:51:14:563 replaces last : to . (microseconds)
        $record[5] =  substr_replace($record[5], '.', 19, 1);
        
        return $record;
    }
    
    private function getCustomerByPersonalTransactionCode($content): ?Customer
    {
        $customerModel = TableRegistry::getTableLocator()->get('Customers');
        $query = $customerModel->find('all', [
            'fields' => [
                'personalTransactionCode' => $customerModel->getPersonalTransactionCodeField(),
            ]
        ]);
        $personalTransactionCodes = $query->all()->extract('personalTransactionCode')->toArray();
        
        $regex = '/' . join('|', $personalTransactionCodes) .  '/';
        preg_match_all($regex, $content, $matches);
        
        $foundCustomer = null;
        if (!empty($matches[0][0])) {
            $foundCustomer = $customerModel->find('all', [
                'conditions' => [
                    $customerModel->getPersonalTransactionCodeField() . ' = :personalTransactionCode'
                ]
            ])->bind(':personalTransactionCode', $matches[0][0], 'string')
            ->first();
        }
        return $foundCustomer;
    }
    
    public function __construct($document)
    {
        parent::__construct($document);
        
        // as long as there is only one bank implementation set default type in constructor
        // later use method setType($type)
        $this->setType(self::TYPE_RAIFFEISEN);
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
            
            // never import negative transactions
            $amount = Configure::read('app.numberHelper')->getStringAsFloat($record[3]);
            if ($amount <= 0) {
                continue;
            }
            
            $preparedRecord = [];
            $preparedRecord['content'] = h($record[1]);
            $preparedRecord['amount'] = $amount;
            $date = new FrozenTime($record[5]);
            $preparedRecord['date'] = $date->format(Configure::read('DateFormat.DatabaseWithTimeAndMicrosecondsAlt'));
            
            $customer = $this->getCustomerByPersonalTransactionCode($preparedRecord['content']);
            $preparedRecord['original_id_customer'] = !is_null($customer) ? $customer->id_customer : '';
            
            $preparedRecords[] = $preparedRecord;
            
        }
        
        $preparedRecords = Hash::sort($preparedRecords, '{n}.date', 'desc');
        
        return $preparedRecords;
    }
    
}

?>
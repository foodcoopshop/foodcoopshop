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

use App\Model\Entity\Customer;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use League\Csv\Reader;

abstract class BankingReader extends Reader implements BankingReaderInterface {

    public $dataContainsHeadline = true;

    public function __construct($document)
    {
        parent::__construct($document);
        $this->configureType();
    }

    protected function getCustomerByPersonalTransactionCode($content): ?Customer
    {
        $customerModel = FactoryLocator::get('Table')->get('Customers');
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

    public function getPreparedRecords(): array
    {

        if (!$this->checkStructure()) {
            throw new \Exception('structure of csv is not valid');
        }

        $records = $this->getRecords();
        $records = iterator_to_array($records);
        $records = $this->equalizeStructure($records);

        $preparedRecords = [];
        foreach($records as $record) {

            // never import negative transactions
            $amount = Configure::read('app.numberHelper')->getStringAsFloat($record['amount']);
            if ($amount <= 0) {
                continue;
            }

            $preparedRecord = [];
            $preparedRecord['content'] = h($record['content']);
            $preparedRecord['amount'] = $amount;
            $date = new FrozenTime($record['date']);
            $preparedRecord['date'] = $date->format(Configure::read('DateFormat.DatabaseWithTimeAndMicrosecondsAlt'));

            $customer = $this->getCustomerByPersonalTransactionCode($preparedRecord['content']);
            $preparedRecord['original_id_customer'] = !is_null($customer) ? $customer->id_customer : '';

            $preparedRecords[] = $preparedRecord;

        }

        $preparedRecords = Hash::sort($preparedRecords, '{n}.date', 'desc');

        return $preparedRecords;
    }

    public function checkStructure(): bool
    {
        $records = $this->getRecords();
        $records = iterator_to_array($records);

        $structureIsOk = false;
        foreach($records as $record) {
            $structureIsOk |= $this->checkStructureForRecord($record);
        }

        return $structureIsOk;
    }

}

?>
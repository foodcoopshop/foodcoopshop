<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Csv\Reader\Banking;

use App\Model\Entity\Customer;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use League\Csv\Reader;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

abstract class BankingReaderService extends Reader implements BankingReaderServiceInterface {

    public const ALLOWED_UPLOAD_MIME_TYPES = [
        'text/csv',
        'application/vnd.ms-excel', // for firefox
    ];
    
    public bool $csvHasIsoFormat = false;

    protected function getCustomerByPersonalTransactionCode($content): ?Customer
    {
        $customersTable = TableRegistry::getTableLocator()->get('Customers');
        $query = $customersTable->find('all',
            fields: [
                'personalTransactionCode' => $customersTable->getPersonalTransactionCodeField(),
            ]
        );
        $personalTransactionCodes = $query->all()->extract('personalTransactionCode')->toArray();

        $regex = '/' . join('|', $personalTransactionCodes) .  '/';
        preg_match_all($regex, $content, $matches);

        $foundCustomer = null;
        if (!empty($matches[0][0])) {
            $foundCustomer = $customersTable->find('all',
                conditions: [
                    $customersTable->getPersonalTransactionCodeField() . ' = :personalTransactionCode'
                ]
            )->bind(':personalTransactionCode', $matches[0][0], 'string')
            ->first();
        }
        return $foundCustomer;
    }

    public function getPreparedRecords(): array
    {
        $this->configureType();
        if (!$this->checkStructure()) {
            throw new \Exception(__('The_structure_of_the_uploaded_file_is_not_valid.'));
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
            $date = new DateTime($record['date']);
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
        $this->configureType();
        $records = $this->getRecords();

        $records = iterator_to_array($records);

        $structureIsOk = false;
        foreach($records as $record) {
            $structureIsOk |= $this->checkStructureForRecord($record);
        }

        return (bool) $structureIsOk;
    }

}

?>
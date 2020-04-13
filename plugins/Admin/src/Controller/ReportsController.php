<?php

namespace Admin\Controller;

use App\Lib\Csv\RaiffeisenBankingReader;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\ORM\Exception\PersistenceFailedException;

/**
 * ReportsController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ReportsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        if (isset($this->getRequest()->getParam('pass')[0])) {
            switch ($this->getRequest()->getParam('pass')[0]) {
                // allow deposit for cash configuration
                case 'deposit':
                    return $this->AppAuth->isSuperadmin();
                    break;
            }
        }
        return $this->AppAuth->isSuperadmin() && Configure::read('app.htmlHelper')->paymentIsCashless();
    }
    
    private function handleCsvUpload()
    {

        $this->Payment = TableRegistry::getTableLocator()->get('Payments');
        
        $csvPayments = [];
        $csvRecords = [];
        $saveRecords = false;
        if (!empty($this->getRequest()->getData('upload'))) {
            $upload = $this->getRequest()->getData('upload');
            $content = $upload->getStream()->getContents();
            $reader = RaiffeisenBankingReader::createFromString($content);
            try {
                $csvRecords = $reader->getPreparedRecords($reader->getRecords());
            } catch(\Exception $e) {
                $this->Flash->error(__d('admin', 'The_uploaded_file_is_not_valid.'));
                $this->redirect($this->referer());
            }
            
            foreach($csvRecords as &$csvRecord) {
                $csvRecord['already_imported'] = $this->Payment->isAlreadyImported($csvRecord['content']);
            }
            
        }
        
        if (!empty($this->getRequest()->getData('Payments'))) {
            $csvRecords = $this->getRequest()->getData('Payments');
            $saveRecords = true;
        }
        
        if (!empty($csvRecords)) {
            
            $csvPayments = $this->Payment->newEntities(
                $csvRecords,
                [
                    'validate' => 'csvImport',
                ]
            );
            
            try {
                foreach($csvPayments as &$csvPayment) {
                    
                    if (!isset($csvPayment->selected)) {
                        $csvPayment->selected = true;
                        if ($csvPayment->already_imported) {
                            $csvPayment->selected = false;
                        }
                    }
                    
                    $csvPayment = $this->Payment->patchEntity(
                        $csvPayment,
                        [
                            'date_transaction_add' => new FrozenTime($csvPayment->date),
                            'approval' => APP_ON,
                            'id_customer' => $csvPayment->original_id_customer == 0 ? $csvPayment->id_customer : $csvPayment->original_id_customer,
                            'transaction_text' => $csvPayment->content,
                            'created_by' => $this->AppAuth->getUserId(),
                        ]
                    );
                    
                }
                if ($saveRecords) {
                    
                    $this->Payment->getConnection()->transactional(function () use ($csvPayments) {
                        
                        $i = 0;
                        foreach($csvPayments as $csvPayment) {
                            if ($csvPayment->isDirty('selected') && $csvPayment->getOriginal('selected') == 0) {
                                unset($csvPayments[$i]);
                            }
                            $i++;
                        }
                        
                        if (empty($csvPayments)) {
                            $this->Flash->error(__d('admin', 'No_records_were_imported.'));
                            $this->redirect($this->referer());
                        }
                        
                        $success = $this->Payment->saveManyOrFail($csvPayments);
                        if ($success) {
                            $message = __d('admin', '{0,plural,=1{1_record_was} other{#_records_were}_successfully_imported.', [count($csvPayments)]);
                            $this->Flash->success($message);
                            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
                            $this->ActionLog->customSave('payment_product_csv_imported', $this->AppAuth->getUserId(), 0, 'payments', $message);
                            $this->redirect($this->referer());
                        }
                        
                    });
                        
                } else {
                    $this->Flash->success(__d('admin', 'Upload_successful._Please_select_the_records_you_want_to_import_and_then_click_save_button.'));
                    $this->set('csvPayments', $csvPayments);
                }
            } catch(PersistenceFailedException $e) {
                $this->Flash->error(__d('admin', 'Errors_while_saving!'));
                $this->set('csvPayments', $csvPayments);
            }
        }
    }

    public function payments($paymentType)
    {
        
        if ($paymentType == 'product' && !Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
            $this->handleCsvUpload();
        }
        
        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        }
        $this->set('customerId', $customerId);

        $conditions = [
            'Payments.type' => $paymentType
        ];
        $conditions[] = 'DATE_FORMAT(Payments.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        $conditions[] = 'DATE_FORMAT(Payments.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'';

        if ($customerId != '') {
            $conditions['Payments.id_customer'] = $customerId;
        }

        // exluce "empty_glasses" deposit payments for manufacturers
        $conditions[] = "((Payments.id_manufacturer > 0 && Payments.text = 'money') || Payments.id_manufacturer = 0)";
        
        $this->Payment = TableRegistry::getTableLocator()->get('Payments');
        $query = $this->Payment->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Customers',
                'Manufacturers',
                'CreatedByCustomers',
                'ChangedByCustomers'
            ]
        ]);

        $payments = $this->paginate($query, [
            'order' => [
                'Payments.date_add' => 'DESC'
            ]
        ]);
        $this->set('payments', $payments);

        $this->set('title_for_layout', __d('admin', 'Report') . ': ' . Configure::read('app.htmlHelper')->getPaymentText($paymentType));
        $this->set('paymentType', $paymentType);
        $this->set('showTextColumn', in_array($paymentType, array(
            'member_fee',
            'deposit'
        )));
    }
}

<?php

namespace Admin\Controller;

use App\Lib\Csv\RaiffeisenBankingReader;
use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\Exception\PersistenceFailedException;

/**
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

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->FormProtection->setConfig('unlockedActions', ['payments']);
    }

    private function handleCsvUpload()
    {

        $this->Payment = $this->getTableLocator()->get('Payments');

        $csvPayments = [];
        $csvRecords = [];
        $saveRecords = false;
        if (!empty($this->getRequest()->getData('upload'))) {
            $upload = $this->getRequest()->getData('upload');
            $content = $upload->getStream()->getContents();
            $reader = RaiffeisenBankingReader::createFromString($content);
            try {
                $csvRecords = $reader->getPreparedRecords($reader->getRecords());
                $this->Flash->success(__d('admin', 'Upload_successful._Please_select_the_records_you_want_to_import_and_then_click_save_button.'));
            } catch(\Exception $e) {
                $this->Flash->error(__d('admin', 'The_uploaded_file_is_not_valid.'));
                $this->redirect($this->referer());
            }

            foreach($csvRecords as &$csvRecord) {
                $csvRecord['already_imported'] = $this->Payment->isAlreadyImported($csvRecord['content'], $csvRecord['date']);
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
                    'validate' => 'csvImportUpload',
                ],
            );

            try {

                $paymentsHaveErrors = false;

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
                            'id_customer' => $csvPayment->id_customer ?? $csvPayment->original_id_customer,
                            'transaction_text' => $csvPayment->content,
                            'created_by' => $this->AppAuth->getUserId(),
                        ],
                        [
                            'validate' => 'csvImportSave',
                        ],
                    );

                    if ($csvPayment->selected && $csvPayment->hasErrors()) {
                        $paymentsHaveErrors |= true;
                    }

                }

                $this->set('csvPayments', $csvPayments);

                if ($paymentsHaveErrors && $saveRecords) {
                    $this->Flash->error(__d('admin', 'Errors_while_saving!'));
                }

                if (!$paymentsHaveErrors && $saveRecords) {

                    $this->Payment->getConnection()->transactional(function () use ($csvPayments) {

                        $i = 0;
                        $sumAmount = 0;
                        foreach($csvPayments as $csvPayment) {
                            if ($csvPayment->isDirty('selected') && $csvPayment->getOriginal('selected') == 0) {
                                unset($csvPayments[$i]);
                            } else {
                                $customer = $this->Payment->Customers->find('all', [
                                    'conditions' => [
                                        'id_customer' => $csvPayment->id_customer,
                                    ]
                                ])->first();
                                $sumAmount += $csvPayment->amount;
                                $email = new AppMailer();
                                $email->viewBuilder()->setTemplate('Admin.credit_csv_upload_successful');
                                $email->setTo($customer->email)
                                ->setSubject(__d('admin', 'Your_transaction_({0})_was_added_to_the_credit_system.', [
                                    Configure::read('app.numberHelper')->formatAsCurrency($csvPayment->amount),
                                ]))
                                ->setViewVars([
                                    'customer' => $customer,
                                    'csvPayment' => $csvPayment,
                                    'appAuth' => $this->AppAuth,
                                ]);
                                $email->send();
                            }
                            $i++;
                        }

                        if (empty($csvPayments)) {
                            $this->Flash->error(__d('admin', 'No_records_were_imported.'));
                            $this->redirect($this->referer());
                        }

                        $success = $this->Payment->saveManyOrFail($csvPayments);
                        if ($success) {
                            $message = __d('admin', '{0,plural,=1{1_record_was} other{#_records_were}_successfully_imported._Sum:_{1}', [
                                count($csvPayments),
                                '<b>' . Configure::read('app.numberHelper')->formatAsCurrency($sumAmount) . '</b>',
                            ]);
                            $this->Flash->success($message);
                            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
                            $this->ActionLog->customSave('payment_product_csv_imported', $this->AppAuth->getUserId(), 0, 'payments', $message);
                            $this->redirect($this->referer());
                        }

                    });
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

        if ($customerId != '') {
            $conditions['Payments.id_customer'] = $customerId;
        }

        // exluce "empty_glasses" deposit payments for manufacturers
        $conditions[] = "((Payments.id_manufacturer > 0 && Payments.text = 'money') || Payments.id_manufacturer = 0)";

        $this->Payment = $this->getTableLocator()->get('Payments');
        $query = $this->Payment->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Customers',
                'Manufacturers',
                'CreatedByCustomers',
                'ChangedByCustomers'
            ]
        ]);

        $query->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
            $exp->gte('DATE_FORMAT(Payments.date_add, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom));
            $exp->lte('DATE_FORMAT(Payments.date_add, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo));
            return $exp;
        });

        $payments = $this->paginate($query, [
            'order' => [
                'Payments.date_add' => 'DESC'
            ]
        ]);
        $this->set('payments', $payments);

        $this->set('customersForDropdown', $this->Payment->Customers->getForDropdown());
        $this->set('title_for_layout', __d('admin', 'Report') . ': ' . Configure::read('app.htmlHelper')->getPaymentText($paymentType));
        $this->set('paymentType', $paymentType);
        $this->set('showTextColumn', $paymentType == 'deposit');
    }
}

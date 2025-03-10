<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\EventInterface;
use Cake\ORM\Exception\PersistenceFailedException;
use App\Services\Csv\Reader\Banking\BankingReaderServiceFactory;
use Cake\I18n\DateTime;
use App\Model\Entity\Payment;
use App\Services\Csv\Reader\Banking\BankingReaderService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ReportsController extends AdminAppController
{

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->FormProtection->setConfig('unlockedActions', ['payments']);
    }

    private function handleCsvUpload(): void
    {

        $paymentsTable = $this->getTableLocator()->get('Payments');

        $csvPayments = [];
        $csvRecords = [];
        $saveRecords = false;
        if (!empty($this->getRequest()->getData('upload'))) {

            $upload = $this->getRequest()->getData('upload');
            if (!in_array($upload->getClientMediaType(), BankingReaderService::ALLOWED_UPLOAD_MIME_TYPES)) {
                $this->Flash->error(__d('admin', 'The_uploaded_file_is_not_valid.'));
                return;
            }

            $content = $upload->getStream()->getContents();
            $bankingReaderService = BankingReaderServiceFactory::get(Configure::read('app.bankNameForCreditSystem'));
            $reader = $bankingReaderService::createFromString($content);

            if ($reader->csvHasIsoFormat) {
                $reader->addStreamFilter('convert.iconv.ISO-8859-15/UTF-8');
            }

            try {
                $csvRecords = $reader->getPreparedRecords($reader->getRecords());
                $this->Flash->success(__d('admin', 'Upload_successful._Please_select_the_records_you_want_to_import_and_then_click_save_button.'));
            } catch(\Exception $e) {
                $this->Flash->error(__d('admin', 'The_uploaded_file_is_not_valid.'));
                $this->redirect($this->referer());
            }

            foreach($csvRecords as &$csvRecord) {
                $csvRecord['already_imported'] = $paymentsTable->isAlreadyImported($csvRecord['content'], $csvRecord['date']);
            }

        }

        if (!empty($this->getRequest()->getData('Payments'))) {
            $csvRecords = $this->getRequest()->getData('Payments');
            $saveRecords = true;
        }

        if (!empty($csvRecords)) {

            $csvPayments = $paymentsTable->newEntities(
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
                    $csvPayment = $paymentsTable->patchEntity(
                        $csvPayment,
                        [
                            'date_transaction_add' => new DateTime($csvPayment->date),
                            'approval' => APP_ON,
                            'id_customer' => $csvPayment->id_customer ?? $csvPayment->original_id_customer,
                            'transaction_text' => $csvPayment->content,
                            'created_by' => $this->identity->getId(),
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

                    $paymentsTable = $this->getTableLocator()->get('Payments');
                    $paymentsTable->getConnection()->transactional(function () use ($csvPayments) {

                        $i = 0;
                        $sumAmount = 0;
                        foreach($csvPayments as $csvPayment) {
                            if ($csvPayment->isDirty('selected') && $csvPayment->getOriginal('selected') == 0) {
                                unset($csvPayments[$i]);
                            } else {
                                $customersTable = $this->getTableLocator()->get('Customers');
                                $customer = $customersTable->find('all', conditions: [
                                    $customersTable->aliasField('id_customer') => $csvPayment->id_customer,
                                ])->first();
                                $sumAmount += $csvPayment->amount;

                                if ($customer->credit_upload_reminder_enabled) {
                                    $email = new AppMailer();
                                    $email->viewBuilder()->setTemplate('Admin.credit_csv_upload_successful');
                                    $email->setTo($customer->email)
                                    ->setSubject(__d('admin', 'Your_transaction_({0})_was_added_to_the_credit_system.', [
                                        Configure::read('app.numberHelper')->formatAsCurrency($csvPayment->amount),
                                    ]))
                                    ->setViewVars([
                                        'customer' => $customer,
                                        'newsletterCustomer' => $customer,
                                        'csvPayment' => $csvPayment,
                                        'identity' => $this->identity,
                                    ]);
                                    $email->addToQueue();
                                }
                            }
                            $i++;
                        }

                        if (empty($csvPayments)) {
                            $this->Flash->error(__d('admin', 'No_records_were_imported.'));
                            $this->redirect($this->referer());
                        }

                        $paymentsTable = $this->getTableLocator()->get('Payments');
                        $success = $paymentsTable->saveManyOrFail($csvPayments);
                        if ($success) {
                            $message = __d('admin', '{0,plural,=1{1_record_was} other{#_records_were}_successfully_imported._Sum:_{1}', [
                                count($csvPayments),
                                '<b>' . Configure::read('app.numberHelper')->formatAsCurrency($sumAmount) . '</b>',
                            ]);
                            $this->Flash->success($message);
                            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
                            $actionLogsTable->customSave('payment_product_csv_imported', $this->identity->getId(), 0, 'payments', $message);
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

    public function payments($paymentType): void
    {

        if ($paymentType == Payment::TYPE_PRODUCT && !Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
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

        // exclude "empty_glasses" deposit payments for manufacturers
        $conditions[] = "((Payments.id_manufacturer > 0 && Payments.text = 'money') || Payments.id_manufacturer = 0)";

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $customersTable = $this->getTableLocator()->get('Customers');

        $query = $paymentsTable->find('all',
        conditions: $conditions,
        contain: [
            'Customers',
            'Manufacturers',
            'CreatedByCustomers',
            'ChangedByCustomers'
        ]);

        $query->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
            $exp->gte('DATE_FORMAT(Payments.date_add, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom));
            $exp->lte('DATE_FORMAT(Payments.date_add, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo));
            return $exp;
        });

        $payments = $this->paginate($query, [
            'sortableFields' => [
                'Customers.' . Configure::read('app.customerMainNamePart'),
                'Payments.approval',
                'Payments.date_add',
                'CreatedByCustomers.' . Configure::read('app.customerMainNamePart'),
                'Payments.date_transaction_add',
                'Payments.amount',
            ],
            'order' => [
                'Payments.date_add' => 'DESC'
            ],
        ]);
        $this->set('payments', $payments);

        $this->set('customersForDropdown', $customersTable->getForDropdown());
        $this->set('title_for_layout', __d('admin', 'Report') . ': ' . Configure::read('app.htmlHelper')->getPaymentText($paymentType));
        $this->set('paymentType', $paymentType);
        $this->set('showTextColumn', $paymentType == Payment::TYPE_DEPOSIT);
    }
}

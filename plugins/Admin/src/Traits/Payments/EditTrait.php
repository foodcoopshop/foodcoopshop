<?php
declare(strict_types=1);

namespace Admin\Traits\Payments;

use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Mailer\AppMailer;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait EditTrait
{

    public function edit($paymentId): void
    {

        $this->set('title_for_layout', __d('admin', 'Check_credit_upload'));

        $this->setFormReferer();

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payment = $paymentsTable->find('all',
        conditions: [
            $paymentsTable->aliasField('id') => $paymentId,
            $paymentsTable->aliasField('type IN') => [
                Payment::TYPE_PRODUCT,
                Payment::TYPE_PAYBACK,
            ],
        ],
        contain: [
            'Customers',
            'ChangedByCustomers'
        ])->first();

        if (empty($payment)) {
            throw new RecordNotFoundException('payment not found');
        }

        if (empty($this->getRequest()->getData())) {
            $this->set('payment', $payment);
            return;
        }

        $payment = $paymentsTable->patchEntity(
            $payment,
            $this->getRequest()->getData(),
            [
                'validate' => 'edit'
            ]
        );

        if ($payment->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('payment', $payment);
        } else {
            $payment = $paymentsTable->patchEntity(
                $payment,
                [
                    'date_changed' => DateTime::now(),
                    'changed_by' => $this->identity->getId()
                ]
            );
            $payment = $paymentsTable->save($payment);

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogType = match($payment->approval) {
                -1 => 'payment_product_approval_not_ok',
                 0 => 'payment_product_approval_open',
                 1 => 'payment_product_approval_ok',
                 default => '',
            };

            $newStatusAsString = Configure::read('app.htmlHelper')->getApprovalStates()[$payment->approval];

            $message = __d('admin', 'The_status_of_the_credit_upload_for_{0}_was_successfully_changed_to_{1}.', ['<b>'.$payment->customer->name.'</b>', '<b>' .$newStatusAsString.'</b>']);

            if ($payment->send_email) {
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('Admin.payment_status_changed');
                $email->setTo($payment->customer->email)
                    ->setSubject(__d('admin', 'The_status_of_your_credit_upload_was_successfully_changed_to_{0}.', ['"' .$newStatusAsString.'"']))
                    ->setViewVars([
                        'identity' => $this->identity,
                        'data' => $payment->customer,
                        'newsletterCustomer' => $payment->customer,
                        'newStatusAsString' => $newStatusAsString,
                        'payment' => $payment
                    ]);
                $email->addToQueue();
                $message = __d('admin', 'The_status_of_the_credit_upload_for_{0}_was_successfully_changed_to_{1}_and_an_email_was_sent_to_the_member.', ['<b>'.$payment->customer->name.'</b>', '<b>' .$newStatusAsString.'</b>']);
            }

            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $payment->id, 'payments', $message . ' (PaymentId: ' . $payment->id.')');
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $payment->id);

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('payment', $payment);
    }

}
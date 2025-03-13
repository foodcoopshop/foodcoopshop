<?php
declare(strict_types=1);

namespace Admin\Traits\Payments;

use Cake\Core\Configure;
use Cake\I18n\DateTime;
use App\Model\Entity\Payment;

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

trait ChangeStatusTrait
{

    public function changeStatus(): void
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $paymentId = $this->getRequest()->getData('paymentId');

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payment = $paymentsTable->find('all',
        conditions: [
            'Payments.id' => $paymentId,
            'Payments.approval <> ' . APP_ON,
        ],
        contain: [
            'Customers',
            'Manufacturers',
        ])->first();

        if (empty($payment)) {
            $message = 'payment id ('.$paymentId.') not correct or already approved (approval: 1)';
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        // TODO add payment owner check (also for manufacturers!)
        $paymentsTable->save(
            $paymentsTable->patchEntity(
                $payment,
                [
                    'status' => APP_DEL,
                    'date_changed' => DateTime::now(),
                ]
            )
        );

        $actionLogType = $payment->type;
        if ($payment->type == Payment::TYPE_DEPOSIT) {
            $userType = 'customer';
            if ($payment->id_manufacturer > 0) {
                $userType = 'manufacturer';
            }
            $actionLogType .= '_'.$userType;
        }

        $message = __d('admin', 'The_payment_({0}_{1})_was_removed_successfully.', [
            Configure::read('app.numberHelper')->formatAsCurrency($payment->amount),
            Configure::read('app.htmlHelper')->getPaymentText($payment->type)]
        );
        if ($this->identity->isSuperadmin() && $this->identity->getId() != $payment->id_customer) {
            if (isset($payment->customer->name)) {
                $username = $payment->customer->name;
            } else {
                $username = $payment->manufacturer->name;
            }
            $message = __d('admin', 'The_payment_({0}_{1})_of_{2}_was_removed_successfully.', [
                Configure::read('app.numberHelper')->formatAsCurrency($payment->amount),
                Configure::read('app.htmlHelper')->getPaymentText($payment->type),
                $username
            ]);
        }

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('payment_' . $actionLogType . '_deleted', $this->identity->getId(), $paymentId, 'payments', $message . ' (PaymentId: ' . $paymentId . ')');

        $this->Flash->success($message);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}
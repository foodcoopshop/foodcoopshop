<?php
declare(strict_types=1);

namespace Admin\Traits\Payments;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Model\Entity\Payment;
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

trait PreviewEmailTrait
{

    public function previewEmail($paymentId, $approval): void
    {

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payment = $paymentsTable->find('all',
        conditions: [
            $paymentsTable->aliasField('id') => $paymentId,
            $paymentsTable->aliasField('type') => Payment::TYPE_PRODUCT,
        ],
        contain: [
            'Customers'
        ])->first();
        if (empty($payment)) {
            throw new RecordNotFoundException('payment not found');
        }

        if (!in_array($approval, [1,-1])) {
            throw new RecordNotFoundException('approval not implemented');
        }

        $payment->approval = $approval;
        $payment->approval_comment = __d('admin', 'Your_comment_will_be_shown_here.');
        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('Admin.payment_status_changed');
        $email->setTo($payment->customer->email)
            ->setViewVars([
                'identity' => $this->identity,
                'data' => $payment->customer,
                'newStatusAsString' => Configure::read('app.htmlHelper')->getApprovalStates()[$approval],
                'payment' => $payment
            ]);
        echo $email->render()->getMessage()->getBodyString();
        exit;
    }

}
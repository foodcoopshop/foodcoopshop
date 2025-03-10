<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Queue\Task;

use Queue\Queue\Task\EmailTask;
use Throwable;
use Cake\I18n\DateTime;
use App\Model\Entity\OrderDetail;
use Cake\ORM\TableRegistry;

class AppEmailTask extends EmailTask
{

    public ?int $timeout = 300;

    use UpdateActionLogTrait;

    public function run(array $data, int $jobId): void {

        try {
            $afterRunParams = $data['afterRunParams'];
            parent::run($data, $jobId);
        } catch(Throwable $e) {
            if (!empty($data['afterRunParams'])) {
                if (isset($afterRunParams['actionLogId']) && isset($afterRunParams['actionLogIdentifier']) ) {
                    $this->updateActionLogFailure($afterRunParams['actionLogId'], $afterRunParams['actionLogIdentifier'], $jobId, $e->getMessage());
                }
            }
            throw $e;
        }

        // if no exception is triggered, this part is reached
        // afterRunParams can be directly set to Mailer instance like
        // $email->afterRunParams['foo' => 'bar'];

        if (empty($data['afterRunParams'])) {
            return;
        }

        if (isset($afterRunParams['actionLogId']) && isset($afterRunParams['actionLogIdentifier']) ) {
            $this->updateActionLogSuccess($afterRunParams['actionLogId'], $afterRunParams['actionLogIdentifier'], $jobId);
        }

        if (isset($afterRunParams['manufacturerId']) && isset($afterRunParams['orderDetailIds'])) {
            $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
            $orderDetailsTable->updateOrderState(null, null, [OrderDetail::STATE_OPEN], OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER, $afterRunParams['manufacturerId'], $afterRunParams['orderDetailIds']);
        }

        if (isset($afterRunParams['invoiceId'])) {
            $invoicesTable = TableRegistry::getTableLocator()->get('Invoices');
            $invoiceId = $afterRunParams['invoiceId'];
            $invoiceEntity = $invoicesTable->patchEntity(
                $invoicesTable->get($invoiceId), [
                    'email_status' => DateTime::now(),
                ]
            );
            $invoicesTable->save($invoiceEntity);
        }

    }

}

?>
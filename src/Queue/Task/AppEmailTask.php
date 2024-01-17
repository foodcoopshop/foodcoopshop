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

use Cake\Datasource\FactoryLocator;
use Throwable;
use Queue\Model\QueueException;
use Cake\Mailer\TransportFactory;
use Cake\Mailer\Message;
use Cake\Log\Log;
use Queue\Queue\Task;

class AppEmailTask extends Task
{

    public ?int $timeout = 300;

    use UpdateActionLogTrait;

    public function run(array $data, int $jobId): void {

        try {
            $afterRunParams = $data['afterRunParams'];

            if (!isset($data['settings'])) {
                throw new QueueException('Queue Email task called without settings data.');
            }
    
            $message = $data['settings'];
		    if ($message && is_object($message) && $message instanceof Message) {
			try {
				$transport = TransportFactory::get($data['transport'] ?? 'default');
				$result = $transport->send($message);
			} catch (Throwable $e) {
				$error = $e->getMessage();
				$error .= ' (line ' . $e->getLine() . ' in ' . $e->getFile() . ')' . PHP_EOL . $e->getTraceAsString();
				Log::write('error', $error);

				throw $e;
			}

			if (!$result) {
				throw new QueueException('Could not send email.');
			}

			return;
		}

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
            $orderDetailTable = FactoryLocator::get('Table')->get('OrderDetails');
            $orderDetailTable->updateOrderState(null, null, [ORDER_STATE_ORDER_PLACED], ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER, $afterRunParams['manufacturerId'], $afterRunParams['orderDetailIds']);
        }

        if (isset($afterRunParams['invoiceId'])) {
            $invoiceTable = FactoryLocator::get('Table')->get('Invoices');
            $invoiceId = $afterRunParams['invoiceId'];
            $invoiceEntity = $invoiceTable->patchEntity(
                $invoiceTable->get($invoiceId), [
                    'email_status' => \Cake\I18n\DateTime::now(),
                ]
            );
            $invoiceTable->save($invoiceEntity);
        }

    }

}

?>
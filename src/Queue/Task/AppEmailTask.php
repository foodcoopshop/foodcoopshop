<?php

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Queue\Task;

use Queue\Queue\Task\EmailTask;
use Cake\Datasource\FactoryLocator;

class AppEmailTask extends EmailTask
{

    use UpdateActionLogTrait;

    public function run(array $data, int $jobId): void {

        parent::run($data, $jobId);

        if (!empty($data['afterRunParams'])) {
            $afterRunParams = $data['afterRunParams'];
            if (isset($afterRunParams['actionLogId']) && isset($afterRunParams['actionLogIdentifier']) ) {
                $this->updateActionLog($afterRunParams['actionLogId'], $afterRunParams['actionLogIdentifier'], $jobId);
            }
            if (isset($afterRunParams['manufacturerId']) && isset($afterRunParams['orderDetailIds'])) {
                $orderDetailTable = FactoryLocator::get('Table')->get('OrderDetails');
                $orderDetailTable->updateOrderState(null, null, [ORDER_STATE_ORDER_PLACED], ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER, $afterRunParams['manufacturerId'], $afterRunParams['orderDetailIds']);
            }
        }

    }

}

?>
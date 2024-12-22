<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use App\Model\Table\PickupDaysTable;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait EditPickupDayCommentTrait 
{

    protected PickupDaysTable $PickupDay;

    public function editPickupDayComment()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $customerId = $this->getRequest()->getData('customerId');
        $pickupDay = $this->getRequest()->getData('pickupDay');
        $pickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        $pickupDayComment = htmlspecialchars_decode(strip_tags(trim($this->getRequest()->getData('pickupDayComment')), '<strong><b>'));

        $customersTable = $this->getTableLocator()->get('Customers');
        $customer = $customersTable->find('all',
            conditions: [
                'id_customer' => $customerId,
            ]
        )->first();

        $this->PickupDay = $this->getTableLocator()->get('PickupDays');
        $result = $this->PickupDay->insertOrUpdate(
            [
                'customer_id' => $customerId,
                'pickup_day' => $pickupDay
            ],
            [
                'comment' => $pickupDayComment
            ]
        );

        $this->Flash->success(__d('admin', 'The_comment_was_changed_successfully.'));

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('order_comment_changed', $this->identity->getId(), $customerId, 'customers', __d('admin', 'The_pickup_day_comment_of_{0}_was_changed:', [$customer->name]) . ' <div class="changed">' . $pickupDayComment . ' </div>');

        $this->set([
            'result' => $result,
            'status' => !empty($result),
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['result', 'status', 'msg']);

    }

}

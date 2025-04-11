<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

use App\Services\OrderDetailCancellationService;

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

trait DeleteTrait
{

    use UpdateOrderDetailsTrait;

    public function delete(): void
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $orderDetailIds = $this->getRequest()->getData('orderDetailIds');
        $cancellationReason = strip_tags(html_entity_decode($this->getRequest()->getData('cancellationReason')));

        if (!(is_array($orderDetailIds))) {
            $this->set([
                'status' => 0,
                'msg' => 'param needs to be an array, given: ' . $orderDetailIds,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $orderDetailCancellationService = new OrderDetailCancellationService();
        $flashMessage = $orderDetailCancellationService->delete($orderDetailIds, $cancellationReason);
        $this->Flash->success($flashMessage);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}

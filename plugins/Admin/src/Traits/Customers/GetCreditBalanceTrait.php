<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait GetCreditBalanceTrait {

    public function getCreditBalance($customerId)
    {
        $this->request = $this->request->withParam('_ext', 'json');
        $customersTable = $this->getTableLocator()->get('Customers');
        $creditBalance = $customersTable->getCreditBalance($customerId);

        $this->set([
            'status' => 1,
            'creditBalance' => '<span class="'.($creditBalance < 0 ? 'negative' : '').'">' . Configure::read('app.numberHelper')->formatAsCurrency($creditBalance) . '</span>',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'creditBalance']);
    }

}
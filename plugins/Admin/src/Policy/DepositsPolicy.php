<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class DepositsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if ($identity === null) {
            return false;
        }

        return match($request->getParam('action')) {
            'overviewDiagram' => Configure::read('app.isDepositEnabled') && $identity->isSuperadmin(),
            'index', 'detail' => Configure::read('app.isDepositEnabled') && $identity->isSuperadmin() || $identity->isAdmin(),
            'myIndex', 'myDetail' => Configure::read('app.isDepositEnabled') && $identity->isManufacturer(),
             default => Configure::read('app.isDepositEnabled') && $identity->isManufacturer(),
        };
    
    }

}
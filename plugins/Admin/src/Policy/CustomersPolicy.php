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
class CustomersPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if ($identity === null) {
            return false;
        }

        return match($request->getParam('action')) {
            'generateMemberCards' => Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($identity->isSuperadmin() || $identity->isAdmin()),
            'generateMyMemberCard' => Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($identity->isSuperadmin() || $identity->isAdmin() || $identity->isCustomer()),
            'creditBalanceSum', 'delete' => $identity->isSuperadmin(),
            'profile' => $identity->isSuperadmin() || $identity->isAdmin() || $identity->isCustomer(),
            'changePassword', 'ajaxGetCustomersForDropdown' => $identity !== null,
            default => $identity->isSuperadmin() || $identity->isAdmin(),
        };
    
    }

}
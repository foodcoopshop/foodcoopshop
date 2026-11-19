<?php
declare(strict_types=1);

namespace App\Services;

use Authentication\IdentityInterface as AuthenticationIdentityInterface;
use Authorization\IdentityInterface as AuthorizationIdentityInterface;
use Cake\Http\ServerRequest;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 *
 * Shopping lists are available for members (incl. admins) that order for themselves.
 * Not available for manufacturers, self service customers, instant orders and the self service mode.
 */
class ShoppingListAccessService
{

    public static function isEnabled(AuthenticationIdentityInterface|AuthorizationIdentityInterface|null $identity, ServerRequest $request): bool
    {
        if ($identity === null) {
            return false;
        }
        /** @var \App\Model\Entity\Customer $identity */
        if ($identity->isManufacturer() || $identity->isSelfServiceCustomer()) {
            return false;
        }
        if ($request->getSession()->check('OrderIdentity')) {
            return false;
        }
        return true;
    }

}

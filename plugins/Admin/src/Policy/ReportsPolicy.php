<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;
use Authorization\Policy\ResultInterface;
use Authorization\IdentityInterface;

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
class ReportsPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if ($identity === null) {
            return false;
        }

        if (isset($request->getParam('pass')[0]) && $request->getParam('pass')[0] == 'deposit') {
            // allow deposit for cash configuration
            return $identity->isSuperadmin();
        }

        return $identity->isSuperadmin() && Configure::read('app.htmlHelper')->paymentIsCashless();

    }

}
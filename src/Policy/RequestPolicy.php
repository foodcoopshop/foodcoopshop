<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
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
class RequestPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {
        $plugin = $request->getParam('plugin');
        $controller = $request->getParam('controller');

        $policy = 'App\\Policy\\' . $controller . 'Policy';

        if ($plugin == 'DebugKit') {
            return true;
        }

        if ($plugin == 'Admin') {
            $policy = 'Admin\\Policy\\' . $controller . 'Policy';
        }

        if ($plugin == 'Network') {
            $policy = 'Network\\Policy\\' . $controller . 'Policy';
        }

        if (class_exists($policy)) {
            return (new $policy())->canAccess($identity, $request);
        }

        // !sic default == true to throw correct 404Error for not available files 
        return true;

    }

}
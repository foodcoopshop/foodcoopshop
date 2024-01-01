<?php
declare(strict_types=1);

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
namespace App\Services;

use Cake\Routing\Router;
use App\Model\Entity\Customer;
use Cake\Datasource\FactoryLocator;
use Authorization\Identity;

class IdentityService
{

    /**
     * creating an empty entity is necessary to globally use $identity->isLoggedIn() and other methods
     * even if user is not logged in, a valid customer entity is returned
     */
    public function getIdentity(): Customer|Identity
    {
        $identity = Router::getRequest()->getAttribute('identity');
        if ($identity === null) {
            $customerTable = FactoryLocator::get('Table')->get('Customers');
            $identity = $customerTable->newEmptyEntity();
        }
        return $identity;
    }

}
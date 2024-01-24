<?php
declare(strict_types=1);

namespace Network\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Datasource\FactoryLocator;
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
class SyncsPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if ($identity === null) {
            return false;
        }

        if (!$identity->isManufacturer()) {
            return false;
        }

        $syncDomainTable = FactoryLocator::get('Table')->get('Network.SyncDomains');
        $syncManufacturerTable = FactoryLocator::get('Table')->get('Network.SyncManufacturers');
        $isAllowedToUseAsMasterFoodcoop = $syncManufacturerTable->isAllowedToUseAsMasterFoodcoop($identity);
        $syncDomains = $syncDomainTable->getActiveManufacturerSyncDomains(
            $identity->getManufacturerEnabledSyncDomains()
        );

        return $isAllowedToUseAsMasterFoodcoop && count($syncDomains) > 0;

    }

}
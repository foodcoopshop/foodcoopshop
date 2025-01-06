<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace Network\Model\Table;

use App\Model\Table\ManufacturersTable;

class SyncManufacturersTable extends ManufacturersTable
{

    public function isAllowedToUseAsMasterFoodcoop($identity): bool
    {
        $isAllowed =
            $identity->isManufacturer() &&
            $this->getOptionVariableMemberFee(
                $identity->getManufacturerVariableMemberFee()
            ) == 0;
        return $isAllowed;
    }
}

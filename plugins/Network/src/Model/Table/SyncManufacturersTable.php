<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace Network\Model\Table;

use App\Model\Table\ManufacturersTable;

class SyncManufacturersTable extends ManufacturersTable
{

    public function isAllowedToUseAsMasterFoodcoop($appAuth)
    {
        $isAllowed =
            $appAuth->isManufacturer() &&
            $this->getOptionVariableMemberFee(
                $appAuth->getManufacturerVariableMemberFee()
            ) == 0;
        return $isAllowed;
    }
}

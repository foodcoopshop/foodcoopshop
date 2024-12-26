<?php
declare(strict_types=1);

namespace App\Model\Entity;

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
class Cart extends AppEntity
{

    const TYPE_WEEKLY_RHYTHM = 1;
    const TYPE_INSTANT_ORDER = 2;
    const TYPE_SELF_SERVICE  = 3;

    const SELF_SERVICE_PAYMENT_TYPE_CASH   = 1;
    const SELF_SERVICE_PAYMENT_TYPE_CREDIT = 2;

}

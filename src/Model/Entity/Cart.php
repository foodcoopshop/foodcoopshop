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

    /**
     * fields that must never be set via mass assignment (newEntity / patchEntity with request data)
     * set them explicitly or use the "accessibleFields" option
     * @var array<string, bool>
     */
    protected array $_accessible = [
        '*' => true,
        'id_cart' => false,
        'id_customer' => false,
        'cart_type' => false,
        'status' => false,
    ];

    const TYPE_WEEKLY_RHYTHM = 1;
    const TYPE_INSTANT_ORDER = 2;
    const TYPE_SELF_SERVICE  = 3;

    const SELF_SERVICE_PAYMENT_TYPE_CASH   = 1;
    const SELF_SERVICE_PAYMENT_TYPE_CREDIT = 2;

}

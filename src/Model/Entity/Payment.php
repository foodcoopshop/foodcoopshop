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
class Payment extends AppEntity
{

    const TYPE_DEPOSIT = 'deposit';
    const TYPE_PAYBACK = 'payback';
    const TYPE_PRODUCT = 'product';

    const TEXT_EMPTY_GLASSES = 'empty_glasses';
    const TEXT_MONEY = 'money';

    const ALLOWED_CUSTOMER_TYPES = [
        self::TYPE_DEPOSIT,
        self::TYPE_PAYBACK,
        self::TYPE_PRODUCT,
    ];

    const ALLOWED_MANUFACTURER_TYPES = [
        self::TYPE_DEPOSIT,
    ];

    const MAX_AMOUNTS_CUSTOMER = [
        self::TYPE_DEPOSIT => 20,
        self::TYPE_PAYBACK => 200,
        self::TYPE_PRODUCT => 200,
    ];

    const MAX_AMOUNTS_MANUFACTURER = [
        self::TYPE_DEPOSIT => 100,
    ];

}

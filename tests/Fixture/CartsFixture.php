<?php
declare(strict_types=1);

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

namespace App\Test\Fixture;

use App\Model\Entity\Cart;
use Cake\TestSuite\Fixture\TestFixture;

class CartsFixture extends TestFixture
{
    public string $table = 'fcs_carts';

    public array $records = [
        [
            'id_cart' => 1,
            'id_customer' => 92,
            'cart_type' => Cart::TYPE_WEEKLY_RHYTHM,
            'status' => 0,
            'created' => '2018-03-01 10:17:14',
            'modified' => '2018-03-01 10:17:14',
        ],
    ];

}
?>
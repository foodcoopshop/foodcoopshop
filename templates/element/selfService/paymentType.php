<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use App\Model\Entity\Cart;

if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    return false;
}

$cartsTable = FactoryLocator::get('Table')->get('Carts');
$paymentTypeAsString = __('Credit');
if ($paymentType == Cart::SELF_SERVICE_PAYMENT_TYPE_CASH) {
    $paymentTypeAsString =  __('Cash');
    $paymentTypeAsString .= '/Karte';
}

echo '<p class="payment-type">' .  __('Payment_type') . ': ' . $paymentTypeAsString . '</p>';

?>
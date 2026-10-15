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

use Cake\Core\Configure;
use App\Model\Entity\Customer;

$buttons[] = $this->element('copyEmailButton', [
    'object' => 'customer',
]);
if (Customer::getEnabledInfoChannels() !== []) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Admin.initCopySelectedContactDetailsToClipboard('customer', 'mobile');",
    ]);
    $buttons[] = '<a class="btn-clipboard-mobile dropdown-item"><i class="fas fa-mobile-alt"></i> ' . __('Copy mobile numbers') . '</a>';
}

$buttons[] = $this->element('customerList/button/generateMemberCardsOfSelectedCustomers');
$buttons[] = $this->element('customerList/button/exportCustomers');

echo $this->element('dropdownWithButtons', [
    'helperLink' => $helperLink,
    'buttons' => $buttons,
    'label' => __('Actions') . '...',
]);

?>
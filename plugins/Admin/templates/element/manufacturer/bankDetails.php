<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    return;
}

echo '<h2>'.__('Bank_account_data').' <span>'.__('are_not_visible_in_public_and_are_only_used_for_transferring_your_proceeds.').'</span></h2>';
echo $this->Form->control('Manufacturers.bank_name', [
    'label' => __('Bank'),
]);
echo $this->Form->control('Manufacturers.bank_account_holder', [
    'label' => __('Account holder') . '<br /><span class="small">'.__('exact wording').'</span>',
    'escape' => false,
]);
echo $this->Form->control('Manufacturers.iban', [
    'label' => __('IBAN'),
    'maxLength' => '',
]);
echo $this->Form->control('Manufacturers.bic', [
    'label' => __('BIC'),
    'maxLength' => '',
]);
echo '<div class="sc"></div>';


?>
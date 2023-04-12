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

use Cake\Core\Configure;
use Cake\I18n\I18n;

if (!Configure::read('app.rightOfWithdrawalEnabled')) {
    return false;
}

echo '<div id="cancellation-terms" class="hide">';
    echo $this->element('legal/'.I18n::getLocale().'/rightOfWithdrawalTerms');
echo '</div>';
$cancellationTermsLink = '<a data-element-selector="#cancellation-terms" href="javascript:void(0);" class="open-with-modal">'.__('right_of_withdrawal').'</a>';
echo $this->Form->control('Carts.cancellation_terms_accepted', [
    'label' => __('I_accept_the_{0}_and_accept_that_it_is_not_valid_for_perishable_goods.', [$cancellationTermsLink]),
    'type' => 'checkbox',
    'escape' => false
]);

?>
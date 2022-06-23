<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<ul class="nav nav-tabs">
    <?php
    $tabs = $this->Html->getReportTabs();
    foreach ($tabs as $tab) {
        $btnClass = '';
        if ($tab['key'] == $key) {
            $btnClass = 'active';
        }
        if ($this->Html->paymentIsCashless() || in_array($tab['key'], ['deposit', 'credit-balance-sum'])) {

            $dateParams = '?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo;
            if (in_array($tab['key'], ['profit'])) {
                $dateParams = '';
            }
            echo '<li class="' . $btnClass . '"><a href="' . $tab['url'] . $dateParams . '">' . $tab['name'] . '</a></li>';

        }

    }
?>
</ul>

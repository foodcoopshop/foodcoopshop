<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<ul class="nav nav-tabs">
    <?php
    $tabs = $this->Html->getReportTabs();
    foreach ($tabs as $tab) {
        $btnClass = '';
        if ($tab['key'] == $key) {
            $btnClass = 'active';
        }
        // show deposit report also for cash configuration
        if ($this->Html->paymentIsCashless() || in_array($tab['key'], ['deposit', 'member_fee', 'member_fee_flexible', 'credit-balance-sum'])) {
            echo '<li class="' . $btnClass . '"><a href="' . $tab['url'] . '?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '">' . $tab['name'] . '</a></li>';
        }
    }
?>
</ul>

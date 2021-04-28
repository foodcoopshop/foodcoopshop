<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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
        if (($this->Html->paymentIsCashless() && Configure::read('app.isDepositPaymentCashless')) || in_array($tab['key'], ['deposit', 'credit-balance-sum'])) {
            echo '<li class="' . $btnClass . '"><a href="' . $tab['url'] . '?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '">' . $tab['name'] . '</a></li>';
        }
    }
?>
</ul>

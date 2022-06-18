<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<ul class="nav nav-tabs">
    <?php
    $tabs = $this->Network->getTabs();
    foreach ($tabs as $tab) {
        $btnClass = '';
        if ($tab['url'] == $url) {
            $btnClass = 'active';
        }
        echo '<li class="' . $btnClass . '">';
            echo '<a href="' . $tab['url'] .  '">'.$tab['name'].'</a>';
        echo '</li>';
    }
?>
</ul>
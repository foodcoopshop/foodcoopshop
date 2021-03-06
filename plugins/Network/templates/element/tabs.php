<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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
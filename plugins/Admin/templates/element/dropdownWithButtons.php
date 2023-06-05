<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */



$buttons = array_filter($buttons); // remove empty array elements

$buttons[] = '<hr class="dropdown-divider" />';
$buttons[] = '<a class="dropdown-item" href="javascript:window.print();" target="_blank"><i class="fas fa-print fa-fw"></i> ' .  __d('admin', 'Print_page') . '</a>';
$buttons[] = '<a class="dropdown-item" href="' . $helperLink . '" target="_blank"><i class="fas fa-question fa-fw"></i> ' .  __d('admin', 'Help') . '</a>';

?>

<div class="dropdown">
    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-angles-right"></i> <?php echo $label; ?>
    </button>
    <ul class="dropdown-menu">
        <?php
            foreach($buttons as $button) {
                echo '<li>';
                    echo $button;
                echo '</li>';
            }
        ?>
    </ul>
</div>

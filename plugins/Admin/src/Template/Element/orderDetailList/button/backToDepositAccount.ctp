<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if ($deposit != '') {
    $depositOverviewUrl = $this->Slug->getDepositList($manufacturerId);
    if ($appAuth->isManufacturer()) {
        $depositOverviewUrl = $this->Slug->getMyDepositList();
    }
    echo '<a class="btn btn-outline-light" href="'.$depositOverviewUrl.'"><i class="fas fa-arrow-circle-left"></i> ' . __d('admin', 'Back_to_deposit_account') . '</a>';
}

?>

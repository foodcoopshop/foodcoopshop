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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?><div id="info-box" class="box">
    <?php
        echo preg_replace('/{'.__('DELIVERY_DAY').'}/', $this->Time->getDeliveryDateByCurrentDayFormattedWithWeekday(), Configure::read('appDb.FCS_RIGHT_INFO_BOX_HTML'));
    ?>
</div>
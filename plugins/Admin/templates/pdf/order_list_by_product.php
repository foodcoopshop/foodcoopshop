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
echo $this->element('pdf/order_list', [
    'pdf' => $pdf,
    'groupType' => 'product',
    'groupTypeLabel' => __d('admin', 'product'),
    'results' => $productResults,
    'manufacturer' => isset($manufacturer) ? $manufacturer : [],
    'currentDateForOrderLists' => isset($currentDateForOrderLists) ? $currentDateForOrderLists : null
]);

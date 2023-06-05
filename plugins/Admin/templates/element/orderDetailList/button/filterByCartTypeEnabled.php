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


$queryString = '';
$queryParams = $this->request->getQueryParams() ?? [];
$queryParams['filterByCartTypeEnabled'] = !$filterByCartTypeEnabled;
$queryString = '?' . http_build_query($queryParams);
$href = $this->request->getUri()->getPath() . $queryString;

echo '<a href="' . $href . '" class="dropdown-item"><i class="fas fa-shopping-cart fa-fw ok"></i> ' .  __d('admin', 'Filter_by_cart_type') . ': ' . ($filterByCartTypeEnabled ? __d('admin', 'yes') : __d('admin', 'no')) . '</a>';

?>
<?php
declare(strict_types=1);

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

if (isset($_SERVER['REQUEST_URI'])) {
    echo '<b>RequestUri</b>:' .$_SERVER['REQUEST_URI'].'<br /><br />';
}

if (!empty($_SERVER['HTTP_REFERER'])) {
    echo '<b>Referer</b>:' .$_SERVER['HTTP_REFERER'].'<br /><br />';
}

echo '<b>Message</b><br />';
echo '<pre>';
    print_r($message);
echo '</pre>';
echo '<br /><br />';

echo '<b>Request</b><br />';
echo '<pre>';
    print_r($_REQUEST);
echo '</pre>';
echo '<br /><br />';

echo '<b>User</b><br />';
echo '<pre>';
    print_r($identity);
echo '</pre>';
echo '<br /><br />';

echo '<b>Server</b><br />';
echo '<pre>';
    print_r($_SERVER);
echo '</pre>';
echo '<br /><br />';

echo '<b>Files</b><br />';
echo '<pre>';
    print_r($_FILES);
echo '</pre>';
echo '<br /><br />';

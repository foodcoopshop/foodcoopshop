<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (Configure::read('debug')) {
    $this->layout = 'dev_error';
    $this->assign('title', $message);
    $this->start('file');
    $this->end();
} else {
    $this->layout = 'error';
    echo '<a href="/"><img id="installation-logo" src="/files/images/' . Configure::read('app.logoFileName') . '" /></a>';
    echo '<h2>'.$message.'</h2>';
}
?>

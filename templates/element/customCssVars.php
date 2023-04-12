<?php
declare(strict_types=1);

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

use Cake\Core\Configure;

?>

<style>
    :root {
        --theme-color: <?php echo Configure::read('app.customThemeMainColor'); ?>;
        --logo-width: <?php echo Configure::read('app.logoWidth'); ?>px;
        --logo-max-height: <?php echo Configure::read('app.logoMaxHeight') . (Configure::read('app.logoMaxHeight') != 'auto' ? 'px' : ''); ?>;
    }
</style>
<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div class="logo-wrapper <?php echo Configure::read('app.selfServiceEasyModeEnabled') ? 'no-pointer' : ''?>">
    <a class="not-in-moblie-menu" href="<?php echo $this->Slug->getHome(); ?>" title="<?php echo __('Home'); ?>">
        <img class="logo" src="/files/images/<?php echo Configure::read('app.logoFileName'); ?>?<?php echo filemtime(WWW_ROOT.'files'.DS.'images'.DS.Configure::read('app.logoFileName'))?>" />
    </a>
</div>
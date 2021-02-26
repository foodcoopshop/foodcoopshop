<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div class="logo-wrapper">
    <a class="not-in-moblie-menu" href="<?php echo $this->Slug->getHome(); ?>" title="<?php echo __('Home'); ?>">
        <img class="logo" src="/files/images/<?php echo Configure::read('app.logoFileName'); ?>?<?php echo filemtime(WWW_ROOT.'files'.DS.'images'.DS.Configure::read('app.logoFileName'))?>" />
    </a>
</div>
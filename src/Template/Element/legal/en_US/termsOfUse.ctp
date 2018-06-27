<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<h1>Terms of use</h1> 

<h2>Platform owner</h2>

<p>
    <?php
    if (Configure::read('appDb.FCS_PLATFORM_OWNER') != '') {
        echo Configure::read('appDb.FCS_PLATFORM_OWNER');
    } else {
        echo Configure::read('appDb.FCS_APP_NAME');
        echo '<br />'.$this->MyHtml->getAddressFromAddressConfiguration();
    }
    ?>
</p>

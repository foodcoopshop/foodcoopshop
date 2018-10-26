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
use Cake\Core\Configure;

if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isManufacturer()) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace').".Admin.initEmailToAllButton();"
    ]);
    echo '<button data-email-addresses="'.join(',', $emailAddresses).'" class="email-to-all btn btn-outline-light"><i class="fa fa-envelope-o"></i> '.__d('admin', 'Copy_all_email_addresses').'</button>';
}

?>
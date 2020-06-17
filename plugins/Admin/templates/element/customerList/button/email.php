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

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace').".Admin.initCopySelectedCustomerEmailsToClipboard();"
]);
echo '<button class="btn-clipboard btn btn-outline-light"><i class="far fa-envelope"></i> '.__d('admin', 'Copy_email_addresses_of_selected_customers').'</button>';

?>
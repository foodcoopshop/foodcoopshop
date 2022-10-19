<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if ($appAuth->isSuperadmin() || $appAuth->isAdmin() || $appAuth->isManufacturer()) {

    if ($appAuth->isManufacturer() && $appAuth->getManufacturerAnonymizeCustomers()) {
        return;
    }

    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace').".Admin.initEmailToAllButton();"
    ]);
    echo '<button data-clipboard-text="'.join(',', $emailAddresses).'" class="btn-clipboard btn btn-outline-light"><i class="far fa-envelope"></i> '.__d('admin', 'Copy_all_email_addresses').'</button>';
}

?>
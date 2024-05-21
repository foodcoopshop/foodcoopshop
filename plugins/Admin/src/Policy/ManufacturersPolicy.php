<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;
use Authorization\Policy\ResultInterface;
use Authorization\IdentityInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ManufacturersPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if ($identity === null) {
            return false;
        }

        return match($request->getParam('action')) {
            'profile', 'myOptions' => $identity->isManufacturer(),
            'index', 'export', 'add' => $identity->isSuperadmin() || $identity->isAdmin(),
            'edit', 'editOptions', 'getOrderListByProduct', 'getOrderListByCustomer', 'getInvoice' => 
                $identity->isSuperadmin() || $identity->isAdmin(),
            'getDeliveryNote' => Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && $identity->isSuperadmin(),
            'getInvoice' => !Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && !Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && ($identity->isSuperadmin() || $identity->isAdmin()),
            'setElFinderUploadPath' => $identity->isSuperadmin() || $identity->isAdmin() || $identity->isManufacturer(),
             default =>  $identity === null
        };

    }

}
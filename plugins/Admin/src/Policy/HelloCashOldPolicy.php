<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
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
class HelloCashOldPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if ($identity === null) {
            return false;
        }

        $isAllowed = Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') &&
            Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED') &&
            ($identity->isSuperadmin() || $identity->isAdmin() || $identity->isCustomer());

        if ($identity->isCustomer()) {
            $invoiceId = $request->getParam('pass')[0];
            $invoiceTable = FactoryLocator::get('Table')->get('Invoices');
            $invoice = $invoiceTable->find('all',
                conditions : [
                    'Invoices.id' => $invoiceId,
                    'Invoices.id_customer' => $identity->getId(),
                ],
            )->first();
            $isAllowed = !empty($invoice);
        }

        return $isAllowed;
        
    }

}
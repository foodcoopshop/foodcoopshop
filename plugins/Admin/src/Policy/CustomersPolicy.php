<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;

class CustomersPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if ($identity === null) {
            return false;
        }

        return match($request->getParam('action')) {
            'generateMemberCards' => Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($identity->isSuperadmin() || $identity->isAdmin()),
            'generateMyMemberCard' => Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($identity->isSuperadmin() || $identity->isAdmin() || $identity->isCustomer()),
            'creditBalanceSum', 'delete' => $identity->isSuperadmin(),
            'profile' => $identity->isSuperadmin() || $identity->isAdmin() || $identity->isCustomer(),
            'changePassword', 'ajaxGetCustomersForDropdown' => $identity !== null,
            default => $identity->isSuperadmin() || $identity->isAdmin(),
        };
    
    }

}
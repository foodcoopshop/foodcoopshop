<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;

class SelfServicePolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if (!(Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && $identity !== null)) {
            return false;
        }

        return true;

    }

}
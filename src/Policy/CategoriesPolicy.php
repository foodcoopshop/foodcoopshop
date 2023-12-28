<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;

class CategoriesPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {
        if (! (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $identity !== null)) {
            return false;
        }
        return true;
    }

}
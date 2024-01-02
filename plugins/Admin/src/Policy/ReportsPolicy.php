<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;

class ReportsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if ($identity === null) {
            return false;
        }

        if (isset($request->getParam('pass')[0]) && $request->getParam('pass')[0] == 'deposit') {
            // allow deposit for cash configuration
            return $identity->isSuperadmin();
        }

        return $identity->isSuperadmin() && Configure::read('app.htmlHelper')->paymentIsCashless();

    }

}
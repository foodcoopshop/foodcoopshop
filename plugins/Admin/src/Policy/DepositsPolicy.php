<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;

class DepositsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if ($identity === null) {
            return false;
        }

        return match($request->getParam('action')) {
            'overviewDiagram' => Configure::read('app.isDepositEnabled') && $identity->isSuperadmin(),
            'index', 'detail' => Configure::read('app.isDepositEnabled') && $identity->isSuperadmin() || $identity->isAdmin(),
            'myIndex', 'myDetail' => Configure::read('app.isDepositEnabled') && $identity->isManufacturer(),
             default => Configure::read('app.isDepositEnabled') && $identity->isManufacturer(),
        };
    
    }

}
<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;

class FeedbacksPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if ($identity === null) {
            return false;
        }

        return match($request->getParam('action')) {
            'myFeedback' => Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $identity !== null,
             default => Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $identity->isSuperadmin(),
        };
    
    }

}
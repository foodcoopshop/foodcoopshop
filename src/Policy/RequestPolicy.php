<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;

class RequestPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {
        
        $plugin = $request->getParam('plugin');
        $controller = $request->getParam('controller');

        if ($plugin == 'DebugKit') {
            return true;
        }

        $policy = match($plugin) {
            'Admin' => 'App\\Policy\\AdminPolicy',
            default => 'App\\Policy\\' . $controller . 'Policy',
        };

        if (class_exists($policy)) {
            return (new $policy())->canAccess($identity, $request);
        }

        // !sic default == true to throw correct 404Error for not available files 
        return true;

    }

}
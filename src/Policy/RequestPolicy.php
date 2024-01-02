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

        $policy = 'App\\Policy\\' . $controller . 'Policy';

        if ($plugin == 'DebugKit') {
            return true;
        }

        if ($plugin == 'Admin') {
            $policy = 'Admin\\Policy\\' . $controller . 'Policy';
        }

        if (class_exists($policy)) {
            return (new $policy())->canAccess($identity, $request);
        }

        // !sic default == true to throw correct 404Error for not available files 
        return true;

    }

}
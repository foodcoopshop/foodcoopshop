<?php
declare(strict_types=1);

namespace App\Authenticator;

use Authentication\Authenticator\FormAuthenticator;
use Psr\Http\Message\ServerRequestInterface;

class AppFormAuthenticator extends FormAuthenticator
{

    protected function _getData(ServerRequestInterface $request): ?array
    {
        $fields = $this->_config['fields'];

        $body = $request->getParsedBody();

        $data = [];
        foreach ($fields as $key => $field) {
            $value = $body[$field] ?? '';
            $data[$key] = $value;
        }
        return $data;
    }

}

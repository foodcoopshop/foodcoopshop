<?php
declare(strict_types=1);

namespace App\Middleware\UnauthorizedHandler;

use Authorization\Exception\Exception;
use Authorization\Middleware\UnauthorizedHandler\RedirectHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CustomRedirectHandler extends RedirectHandler {
    public function handle( Exception $exception, ServerRequestInterface $request, array $options = [] ): ResponseInterface {
        /** @phpstan-ignore-next-line */
        $request->getFlash()->error(ACCESS_DENIED_MESSAGE);
        return parent::handle( $exception, $request, $options);
    }
}

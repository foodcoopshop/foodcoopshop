<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class CorsMiddleware implements MiddlewareInterface
{
    protected array $apiUrls = [];

    /**
     * Constructor.
     *
     * @param array<string, mixed> $options The options to use
     */
    public function __construct(array $options = [])
    {
        if (!empty($options['apiUrls'])) {
            $this->apiUrls = $options['apiUrls'];
        }
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $response = $handler->handle($request);

        $isApiRequest = in_array($request->getPath(), $this->apiUrls);
        if (!$isApiRequest) {
            return $response;
        }

        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
        $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        return $response;
    }
}
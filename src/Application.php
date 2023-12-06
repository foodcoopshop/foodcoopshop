<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/AGPL-3.0
 */

namespace App;

use Cake\Core\Configure;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Core\Exception\MissingPluginException;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Middleware\RequestAuthorizationMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\Policy\MapResolver;
use Authorization\AuthorizationService;
use Authentication\AuthenticationServiceProviderInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Cake\Http\ServerRequest;
use App\Policy\RequestPolicy;
use Authorization\Exception\MissingIdentityException;
use Authorization\Exception\ForbiddenException;
use Authentication\Identifier\Resolver\OrmResolver;
use Authentication\Identifier\IdentifierInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication
    implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{

    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        $this->addPlugin('Authentication');
        $this->addPlugin('Authorization');
        
        if (Configure::read('debug')) {
            $this->addPlugin('Bake');
            Configure::write('DebugKit.forceEnable', true);
            $this->addPlugin('DebugKit', ['bootstrap' => true]);
        }

        $this->addPlugin('Migrations');
        $this->addPlugin('AssetCompress', ['bootstrap' => true]);
        $this->addPlugin('Queue', ['bootstrap' => true]);

        $this->addPlugin('Admin', [
            'bootstrap' => false,
            'routes' => true,
            'autoload' => true
        ]);

        require_once $this->configDir . 'bootstrap_locale.php';

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) {
            $this->addPlugin('Network', [
                'routes' => true,
                'autoload' => true
            ]);
        }

    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {

        $csrf = new CsrfProtectionMiddleware();

        // Token check will be skipped when callback returns `true`.
        $csrf->skipCheckCallback(function ($request) {
            if (in_array($request->getPath(), ['/api/getProducts.json', '/api/updateProducts.json', '/api/getOrders.json'])) {
                return true;
            }
        });
        
        $middlewareQueue

        // Handle plugin/theme assets like CakePHP normally does.
        ->add(new AssetMiddleware([
            'cacheTime' => Configure::read('Asset.cacheTime'),
        ]))

        // Ensure routing middleware is added to the queue before CSRF protection middleware.
        ->add($csrf)

        // Add routing middleware.
        // If you have a large number of routes connected, turning on routes
        // caching in production could improve performance. For that when
        // creating the middleware instance specify the cache config name by
        // using it's second constructor argument:
        // `new RoutingMiddleware($this, '_cake_routes_')`
        ->add(new RoutingMiddleware($this))

        ->add(new AuthenticationMiddleware($this))

        ->add(
            new AuthorizationMiddleware($this, [
                'unauthorizedHandler' => [
                    'className' => 'CustomRedirect',
                    'url' => Configure::read('app.slugHelper')->getLogin(),
                    'exceptions' => [
                        MissingIdentityException::class,
                        ForbiddenException::class,
                    ],
                ],
            ])
        )

        ->add(new RequestAuthorizationMiddleware())

        // Catch any exceptions in the lower layers,
        // and make an error page/response
        ->add(new ErrorHandlerMiddleware(Configure::read('Error')))

        ;

        return $middlewareQueue;
    }

    protected function bootstrapCli(): void
    {
        try {
            $this->addPlugin('Bake');
        } catch (MissingPluginException $e) {
            // Do not halt if the plugin is missing
        }

        $this->addPlugin('Migrations');
        $this->addPlugin('Queue', ['bootstrap' => true]);

    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();

        $service->setConfig([
            'queryParam' => 'redirect',
        ]);

        $fields = [
            IdentifierInterface::CREDENTIAL_USERNAME => 'email',
            IdentifierInterface::CREDENTIAL_PASSWORD => 'passwd'
        ];
        
        // Load the authenticators
        $service->loadAuthenticator('Authentication.Session');
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => $fields,
        ]);

        $service->loadIdentifier('Authentication.Password', [
            'resolver' => [
                'className' => OrmResolver::class,
                'userModel' => 'Customers',
                'finder' => 'auth', // CustomersTable::findAuth
            ],
            'fields' => $fields,
        ]);

        return $service;
    }

    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $mapResolver = new MapResolver();
        $mapResolver->map(ServerRequest::class, RequestPolicy::class);
        return new AuthorizationService($mapResolver);
    }

}
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
use DateTime;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Utility\Security;
use App\Controller\Component\StringComponent;
use Authentication\Identifier\AbstractIdentifier;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication
    implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{

    public function checkMandatorySettings(): void
    {
        $securityErrors = 0;
        if (Configure::read('app.discourseSsoEnabled') && Configure::read('app.discourseSsoSecret') == '') {
            echo '<p>Please copy this <b>app.discourseSsoSecret</b> to your custom_config.php: '.StringComponent::createRandomString(20).'</p>';
            $securityErrors++;
        }
        if (Security::getSalt() == '') {
            echo '<p>Please copy this <b>Security => salt</b> to your custom_config.php: '.hash('sha256', Security::randomBytes(64)).'</p>';
            $securityErrors++;
        }
        if (Configure::read('App.fullBaseUrl') == '') {
            echo '<p>Please copy <b>' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '</b> to custom_config.php</p>';
            $securityErrors++;
        }
        if (Configure::read('Security.cookieKey') == '') {
            echo '<p>Please copy this <b>Security => cookieKey</b> to your custom_config.php: '.hash('sha256', Security::randomBytes(64)).'</p>';
            $securityErrors++;
        }
        if ($securityErrors > 0) {
            die('<p><b>Security errors: '.$securityErrors.'</b></p>');
        }
    }

    public function bootstrap(): void
    {
        parent::bootstrap();

        $this->checkMandatorySettings();

        $this->addPlugin('Authentication');
        $this->addPlugin('Authorization');

        if (Configure::read('debug')) {
            $this->addPlugin('Bake');
            Configure::write('DebugKit.forceEnable', true);
            $this->addPlugin('DebugKit', ['bootstrap' => true]);
        }

        $this->addPlugin('Migrations');
        $this->addPlugin('AssetCompress', ['bootstrap' => true]);
        $this->addPlugin('Queue', [
            'bootstrap' => true,
            'routes' => false,
        ]);

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

    private function getApiUrls(): array
    {
        return [
            '/api/getProducts.json',
            '/api/updateProducts.json',
            '/api/getOrders.json',
        ];
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {

        $csrf = new CsrfProtectionMiddleware();

        $isApiRequest = false;
        if (isset($_SERVER['REQUEST_URI'])) {
            $isApiRequest = in_array($_SERVER['REQUEST_URI'], $this->getApiUrls());
        }

        if ($isApiRequest) {

            // q&d solution to enable CORS for api requests
            @header('Access-Control-Allow-Origin: *');
            @header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
            @header('Access-Control-Allow-Headers: Content-Type, Authorization');
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                exit;
            }

        }

        // Token check will be skipped when callback returns `true`.
        $apiUrls = $this->getApiUrls();
        $csrf->skipCheckCallback(function ($request) use ($apiUrls) {
            return in_array($request->getUri()->getPath(), $apiUrls);
        });

        $authorizationMiddlewareConfig = [];
        if (!$isApiRequest) {
            $authorizationMiddlewareConfig = [
                'unauthorizedHandler' => [
                    'className' => 'CustomRedirect',
                    'url' => Configure::read('app.slugHelper')->getLogin(),
                    'exceptions' => [
                        MissingIdentityException::class,
                        ForbiddenException::class,
                    ],
                ],
            ];
        }

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

        ->add (new EncryptedCookieMiddleware(
            ['CookieAuth'],
            Configure::read('Security.cookieKey')
        ))

        ->add(new AuthenticationMiddleware($this))

        ->add(new AuthorizationMiddleware($this, $authorizationMiddlewareConfig))

        ->add(new RequestAuthorizationMiddleware())

        // Catch any exceptions in the lower layers,
        // and make an error page/response
        ->add(new ErrorHandlerMiddleware(Configure::read('Error')))

        ;

        return $middlewareQueue;
    }

    protected function bootstrapCli(): void
    {

        $this->checkMandatorySettings();

        try {
            $this->addPlugin('Bake');
        } catch (MissingPluginException $e) {
            // Do not halt if the plugin is missing
        }

        $this->addPlugin('Migrations');
        $this->addPlugin('Queue');

    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();

        $fields = [
            AbstractIdentifier::CREDENTIAL_USERNAME => 'email',
            AbstractIdentifier::CREDENTIAL_PASSWORD => 'passwd',
        ];

        $ormResolver = [
            'className' => OrmResolver::class,
            'userModel' => 'Customers',
            'finder' => 'auth', // CustomersTable::findAuth
        ];

        $service->setConfig([
            'queryParam' => 'redirect',
        ]);

        $service->loadIdentifier('Authentication.Password', [
            'resolver' => $ormResolver,
            'fields' => $fields,
        ]);

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $service->loadIdentifier('App.BarCode', [
                'resolver' => $ormResolver,
            ]);
        }

        $isApiRequest = in_array($request->getUri()->getPath(), $this->getApiUrls());
        if ($isApiRequest) {

            // enables basic authentication with php in cgi mode
            if (isset($_SERVER['HTTP_AUTHORIZATION']))
            {
                $ha = base64_decode( substr($_SERVER['HTTP_AUTHORIZATION'],6) );
                if (isset($ha[0]) && isset($ha[1])) {
                    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $ha);
                }
            }

            $service->loadAuthenticator('Authentication.HttpBasic', [
                'resolver' => $ormResolver,
                'fields' => $fields,
                'realm' => $request->getServerParams()['SERVER_NAME'] ?? 'FCS',
            ]);
            return $service;
        }

        $service->loadAuthenticator('Authentication.Session', [
            'fields' => [AbstractIdentifier::CREDENTIAL_USERNAME => 'email'],
            'identify' => true,
        ]);

        $service->loadAuthenticator('App.AppForm', [
            'loginUrl' => Configure::read('app.slugHelper')->getLogin(),
        ]);

        $service->loadAuthenticator('Authentication.Cookie', [
            'fields' => $fields,
            'loginUrl' => Configure::read('app.slugHelper')->getLogin(),
            'cookie' => [
                'expires' => new DateTime('+30 day'),
            ],
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
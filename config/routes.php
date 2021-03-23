<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 */
/** @var \Cake\Routing\RouteBuilder $routes */
$routes->setRouteClass(DashedRoute::class);

$routes->scope('/', function (RouteBuilder $builder) {

    $builder->setExtensions(['pdf', 'js']);

    $builder->connect('/', ['controller' => 'Pages', 'action' => 'home']);

    $builder->connect('/'.__('route_sign_in'), ['controller' => 'Customers', 'action' => 'login']);
    $builder->connect('/'.__('route_sign_out'), ['controller' => 'Customers', 'action' => 'logout']);
    $builder->connect('/'.__('route_registration'), ['controller' => 'Customers', 'action' => 'login']);
    $builder->connect('/'.__('route_registration_successful'), ['controller' => 'Customers', 'action' => 'registrationSuccessful']);
    $builder->connect('/'.__('route_information_about_right_of_withdrawal'), ['controller' => 'Carts', 'action' => 'generateRightOfWithdrawalInformationPdf']);
    if (Configure::read('app.termsOfUseEnabled')) {
        $builder->connect('/'.__('route_terms_of_use'), ['controller' => 'Pages', 'action' => 'termsOfUse']);
    }
    $builder->connect('/'.__('route_privacy_policy'), ['controller' => 'Pages', 'action' => 'privacyPolicy']);
    $builder->connect('/'.__('route_list_of_allergens'), ['controller' => 'Pages', 'action' => 'listOfAllergens']);
    $builder->connect('/'.__('route_accept_terms_of_use'), ['controller' => 'Customers', 'action' => 'acceptUpdatedTermsOfUse']);

    $builder->connect('/'.__('route_new_products'), ['controller' => 'Categories', 'action' => 'newProducts']);
    $builder->connect('/'.__('route_request_new_password'), ['controller' => 'Customers', 'action' => 'newPasswordRequest']);
    $builder->connect('/'.__('route_activate_new_password').'/*', ['controller' => 'Customers', 'action' => 'activateNewPassword']);

    if (Configure::read('app.isBlogFeatureEnabled')) {
        $builder->connect('/'.__('route_news_list'), ['controller' => 'BlogPosts', 'action' => 'index']);
        $builder->connect('/'.__('route_news_detail').'/*', ['controller' => 'BlogPosts', 'action' => 'detail']);
    }
    $builder->connect('/'.__('route_search').'/*', ['controller' => 'Categories', 'action' => 'search']);
    $builder->connect('/'.__('route_category').'/*', ['controller' => 'Categories', 'action' => 'detail']);
    $builder->connect('/'.__('route_product').'/*', ['controller' => 'Products', 'action' => 'detail']);
    $builder->connect('/'.__('route_manufacturer_list'), ['controller' => 'Manufacturers', 'action' => 'index']);
    $builder->connect('/'.__('route_manufacturer_detail').'/*', ['controller' => 'Manufacturers', 'action' => 'detail']);
    $builder->connect('/'.__('route_content').'/{idAndSlug}', ['controller' => 'Pages', 'action' => 'detail'])
        ->setPatterns(['idAndSlug' => '[a-zA-Z0-9-]+'])
        ->setPass(['idAndSlug']);
    $builder->connect('/'.__('route_cart').'/'.__('route_cart_show'), ['controller' => 'Carts', 'action' => 'detail']);
    $builder->connect('/'.__('route_cart').'/'.__('route_cart_finish'), ['controller' => 'Carts', 'action' => 'finish']);
    $builder->connect('/'.__('route_cart').'/'.__('route_cart_finished').'/*', ['controller' => 'Carts', 'action' => 'orderSuccessful']);
    $builder->connect('/'.__('route_cart').'/:action/*', ['controller' => 'Carts']);

    if (Configure::read('app.discourseSsoEnabled')) {
        $builder->connect('/discourse/sso', ['controller' => 'Pages', 'action' => 'discourseSso']);
    }

    if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
        $builder->connect('/'.__('route_self_service'), ['controller' => 'SelfService']);
    }

    $builder->connect('/js/localized-javascript', ['controller' => 'Localized', 'action' => 'renderAsJsFile'])->setExtensions(['js']);

    // first folder must not exist physically!
    $builder->connect('/photos/profile-images/customers/:imageSrc', ['controller' => 'Customers', 'action' => 'profileImage'])->setExtensions(['jpg']);

    /*
     * Connect catchall routes for all controllers.
     *
     * The `fallbacks` method is a shortcut for
     *
     * ```
     * $builder->connect('/:controller', ['action' => 'index']);
     * $builder->connect('/:controller/:action/*', []);
     * ```
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $builder->fallbacks();
});

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder) {
     *     // No $builder->applyMiddleware() here.
     *     // Connect API actions here.
     * });
     * ```
     */
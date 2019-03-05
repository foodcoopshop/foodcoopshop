<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
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
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

/**
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
 *
 */
Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {

    $routes->setExtensions(['pdf', 'js']);

    $routes->connect('/', ['controller' => 'Pages', 'action' => 'home']);

    $routes->connect('/'.__('route_sign_in'), ['controller' => 'Customers', 'action' => 'login']);
    $routes->connect('/'.__('route_sign_out'), ['controller' => 'Customers', 'action' => 'logout']);
    $routes->connect('/'.__('route_registration'), ['controller' => 'Customers', 'action' => 'login']);
    $routes->connect('/'.__('route_registration_successful'), ['controller' => 'Customers', 'action' => 'registrationSuccessful']);
    $routes->connect('/'.__('route_information_about_right_of_withdrawal'), ['controller' => 'Carts', 'action' => 'generateRightOfWithdrawalInformationPdf']);
    if (Configure::read('app.termsOfUseEnabled')) {
        $routes->connect('/'.__('route_terms_of_use'), ['controller' => 'Pages', 'action' => 'termsOfUse']);
    }
    $routes->connect('/'.__('route_privacy_policy'), ['controller' => 'Pages', 'action' => 'privacyPolicy']);
    $routes->connect('/'.__('route_list_of_allergens'), ['controller' => 'Pages', 'action' => 'listOfAllergens']);
    $routes->connect('/'.__('route_accept_terms_of_use'), ['controller' => 'Customers', 'action' => 'acceptUpdatedTermsOfUse']);

    $routes->connect('/'.__('route_new_products'), ['controller' => 'Categories', 'action' => 'newProducts']);
    $routes->connect('/'.__('route_request_new_password'), ['controller' => 'Customers', 'action' => 'newPasswordRequest']);
    $routes->connect('/'.__('route_activate_new_password').'/*', ['controller' => 'Customers', 'action' => 'activateNewPassword']);

    if (Configure::read('app.isBlogFeatureEnabled')) {
        $routes->connect('/'.__('route_news_list'), ['controller' => 'BlogPosts', 'action' => 'index']);
        $routes->connect('/'.__('route_news_detail').'/*', ['controller' => 'BlogPosts', 'action' => 'detail']);
    }
    $routes->connect('/'.__('route_search').'/*', ['controller' => 'Categories', 'action' => 'search']);
    $routes->connect('/'.__('route_category').'/*', ['controller' => 'Categories', 'action' => 'detail']);
    $routes->connect('/'.__('route_product').'/*', ['controller' => 'Products', 'action' => 'detail']);
    $routes->connect('/'.__('route_manufacturer_list'), ['controller' => 'Manufacturers', 'action' => 'index']);
    $routes->connect('/'.__('route_manufacturer_detail').'/:manufacturerSlug/'.__('route_news_list'), ['controller' => 'BlogPosts', 'action' => 'index']);
    $routes->connect('/'.__('route_manufacturer_detail').'/*', ['controller' => 'Manufacturers', 'action' => 'detail']);
    $routes->connect('/'.__('route_content').'/*', ['controller' => 'Pages', 'action' => 'detail']);
    $routes->connect('/'.__('route_cart').'/'.__('route_cart_show'), ['controller' => 'Carts', 'action' => 'detail']);
    $routes->connect('/'.__('route_cart').'/'.__('route_cart_finish'), ['controller' => 'Carts', 'action' => 'finish']);
    $routes->connect('/'.__('route_cart').'/'.__('route_cart_finished').'/*', ['controller' => 'Carts', 'action' => 'orderSuccessful']);
    $routes->connect('/'.__('route_cart').'/:action/*', ['controller' => 'Carts']);

    if (Configure::read('app.discourseSsoEnabled')) {
        $routes->connect('/discourse/sso', ['controller' => 'Pages', 'action' => 'discourseSso']);
    }

    $routes->connect('/js/localized-javascript', ['controller' => 'Localized', 'action' => 'renderAsJsFile'])->setExtensions(['js']);

    $routes->redirect('/admin/orders', '/admin/order-details?groupBy=customer');
    $routes->connect('/admin', array('plugin' => 'Admin', 'controller' => 'Pages', 'action' => 'home'));

    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks(DashedRoute::class);
});

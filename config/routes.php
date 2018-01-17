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

use Cake\Core\Plugin;
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
    
    $routes->connect('/', array('controller' => 'pages', 'action' => 'home'));
    
    $routes->connect('/anmelden', array('controller' => 'customers', 'action' => 'login'));
    $routes->connect('/registrierung', array('controller' => 'customers', 'action' => 'login'));
    $routes->connect('/registrierung/abgeschlossen', array('controller' => 'customers', 'action' => 'registrationSuccessful'));
    $routes->connect('/logout', array('controller' => 'customers', 'action' => 'logout'));
    $routes->connect('/Informationen-ueber-Ruecktrittsrecht', array('controller' => 'carts', 'action' => 'generateCancellationInformationPdf'));
    $routes->connect('/nutzungsbedingungen', array('controller' => 'pages', 'action' => 'termsOfUse'));
    $routes->connect('/datenschutzerklaerung', array('controller' => 'pages', 'action' => 'privacyPolicy'));
    $routes->connect('/nutzungsbedingungen-akzeptieren', array('controller' => 'customers', 'action' => 'acceptUpdatedTermsOfUse'));
    
    $routes->connect('/neue-produkte', array('controller' => 'categories', 'action' => 'newProducts'));
    $routes->connect('/neues-passwort-anfordern', array('controller' => 'customers', 'action' => 'newPasswordRequest'));
    $routes->connect('/neues-passwort-generieren/:changePasswordCode', array('controller' => 'customers', 'action' => 'generateNewPassword'));
    
    $routes->connect('/aktuelles', array('controller' => 'blog_posts', 'action' => 'index'));
    $routes->connect('/aktuelles/*', array('controller' => 'blog_posts', 'action' => 'detail'));
    $routes->connect('/suche/*', array('controller' => 'categories', 'action' => 'search'));
    $routes->connect('/kategorie/*', array('controller' => 'categories', 'action' => 'detail'));
    $routes->connect('/produkt/*', array('controller' => 'products', 'action' => 'detail'));
    $routes->connect('/hersteller', array('controller' => 'manufacturers', 'action' => 'index'));
    $routes->connect('/hersteller/:manufacturerSlug/aktuelles', array('controller' => 'blog_posts', 'action' => 'index'));
    $routes->connect('/hersteller/*', array('controller' => 'manufacturers', 'action' => 'detail'));
    $routes->connect('/content/*', array('controller' => 'pages', 'action' => 'detail'));
    $routes->connect('/warenkorb/anzeigen', array('controller' => 'carts', 'action' => 'detail'));
    $routes->connect('/warenkorb/abschliessen', array('controller' => 'carts', 'action' => 'finish'));
    $routes->connect('/warenkorb/abgeschlossen/*', array('controller' => 'carts', 'action' => 'orderSuccessful'));
    $routes->connect('/warenkorb/:action', array('controller' => 'carts'));
    
    // für normale cake routings (users controller)
    $routes->connect('/:controller/:action');
    
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
    
/**
 * Load all plugin routes. See the Plugin documentation on
 * how to customize the loading of plugin routes.
 */
Plugin::routes();

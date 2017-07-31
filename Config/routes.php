<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

    Router::parseExtensions('pdf');

/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
    // app home
    Router::connect('/', array('controller' => 'pages', 'action' => 'home'));

    Router::connect('/anmelden', array('controller' => 'customers', 'action' => 'login'));
    Router::connect('/registrierung', array('controller' => 'customers', 'action' => 'login'));
    Router::connect('/registrierung/abgeschlossen', array('controller' => 'customers', 'action' => 'registrationSuccessful'));
    Router::connect('/logout', array('controller' => 'customers', 'action' => 'logout'));
    Router::connect('/Informationen-ueber-Ruecktrittsrecht', array('controller' => 'carts', 'action' => 'generateCancellationInformationPdf'));
    Router::connect('/nutzungsbedingungen', array('controller' => 'pages', 'action' => 'termsOfUse'));
    Router::connect('/datenschutzerklaerung', array('controller' => 'pages', 'action' => 'privacyPolicy'));
    Router::connect('/nutzungsbedingungen-akzeptieren', array('controller' => 'customers', 'action' => 'acceptUpdatedTermsOfUse'));

    Router::connect('/neue-produkte', array('controller' => 'categories', 'action' => 'newProducts'));
    Router::connect('/neues-passwort-anfordern', array('controller' => 'customers', 'action' => 'newPasswordRequest'));

    Router::connect('/aktuelles', array('controller' => 'blog_posts', 'action' => 'index'));
    Router::connect('/aktuelles/*', array('controller' => 'blog_posts', 'action' => 'detail'));
    Router::connect('/suche/*', array('controller' => 'categories', 'action' => 'search'));
    Router::connect('/kategorie/*', array('controller' => 'categories', 'action' => 'detail'));
    Router::connect('/produkt/*', array('controller' => 'products', 'action' => 'detail'));
    Router::connect('/hersteller', array('controller' => 'manufacturers', 'action' => 'index'));
    Router::connect('/hersteller/:manufacturerSlug/aktuelles', array('controller' => 'blog_posts', 'action' => 'index'));
    Router::connect('/hersteller/*', array('controller' => 'manufacturers', 'action' => 'detail'));
    Router::connect('/content/*', array('controller' => 'pages', 'action' => 'detail'));
    Router::connect('/warenkorb/anzeigen', array('controller' => 'carts', 'action' => 'detail'));
    Router::connect('/warenkorb/abschliessen', array('controller' => 'carts', 'action' => 'finish'));
    Router::connect('/warenkorb/abgeschlossen/*', array('controller' => 'carts', 'action' => 'orderSuccessful'));
    Router::connect('/warenkorb/:action', array('controller' => 'carts'));

    // home for admin
    Router::connect('/admin', array('plugin' => 'admin', 'controller' => 'pages', 'action' => 'home'));

    // api
    Router::connect('/api/:action', array('controller' => 'api', 'ext' => 'json'));

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
    CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
    require CAKE . 'Config' . DS . 'routes.php';

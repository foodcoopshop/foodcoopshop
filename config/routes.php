<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes) {

    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $routes) {

        $routes->setExtensions(['pdf', 'js']);

        $routes->redirect('/app/*', '/404'); // redirect bots (eg. /app/modules/....) to 404, 401 would be returned due to existing AppController

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
        $routes->connect('/'.__('route_random_products'), ['controller' => 'Categories', 'action' => 'randomProducts']);
        $routes->connect('/'.__('route_request_new_password'), ['controller' => 'Customers', 'action' => 'newPasswordRequest']);
        $routes->connect('/'.__('route_activate_new_password').'/*', ['controller' => 'Customers', 'action' => 'activateNewPassword']);

        if (Configure::read('app.isBlogFeatureEnabled')) {
            $routes->redirect('/'.__('route_news_list_old'), ['controller' => 'BlogPosts', 'action' => 'index']);
            $routes->connect('/'.__('route_news_list'), ['controller' => 'BlogPosts', 'action' => 'index']);
            $routes->connect('/'.__('route_news_detail').'/{idAndSlug}', ['controller' => 'BlogPosts', 'action' => 'detail'])
                ->setPatterns(['idAndSlug' => '[a-zA-Z0-9-]+'])
                ->setPass(['idAndSlug']);
        }
        $routes->connect('/'.__('route_search').'/*', ['controller' => 'Categories', 'action' => 'search']);
        $routes->connect('/'.__('route_category').'/{idAndSlug}', ['controller' => 'Categories', 'action' => 'detail'])
            ->setPatterns(['idAndSlug' => '[a-zA-Z0-9-]+'])
            ->setPass(['idAndSlug']);
        $routes->connect('/'.__('route_product').'/{idAndSlug}', ['controller' => 'Products', 'action' => 'detail'])
            ->setPatterns(['idAndSlug' => '[a-zA-Z0-9-]+'])
            ->setPass(['idAndSlug']);
        $routes->connect('/'.__('route_manufacturer_list'), ['controller' => 'Manufacturers', 'action' => 'index']);
        $routes->connect('/'.__('route_manufacturer_detail').'/{idAndSlug}', ['controller' => 'Manufacturers', 'action' => 'detail'])
        ->setPatterns(['idAndSlug' => '[a-zA-Z0-9-]+'])
        ->setPass(['idAndSlug']);
        $routes->connect('/'.__('route_content').'/{idAndSlug}', ['controller' => 'Pages', 'action' => 'detail'])
            ->setPatterns(['idAndSlug' => '[a-zA-Z0-9-]+'])
            ->setPass(['idAndSlug']);
        $routes->connect('/'.__('route_cart').'/'.__('route_cart_show'), ['controller' => 'Carts', 'action' => 'detail']);
        $routes->connect('/'.__('route_cart').'/'.__('route_cart_finish'), ['controller' => 'Carts', 'action' => 'finish']);
        $routes->connect('/'.__('route_cart').'/'.__('route_cart_finished').'/*', ['controller' => 'Carts', 'action' => 'orderSuccessful']);
        $routes->connect('/'.__('route_cart').'/{action}/*', ['controller' => 'Carts']);

        if (Configure::read('app.discourseSsoEnabled')) {
            $routes->connect('/discourse/sso', ['controller' => 'Pages', 'action' => 'discourseSso']);
        }
        if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
            $routes->connect('/feedback', ['controller' => 'Feedbacks', 'action' => 'index']);
        }

        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
            $routes->connect('/'.__('route_self_service') . '/autoLoginAsSelfServiceCustomer/{id}', [
                'controller' => 'SelfService',
                'action' => 'autoLoginAsSelfServiceCustomer',
            ])->setPatterns(['id' => '[0-9]+']);
            $routes->connect('/'.__('route_self_service'), ['controller' => 'SelfService']);
        }

        $routes->connect('/js/localized-javascript', ['controller' => 'Localized', 'action' => 'renderAsJsFile'])->setExtensions(['js']);

        // first folder must not exist physically!
        $routes->connect('/photos/profile-images/customers/{imageSrc}', ['controller' => 'Customers', 'action' => 'profileImage']);

        $routes->fallbacks();
    });

};

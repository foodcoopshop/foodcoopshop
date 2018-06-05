<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;

class LocalizedController extends Controller
{
    
    private function getStrings()
    {
        $strings = [
            'helper' => [
                'logoutInfoText' => __('Really_sign_out?'),
                'logout' => __('Sign_out?'),
                'routeLogout' => __('route_sign_out'),
                'anErrorOccurred' => __('An_error_occurred')
            ],
            'cart' => [
                'routeCart' => __('route_cart'),
                'emptyCart' => __('Empty_cart'),
                'deposit' => __('deposit'),
                'reallyEmptyCart' => __('Really_empty_cart?'),
                'loadPastOrder' => __('Load_past_order'),
                'loadPastOrderDescriptionHtml' => __('Load_past_order_dialog_description_html'),
                'yes' => __('Yes'),
                'cancel' => __('Cancel'),
                'removeFromCart' => __('Remove_from_cart?'),
                'forEach' => __('for_each'),
                'approx' => __('approx.')
            ],
            'mobile' => [
                'home' => __('Home'),
                'routeManufacturerList' => __('route_manufacturer_list'),
                'manufacturers' => __('Manufacturers'),
                'routeNewsList' => __('route_news_list'),
                'news' => __('News'),
                'pages' => __('Pages'),
                'routeAllCategories' => Configure::read('app.slugHelper')->getAllProducts(),
                'shoppingLimitReached' => __('Shopping_limit_reached'),
                'show' => __('Show'),
                'showAllProducts' => __('Show_all_products')
            ]
        ];
        return $strings;
    }
    
    public function renderAsJsFile() {
        $this->viewBuilder()->setLayout('ajax');
        $this->set('localizedJs', $this->getStrings());
    }
    
}

?>
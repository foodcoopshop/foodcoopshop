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
            'datepicker' => [
                'close' => __('datepicker_close'),
                'prev' => __('datepicker_prev'),
                'next' => __('datepicker_next'),
                'today' => __('datepicker_today'),
                'weekHeader' => __('WeekHeader'),
            ],
            'helper' => [
                'defaultLocale' => Configure::read('App.defaultLocale'),
                'logoutInfoText' => __('Really_sign_out?'),
                'logout' => __('Sign_out?'),
                'routeLogout' => __('route_sign_out'),
                'anErrorOccurred' => __('An_error_occurred'),
                'no' => __('No'),
                'yes' => __('Yes'),
                'save' => __('Save'),
                'cancel' => __('Cancel'),
                'CancelShopOrder' => __('Cancel_shop_order?'),
                'ReallyCancelShopOrder' => __('Really_cancel_shop_order?'),
                'January' => __('January'),
                'February' => __('February'),
                'March' => __('March'),
                'April' => __('April'),
                'May' => __('May'),
                'June' => __('June'),
                'July' => __('July'),
                'August' => __('August'),
                'September' => __('September'),
                'October' => __('October'),
                'November' => __('November'),
                'December' => __('December'),
                'JanuaryShort' => __('JanuaryShort'),
                'FebruaryShort' => __('FebruaryShort'),
                'MarchShort' => __('MarchShort'),
                'AprilShort' => __('AprilShort'),
                'MayShort' => __('MayShort'),
                'JuneShort' => __('JuneShort'),
                'JulyShort' => __('JulyShort'),
                'AugustShort' => __('AugustShort'),
                'SeptemberShort' => __('SeptemberShort'),
                'OctoberShort' => __('OctoberShort'),
                'NovemberShort' => __('NovemberShort'),
                'DecemberShort' => __('DecemberShort'),
                'Monday' => __('Monday'),
                'Tuesday' => __('Tuesday'),
                'Wednesday' => __('Wednesday'),
                'Thursday' => __('Thursday'),
                'Friday' => __('Friday'),
                'Saturday' => __('Saturday'),
                'Sunday' => __('Sunday'),
                'MondayShort' => __('MondayShort'),
                'TuesdayShort' => __('TuesdayShort'),
                'WednesdayShort' => __('WednesdayShort'),
                'ThursdayShort' => __('ThursdayShort'),
                'FridayShort' => __('FridayShort'),
                'SaturdayShort' => __('SaturdayShort'),
                'SundayShort' => __('SundayShort'),
            ],
            'cart' => [
                'routeCart' => __('route_cart'),
                'emptyCart' => __('Empty_cart'),
                'deposit' => __('deposit'),
                'reallyEmptyCart' => __('Really_empty_cart?'),
                'loadPastOrder' => __('Load_past_order'),
                'loadPastOrderDescriptionHtml' => __('Load_past_order_dialog_description_html'),
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
            ],
            'timebasedCurrency' => [
                'routeCart' => __('route_cart')
            ],
            'admin' => [
                'routeCartFinished' => Configure::read('app.slugHelper')->getCartFinish(),
                'PlaceShopOrderFor' => __('Place_shop_order_for'),
                'ShopOrderDateIsSetBackAfterPlacingIt' => __('Shop_order_date_is_set_back_after_placing_it.'),
                'CloseAllOrders' => __('Close_all_orders?'),
                'ReallyCloseAllOrders' => __('Really_close_all_orders?'),
                'GenerateOrdersAsPdf' => __('Generate_orders_as_pdf?'),
                'ReallyGenerateOrdersAsPdf' => __('Really_generate_orders_as_pdf?'),
                'EmailAddresses' => __('Email_addresses'),
                'ChangeOrderStatus' => __('Change_order_status?'),
                'ReallyChangeOrderStatusFrom' => __('Really_change_order_status_from_%s?'),
                'orderStateCancelled' => __('order_state_cancelled'),
                'orderStateOpen' => __('order_state_open')
            ],
            'dialogOrder' => [
                'ChangeCommentOfOrder' => __('Change_comment_of_order'),
                'SetDateOfOrderBackTo' => __('Set_date_of_order_back_to'),
                'SetDateOfOrderBack' => __('Set_date_of_order_back')
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
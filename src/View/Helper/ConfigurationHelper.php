<?php

namespace App\View\Helper;

use App\Model\Table\ConfigurationsTable;
use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ConfigurationHelper extends Helper
{
    public function getConfigurationDropdownOptions($name)
    {
        switch ($name) {
            case 'FCS_SHOW_PRODUCTS_FOR_GUESTS':
            case 'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS':
            case 'FCS_DEFAULT_NEW_MEMBER_ACTIVE':
            case 'FCS_SHOW_FOODCOOPSHOP_BACKLINK':
            case 'FCS_ORDER_COMMENT_ENABLED':
            case 'FCS_TIMEBASED_CURRENCY_ENABLED':
            case 'FCS_FOODCOOPS_MAP_ENABLED':
            case 'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM':
            case 'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS':
            case 'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED':
            case 'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED':
            case 'FCS_SHOW_NEW_PRODUCTS_ON_HOME':
                return Configure::read('app.htmlHelper')->getYesNoArray();
                break;
            case 'FCS_LOCALE':
                return Configure::read('app.implementedLocales');
                break;
            case 'FCS_CUSTOMER_GROUP':
                return array_slice(Configure::read('app.htmlHelper')->getGroups(), 0, 2, true); // true: preserveKeys
                break;
            case 'FCS_NO_DELIVERY_DAYS_GLOBAL':
                return Configure::read('app.timeHelper')->getNextDeliveryDays();
                break;
            case 'FCS_CASHLESS_PAYMENT_ADD_TYPE':
                return $this->getCashlessPaymentAddTypeOptions();
                break;
        }
    }
    
    public function isCashlessPaymentTypeManual()
    {
        return Configure::read('appDb.FCS_CASHLESS_PAYMENT_ADD_TYPE') == ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_MANUAL;
    }
    
    public function getCashlessPaymentAddTypeOptions()
    {
        return [
            ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_MANUAL => __('Customer_adds_payment_manually'),
            ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD => __('Payment_is_added_by_uploading_a_list'),
        ];
    }

    public function getConfigurationDropdownOption($name, $value)
    {
        return self::getConfigurationDropdownOptions($name)[$value];
    }
    
    public function getConfigurationMultipleDropdownOptions($name, $value)
    {
        switch($name) {
            case 'FCS_NO_DELIVERY_DAYS_GLOBAL':
                $formattedAndCleanedDeliveryDays = Configure::read('app.htmlHelper')->getFormattedAndCleanedDeliveryDays($value);
                return join(', ', $formattedAndCleanedDeliveryDays);
                break;
        }
    }
}

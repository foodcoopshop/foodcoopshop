<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\View\Helper;
use App\Services\DeliveryRhythmService;
use Cake\ORM\TableRegistry;
use App\Model\Entity\Configuration;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ConfigurationHelper extends Helper
{
    public function getConfigurationDropdownOptions($name, $identity)
    {
        switch ($name) {
            case 'FCS_SHOW_PRODUCTS_FOR_GUESTS':
            case 'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS':
            case 'FCS_DEFAULT_NEW_MEMBER_ACTIVE':
            case 'FCS_SHOW_FOODCOOPSHOP_BACKLINK':
            case 'FCS_ORDER_COMMENT_ENABLED':
            case 'FCS_FOODCOOPS_MAP_ENABLED':
            case 'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM':
            case 'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS':
            case 'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED':
            case 'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED':
            case 'FCS_FEEDBACK_TO_PRODUCTS_ENABLED':
            case 'FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS':
            case 'FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY':
            case 'FCS_NEWSLETTER_ENABLED':
            case 'FCS_USER_FEEDBACK_ENABLED':
                return Configure::read('app.htmlHelper')->getYesNoArray();
                break;
            case 'FCS_LOCALE':
                return Configure::read('app.implementedLocales');
                break;
            case 'FCS_NO_DELIVERY_DAYS_GLOBAL':
                if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
                    $values = (new DeliveryRhythmService())->getNextDailyDeliveryDays(365);
                } else {
                    $values = (new DeliveryRhythmService())->getNextWeeklyDeliveryDays();
                }
                return $values;
                break;
            case 'FCS_CASHLESS_PAYMENT_ADD_TYPE':
                return $this->getCashlessPaymentAddTypeOptions();
                break;
            case 'FCS_MEMBER_FEE_PRODUCTS':
                $productsTable = TableRegistry::getTableLocator()->get('Products');
                return $productsTable->getForDropdown(0);
                break;
        }
    }

    public function isCashlessPaymentTypeManual()
    {
        return Configure::read('appDb.FCS_CASHLESS_PAYMENT_ADD_TYPE') == Configuration::CASHLESS_PAYMENT_ADD_TYPE_MANUAL;
    }

    public function getCashlessPaymentAddTypeOptions()
    {
        return [
            Configuration::CASHLESS_PAYMENT_ADD_TYPE_MANUAL => __('Customer_adds_payment_manually'),
            Configuration::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD => __('Payment_is_added_by_uploading_a_list'),
        ];
    }

    public function getConfigurationDropdownOption($name, $value, $identity)
    {
        return self::getConfigurationDropdownOptions($name, $identity)[$value];
    }

    public function getConfigurationMultipleDropdownOptions($name, $value)
    {
        switch($name) {
            case 'FCS_NO_DELIVERY_DAYS_GLOBAL':
                $formattedAndCleanedDeliveryDays = Configure::read('app.htmlHelper')->getFormattedAndCleanedDeliveryDays($value);
                return join(', ', $formattedAndCleanedDeliveryDays);
                break;
            case 'FCS_MEMBER_FEE_PRODUCTS':
                $value = explode(',', $value);
                $productsTable = TableRegistry::getTableLocator()->get('Products');
                $products = $productsTable->find('all',
                    conditions: [
                        'Products.id_product IN' => $value,
                    ]
                )->toArray();
                return join(', ', Hash::extract($products, '{n}.name'));
                break;
        }
    }
}

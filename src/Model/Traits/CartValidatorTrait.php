<?php
declare(strict_types=1);

namespace App\Model\Traits;

use App\Model\Entity\Customer;
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait CartValidatorTrait
{

    public function isAmountAvailableAttribute($isStockProduct, $stockManagementEnabled, $alwaysAvailable, $availableQuantity, $amount, $attributeName, $productName): bool|string
    {
        $result = true;
        if ((($isStockProduct && $stockManagementEnabled) || !$alwaysAvailable) && $availableQuantity < $amount) {
            $result = __('The_desired_amount_{0}_of_the_attribute_{1}_of_the_product_{2}_is_not_available_any_more_available_amount_{3}.', ['<b>' . $amount . '</b>', '<b>' . $attributeName . '</b>', '<b>' . $productName . '</b>', $availableQuantity]);
        }
        return $result;
    }

    public function isAmountAvailableProduct($isStockProduct, $stockManagementEnabled, $alwaysAvailable, $attributeId, $availableQuantity, $amount, $productName): bool|string
    {
        $result = true;
        if ((($isStockProduct && $stockManagementEnabled) || !$alwaysAvailable) && $attributeId == 0 && $availableQuantity < $amount) {
            $result = __('The_desired_amount_{0}_of_the_product_{1}_is_not_available_any_more_available_amount_{2}.', ['<b>' . $amount . '</b>', '<b>' . $productName . '</b>', $availableQuantity]);
        }
        return $result;
    }

    public function isProductActive($active, $productName): bool|string
    {
        $result = true;
        if (!$active) {
            $result = __('The_product_{0}_is_not_activated_any_more.', ['<b>' . $productName . '</b>']);
        }
        return $result;
    }

    public function isManufacturerActiveOrManufacturerHasDeliveryBreak($orderCustomerService, $productsTable, $active, $noDeliveryDays, $nextDeliveryDay, $isStockProduct, $stockManagementEnabled, $productName): bool|string
    {

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            return true;
        }

        $result = true;

        if (!$active || (!$orderCustomerService->isOrderForDifferentCustomerMode()
            && !$orderCustomerService->isSelfServiceModeByReferer()
            && $productsTable->deliveryBreakManufacturerEnabled($noDeliveryDays, $nextDeliveryDay, $stockManagementEnabled, $isStockProduct))) {
                $result = __('The_manufacturer_of_the_product_{0}_has_a_delivery_break_or_product_is_not_activated.', ['<b>' . $productName . '</b>']);
        }

        return $result;

    }

    public function isGlobalDeliveryBreakEnabled($orderCustomerService, $productsTable, $nextDeliveryDay, $productName): bool|string
    {

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            return true;
        }

        $result = true;

        if (!$orderCustomerService->isOrderForDifferentCustomerMode() && !$orderCustomerService->isSelfServiceModeByUrl() && !$orderCustomerService->isSelfServiceModeByReferer() &&
            $productsTable->deliveryBreakGlobalEnabled(Configure::read('appDb.FCS_NO_DELIVERY_DAYS_GLOBAL'), $nextDeliveryDay)) {
            $result = __('{0}_has_activated_the_delivery_break_and_product_{1}_cannot_be_ordered.',
                [
                    Configure::read('appDb.FCS_APP_NAME'),
                    '<b>' . $productName . '</b>'
                ]
            );
        }

        return $result;
    }

    public function isProductBulkOrderStillPossible($orderCustomerService, $isStockProduct, $stockManagementEnabled, $deliveryRhythmType, $deliveryRhythmPossibleUntil, $productName): bool|string
    {
        $result = true;
        if (!$orderCustomerService->isOrderForDifferentCustomerMode()) {
            if (!($isStockProduct && $stockManagementEnabled) && $deliveryRhythmType == 'individual') {
                if ($deliveryRhythmPossibleUntil->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')) < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    $result = __('It_is_not_possible_to_order_the_product_{0}_any_more.', ['<b>' . $productName . '</b>']);
                }
            }
        }
        return $result;
    }

    public function hasProductDeliveryRhythmTriggeredDeliveryBreak($orderCustomerService, $nextDeliveryDay, $productName): bool|string
    {
        $result = true;
        if (!$orderCustomerService->isOrderForDifferentCustomerMode() && !$orderCustomerService->isSelfServiceModeByUrl() && !$orderCustomerService->isSelfServiceModeByReferer() && $nextDeliveryDay == 'delivery-rhythm-triggered-delivery-break') {
            $result = __('{0}_can_be_ordered_next_week.',
                [
                    '<b>' . $productName . '</b>'
                ]
            );
        }
        return $result;

    }

    public function validateQuantityInUnitsForSelfServiceMode($orderCustomerService, $object, $unitObject, $orderedQuantityInUnits): bool|string
    {
        $result = true;
        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($orderCustomerService->isSelfServiceModeByReferer() || $orderCustomerService->isSelfServiceModeByUrl())) {
            if ($object->{$unitObject} && $object->{$unitObject}->price_per_unit_enabled && ($orderedQuantityInUnits <0) /* !sic < 0 see getStringAsFloat */) {
                $result = __('Please_provide_a_valid_ordered_quantity_in_units_and_click_on_the_add_button.');
            }
        }
        return $result;
    }

    public function validateMinimalCreditBalance($grossPrice, $orderCustomerService): bool|string
    {

        $identity = Router::getRequest()->getAttribute('identity');

        // implementation for purchase price check is too much work, so simply do not validate at all (enough for now)
        if ($identity->shopping_price != Customer::SELLING_PRICE) {
            return true;
        }

        $result = true;
        if (Configure::read('app.htmlHelper')->paymentIsCashless() && !$orderCustomerService->isOrderForDifferentCustomerMode()) {
            if (!$identity->hasEnoughCreditForProduct($grossPrice)) {
                $result = __('The_product_worth_{0}_cannot_be_added_to_your_cart_please_add_credit_({1})_(minimal_credit_is_{2}).', [
                    '<b>'.Configure::read('app.numberHelper')->formatAsCurrency($grossPrice).'</b>',
                    '<b>'.Configure::read('app.numberHelper')->formatAsCurrency($identity->getCreditBalanceMinusCurrentCartSum()).'</b>',
                    '<b>'.Configure::read('app.numberHelper')->formatAsCurrency(Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE')).'</b>',
                ]);
            }
        }

        return $result;

    }

}
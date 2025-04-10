<?php
declare(strict_types=1);

namespace App\Model\Traits;

use App\Model\Entity\Customer;
use App\Services\OrderCustomerService;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\I18n\Date;
use App\Model\Entity\Product;
use App\Model\Entity\ProductAttribute;

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

    public function isAmountAvailableAttribute(bool $isStockProduct, bool $stockManagementEnabled, bool $alwaysAvailable, string|float $availableQuantity, string|float $amount, string $attributeName, string $productName, string $unitName = ''): bool|string
    {
        $result = true;
        $unitNameString = ($unitName != '') ? ' ' . $unitName : '';
        if ((($isStockProduct && $stockManagementEnabled) || !$alwaysAvailable) && $availableQuantity < $amount) {
            $result = __('The_desired_amount_{0}_of_the_attribute_{1}_of_the_product_{2}_is_not_available_any_more_available_amount_{3}.', [
                '<b>' . Configure::read('app.numberHelper')->formatUnitAsDecimal($amount) . $unitNameString . '</b>',
                '<b>' . $attributeName . '</b>',
                '<b>' . $productName . '</b>',
                Configure::read('app.numberHelper')->formatUnitAsDecimal($availableQuantity) . $unitNameString,
            ]);
        }
        return $result;
    }

    public function isAmountAvailableProduct(bool $isStockProduct, bool $stockManagementEnabled, bool $alwaysAvailable, int $attributeId, string|float $availableQuantity, string|float $amount, string $productName, string $unitName = ''): bool|string
    {
        $result = true;
        $unitNameString = ($unitName != '') ? ' ' . $unitName : '';
        if ((($isStockProduct && $stockManagementEnabled) || !$alwaysAvailable) && $attributeId == 0 && $availableQuantity < $amount) {
            $result = __('The_desired_amount_{0}_of_the_product_{1}_is_not_available_any_more_available_amount_{2}.', [
                '<b>' . Configure::read('app.numberHelper')->formatUnitAsDecimal($amount) . $unitNameString . '</b>',
                '<b>' . $productName . '</b>',
                Configure::read('app.numberHelper')->formatUnitAsDecimal($availableQuantity) . $unitNameString,
            ]);
        }
        return $result;
    }

    public function isProductActive(int $active, string $productName): bool|string
    {
        $result = true;
        if ($active != APP_ON) {
            $result = __('The_product_{0}_is_not_activated_any_more.', ['<b>' . $productName . '</b>']);
        }
        return $result;
    }

    public function isManufacturerActiveOrManufacturerHasDeliveryBreak(bool $active, string|null $noDeliveryDays, string $nextDeliveryDay, bool $isStockProduct, bool $stockManagementEnabled, string $productName): bool|string
    {

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $hasEnabledDeliveryBreak = false;
        } else {
            $productsTable = TableRegistry::getTableLocator()->get('Products');
            $hasEnabledDeliveryBreak = !OrderCustomerService::isOrderForDifferentCustomerMode()
            && !OrderCustomerService::isSelfServiceModeByReferer()
            && $productsTable->deliveryBreakManufacturerEnabled($noDeliveryDays, $nextDeliveryDay, $stockManagementEnabled, $isStockProduct);
        }

        $result = true;
        if (!$active == APP_ON || $hasEnabledDeliveryBreak) {
            $result = __('The_manufacturer_of_the_product_{0}_has_a_delivery_break_or_product_is_not_activated.', ['<b>' . $productName . '</b>']);
        }

        return $result;

    }

    public function isGlobalDeliveryBreakEnabled(string $nextDeliveryDay, string $productName): bool|string
    {

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            return true;
        }

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $result = true;

        if (!OrderCustomerService::isOrderForDifferentCustomerMode() && !OrderCustomerService::isSelfServiceModeByUrl() && !OrderCustomerService::isSelfServiceModeByReferer() &&
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

    public function isProductBulkOrderStillPossible(bool $isStockProduct, bool $stockManagementEnabled, string $deliveryRhythmType, Date|null $deliveryRhythmPossibleUntil, string $productName): bool|string
    {
        $result = true;
        if (!OrderCustomerService::isOrderForDifferentCustomerMode()) {
            if (!($isStockProduct && $stockManagementEnabled) && $deliveryRhythmType == 'individual') {
                if ($deliveryRhythmPossibleUntil->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')) < Configure::read('app.timeHelper')->getCurrentDateForDatabase()) {
                    $result = __('It_is_not_possible_to_order_the_product_{0}_any_more.', ['<b>' . $productName . '</b>']);
                }
            }
        }
        return $result;
    }

    public function hasProductDeliveryRhythmTriggeredDeliveryBreak(string $nextDeliveryDay, string $productName): bool|string
    {
        $result = true;
        if (!OrderCustomerService::isOrderForDifferentCustomerMode() && !OrderCustomerService::isSelfServiceModeByUrl() && !OrderCustomerService::isSelfServiceModeByReferer() && $nextDeliveryDay == 'delivery-rhythm-triggered-delivery-break') {
            $result = __('{0}_can_be_ordered_next_week.',
                [
                    '<b>' . $productName . '</b>'
                ]
            );
        }
        return $result;

    }

    public function validateQuantityInUnitsForSelfServiceMode(Product|ProductAttribute $object, string $unitObject, float $orderedQuantityInUnits): bool|string
    {
        $result = true;
        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && (OrderCustomerService::isSelfServiceModeByReferer() || OrderCustomerService::isSelfServiceModeByUrl())) {
            if ($object->{$unitObject} && $object->{$unitObject}->price_per_unit_enabled && $orderedQuantityInUnits < 0 /* !sic < 0 see getStringAsFloat */) {
                $result = __('Please_provide_a_valid_ordered_quantity_in_units_and_click_on_the_add_button.');
            }
        }
        return $result;
    }

    public function validateMinimalCreditBalance(float $grossPrice): bool|string
    {

        $identity = Router::getRequest()->getAttribute('identity');

        // implementation for purchase price check is too much work, so simply do not validate at all (enough for now)
        if ($identity->shopping_price != Customer::SELLING_PRICE) {
            return true;
        }

        $result = true;
        if (Configure::read('app.htmlHelper')->paymentIsCashless() && !OrderCustomerService::isOrderForDifferentCustomerMode()) {
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
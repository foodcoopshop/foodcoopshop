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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
                'dateFormat' => Configure::read('DateFormat.DateForDatepicker')
            ],
            'helper' => [
                'defaultLocale' => Configure::read('appDb.FCS_DEFAULT_LOCALE'),
                'defaultLocaleInBCP47' => str_replace('_', '-', Configure::read('appDb.FCS_DEFAULT_LOCALE')),
                'logoutInfoText' => __('Really_sign_out?'),
                'logout' => __('Sign_out?'),
                'routeLogout' => __('route_sign_out'),
                'anErrorOccurred' => __('An_error_occurred'),
                'no' => __('No'),
                'yes' => __('Yes'),
                'save' => __('Save'),
                'cancel' => __('Cancel'),
                'CancelInstantOrder' => __('Cancel_instant_order?'),
                'ReallyCancelInstantOrder' => __('Really_cancel_instant_order?'),
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
                'CurrencySymbol' => Configure::read('appDb.FCS_CURRENCY_SYMBOL'),
                'CurrencyName' => Configure::read('app.currencyName'),
                'ShowMore' => __('Show_more'),
                'ShowLess' => __('Show_less')
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
                'approx' => __('approx.'),
                'PickupDay' => __('Pickup_day')
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
                'routeCartFinished' => '/'.__('route_cart') . '/' . __('route_cart_finished'), //! careful, without $cartId argument,
                'PlaceInstantOrderFor' => __('Place_instant_order_for'),
                'CloseAllOrders' => __('Close_all_orders?'),
                'ReallyCloseAllOrders' => __('Really_close_all_orders?'),
                'GenerateOrdersAsPdf' => __('Generate_orders_as_pdf?'),
                'ReallyGenerateOrdersAsPdf' => __('Really_generate_orders_as_pdf?'),
                'EmailAddresses' => __('Email_addresses'),
                'DecreaseAmount' => __('Decrease_amount'),
                'Stock' => __('Stock'),
                'DecreaseAmountExplainationText' => __('The_amount_can_only_be_decreased_to_increase_plaese_order_product_again.'),
                'NewAmount' => __('New_amount'),
                'WhyIsAmountDecreased' => __('Why_is_amount_decreased_(mandatory_field)?'),
                'AdaptPrice' => __('Adapt_price?'),
                'orderedBy' => __('ordered_by'),
                'ExplainationTextApdaptPriceFormApaptWeight' => __('Explaination_text_apdapt_price_form_apapt_weight.'),
                'WhyIsPriceAdapted' => __('Why_is_price_adapted_(mandatory_field)?'),
                'OriginalPriceWithoutReductionOfPriceInTime' => __('Original_price_without_reduction_of_price_in_time'),
                'FromWhichReallyPaidIn' => __('From_which_really_paid_in'),
                'AdaptWeight' => __('Adapt_weight?'),
                'DeliveredWeight' => __('Delivered_weight'),
                'DeliveredTotalWeight' => __('Delivered_total_weight'),
                'BasePrice' => __('Base_price'),
                'PriceIsAutomaticallyAdaptedAfterSave' => __('Price_is_automatically_adapted_after_save.'),
                'FieldIsRedIfWeightNotYetAdapted' => __('The_field_is_red_if_weight_not_yet_adapted.'),
                'DoNotAutomaticallyAdaptPriceJustChangeWeight' => __('Do_not_automatically_apapt_price_just_change_weight.'),
                'ReallyCancelOrderedProduct' => __('Really_cancel_ordered_product?'),
                'DoYouReallyWantToCancelProduct0' => __('Do_you_really_want_to_cancel_product_{0}?'),
                'DoYouReallyWantToCancelProduct0From1' => __('Do_you_really_want_to_cancel_product_{0}_from_{1}?'),
                'WhyIsProductCancelled' => __('Why_is_product_cancelled_(mandatory_field)?'),
                'YesDoCancelButton' => __('Yes_do_cancel_button!'),
                'DoNotCancelButton' => __('Do_not_cancel_button'),
                'PleaseOnlyCancelIfOkForManufacturer' => __('Please_only_cancel_if_ok_for_manufacturer!'),
                'ReallyCancelSelectedProducts' => __('Really_cancel_selected_products?'),
                'YouSelectedOneProductForCancellation' => __('You_selected_1_product_for_cancellation'),
                'YouSelected0ProductsForCancellation' => __('You_selected_{0}_products_for_cancellation'),
                'AdaptAmountReasonIsMandatory' => __('Adapt_amount_reason_is_mandatory.'),
                'DeliveredWeightNeedsToBeGreaterThan0' => __('Delivered_weight_needs_to_be_greater_than_0.'),
                'AdaptPriceReasonIsMandatory' => __('Adapt_price_reason_is_mandatory.'),
                'CancellationReasonIsMandatory' => __('Cancellation_reason_is_mandatory.'),
                'PleaseEnterANumber' => __('Please_enter_a_number.'),
                'AddNewAttributeForProduct' => __('Add_new_attribute_for_product'),
                'PleaseChoseTheNewAttributeForProduct0' => __('Please_chose_the_new_attribute_for_product_{0}.'),
                'ThisFunctionCanOnlyBeUsedIfAttributesExist' => __('This_function_can_only_be_used_if_attributes_exist.'),
                'ChangingDefaultAttributeInfoText0Html' => __('Changing_default_attribute_info_text_{0}_html'),
                'ChangeDefaultAttribute' => __('Change_default_attribute'),
                'ChangeCategories' => __('Change_categories'),
                'Weight' => __('Weight'),
                'EnterApproximateWeightInPriceDialog' => __('Enter_approximate_weight_in_price_dialog.'),
                'ChangeTaxRate' => __('Change_tax_rate'),
                'ShowProductAsNew' => __('Show_product_as_new?'),
                'DoNotShowProductAsNew' => __('Do_not_show_product_as_new?'),
                'ReallyShowProduct0AsNew' => __('Really_show_product_{0}_as_new?'),
                'ReallyDoNotShowProduct0AsNew' => __('Really_do_not_show_product_{0}_as_new?'),
                'ActivateProduct' => __('Activate_product'),
                'DeactivateProduct' => __('Deactivate_product'),
                'ActivateMember' => __('Activate_member?'),
                'DeactivateMember' => __('Deactivate_member?'),
                'ReallyActivateMember0' => __('Really_activate_member_{0}_?'),
                'ReallyDeactivateMember0' => __('Really_deactivate_member_{0}?'),
                'YesInfoMailWillBeSent' => __('Yes_info_mail_will_be_sent'),
                'ReallyActivateProduct0' => __('Really_activate_product_{0}_?'),
                'ReallyDeactivateProduct0' => __('Really_deactivate_product_{0}?'),
                'DeleteAttribute' => __('Delete_attribute'),
                'ReallyDeleteAttribute0' => __('Really_delete_attribute_{0}?'),
                'PleaseChoseIfPaybackOrCreditUpload' => __('Please_chose_if_it_is_a_payback_or_a_credit_upload.'),
                'PleaseChoseTypeOfPayment' => __('Please_chose_the_type_of_your_payment.'),
                'DeletePayment' => __('Delete_payment'),
                'ReallyDeletePayment' => __('Really_delete_payment?'),
                'Date' => __('Date'),
                'Amount' => __('Amount'),
                'PleaseChoseAtLeastOneMonth' => __('Please_chose_at_least_one_month.'),
                'DeleteMember' => __('Delete_member?'),
                'ReallyDeleteMember' => __('Really_delete_member?'),
                'BeCarefulNoWayBack' => __('Be_careful_there_is_no_way_back!'),
                'ErrorsOccurredWhileMemberWasDeleted' => __('Errors_occurred_while_member_was_deleted'),
                'AddComment' => __('Add_comment'),
                'PleaseCancelAllOrderedProductsBeforeCancellingTheOrder' => __('Please_cancel_all_ordered_products_before_cancelling_the_order.'),
                'AddNewProduct' => __('Add_new_product?'),
                'ReallyAddNewProduct' => __('Really_add_new_product?'),
                'ThisFunctionIsNotAvailableToday' => __('This_function_is_not_available_today.'),
                'ManuallySendOrderList' => __('Manually_send_order_list?'),
                'ReallyManuallySendOrderList' => __('Really_manually_send_order_list_to_{0}?'),
                'OrderPeriod' => __('Order_period'),
                'AnExistingOrderListWillBeOverwritten' => __('An_existing_order_list_will_be_overwritten!'),
                'ChangeGroupFor' => __('Change_group_for'),
                'TheMemberNeedsToSignInAgain' => __('The_member_needs_to_sign_again.'),
                'Member' => __('Member'),
                'WhyIsPickupDayChanged' => __('Why_is_pickup_day_changed?'),
                'ChangePickupDay' => __('Change_pickup_day'),
                'NewPickupDay' => __('New_pickup_day'),
                'ChangePickupDayInvoicesInfoText' => __('Change_pickup_day_invoices_info_text'),
                'products' => __('products'),
                'product' => __('product'),
                'EmailAddressesSuccessfullyCopiedToClipboard' => __('{0}_email_addresses_have_been_copied_successfully_to_your_clipboard.'),
                'OneEmailAddressSuccessfullyCopiedToClipboard' => __('1_email_address_has_been_copied_successfully_to_your_clipboard.'),
            ],
            'pickupDay' => [
                'WereTheProductsPickedUp' => __('Were_the_products_picked_up?'),
                'ThisInformationServesThePickupTeamToSeeWhoWasAlreadyHere' => __('This_information_serves_the_pickup_team_to_see_who_was_already_here.'),
                'AllProductsPickedUp' => __('All_products_picked_up?'),
                'WereTheProductsOfAllMembersPickedUp' => __('Were_the_products_of_all_members_picked_up?'),
                'ChangePickupDayComment' => __('Change_pickup_day_comment')
            ],
            'dialogCustomer' => [
                'ChangeMemberComment' => __('Change_member_comment'),
                'ChangeGroup' => __('Change_group')
            ],
            'dialogProduct' => [
                'ChangeAmount' => __('Change_amount'),
                'ChangePrice' => __('Change_price'),
                'PricePerUnit' => __('Price_per_unit'),
                'inclVAT' => __('incl_vat'),
                'PricePerWeightForAdaptionAfterDelivery' => __('Price_per_weight_(for_adaption_after_delivery)'),
                'for' => __('for'),
                'approximateDeliveryWeightIn0PerUnit' => __('approximate_delivery_weight_in_{0}_per_unit'),
                'Name' => __('Name'),
                'ProductRenameInfoText' => __('Product_rename_info_text'),
                'Unit' => __('Unit'),
                'UnitDescriptionExample' => __('Unit_description_example'),
                'DescriptionShort' => __('Description_short'),
                'DescriptionLong' => __('Description_long'),
                'ProductDeclarationOK' => __('Product_declaration_ok?'),
                'DocsUrlProductDeclaration' => Configure::read('app.htmlHelper')->getDocsUrl(__('docs_route_product_declaration')),
                'Help' => __('Help'),
                'ChangeNameAndDescription' => __('Change_name_and_description'),
                'Deposit' => __('Deposit'),
                'EnterZeroForDelete' => __('Enter_zero_for_delete'),
                'IsProductAStockProduct' => __('Is_the_product_a_stock_product?'),
                'StockProduct' => __('Stock_product'),
                'CurrentStock' => __('Current_stock'),
                'AvailableAmount' => __('Available_amount'),
                'OrdersPossibleUntilAmountOf' => __('Orders_possible_until_amount_of'),
                'zeroOrSmallerZero' => __('zero_or_smaller_zero'),
                'ForManufacturersAndContactPersonsCanBeChangedInManufacturerSettings' => __('For_manufacturers_and_contact_persons._Can_be_changed_in_manufacturer_settings.'),
                'NotificationIfAmountLowerThan' => __('Notification_if_amount_lower_than'),
                'IsProductStockProduct' => __('Is_product_a_stock_product?'),
                'TheDeliveryRhythmOfStockProductsIsAlwaysWeekly' => __('The_delivery_rhythm_of_stock_products_is_always_weekly.'),
                'DeliveryRhythm' => __('Delivery_rhythm'),
                'FirstDeliveryDay' => __('First_delivery_day'),
                'DeliveryDay' => __('Delivery_day'),
                'FirstDeliveryDayInfo' => __('First_delivery_day_info.'),
                'canBeLeftBlank' => __('can_be_left_blank'),
                'OrderPossibleUntil' => __('Order_possible_until'),
                'InfoPageForDeliveryRhythm' => __('Info_page_for_delivery_rhythm'),
                'DocsUrlOrderHandling' => Configure::read('app.htmlHelper')->getDocsUrl(__('docs_route_order_handling')),
            ],
            'upload' => [
                'delete' => __('delete'),
                'DeleteImage' => __('Delete_image?'),
                'ReallyDeleteImage' => __('Really_delete_image?'),
                'rotateAntiClockwise' => __('rotate_anti_clockwise?'),
                'rotateClockwise' => __('rotate_clockwise?'),
                'ChangeGeneralTermsAndConditions' => __('Change_general_terms_and_conditions')
            ],
            
            'syncProducts' => [
                'Id' => __('Id'),
                'Product' => __('Product'),
                'AnAttributeCannotBeAssignedToAProduct' => __('An_attribute_cannot_be_assigned_to_a_product.'),
                'AProductCannotBeAssignedToAnAttribute' => __('A_product_cannot_be_assigned_to_an_attribute.'),
            ],
            
            'syncProductData' => [
                'Image' => __('Image'),
                'Name' => __('Name'),
                'NameAdditionalInfo' => __('Name_additional_info'),
                'StockProduct' => __('Stock_product'),
                'Quantity' => __('Quantity'),
                'Price' => __('Price'),
                'Deposit' => __('Deposit'),
                'DeliveryRhythm' => __('Delivery_rhythm'),
                'Status' => __('Status'),
                'NoProductsOrAttributesSelected' => __('No_products_or_attributes_selected.'),
                'NoProductDataSelected' => __('No_product_data_selected.'),
                'PleaseEnterYourCredentials' => __('Please_enter_your_credentials.'),
                'ReallySynchronize' => __('Really_synchronize?'),
                'ThisActionCannotBeUndone' => __('This_action_cannot_be_undone.'),
                'SynchronizeDialogInfoText' => __('Really_synchronize_data_{0}_from_{1}_and_{2}_to_the_following_foodcoops?_{3}'),
                'product' => __('product'),
                'products' => __('products'),
                'attribute' => __('attribute'),
                'attributes' => __('attributes')
            ]

        ];
        return $strings;
    }

    public function renderAsJsFile() {
        $this->response = $this->response->withType('application/javascript');
        $this->viewBuilder()->setLayout('ajax');
        $this->set('localizedJs', $this->getStrings());
    }

}

?>

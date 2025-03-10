<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Controller;

use App\Services\OutputFilter\OutputFilterService;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

class LocalizedController extends Controller
{

    private function getStrings(): array
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
                'defaultLocaleShort' => substr(Configure::read('appDb.FCS_DEFAULT_LOCALE'), 0, 2),
                'defaultLocaleInBCP47' => str_replace('_', '-', Configure::read('appDb.FCS_DEFAULT_LOCALE')),
                'logoutInfoText' => __('Really_sign_out?'),
                'logout' => __('Sign_out?'),
                'routeLogout' => __('route_sign_out'),
                'routeSelfService' => __('route_self_service'),
                'anErrorOccurred' => __('An_error_occurred'),
                'no' => __('No'),
                'yes' => __('Yes'),
                'save' => __('Save'),
                'cancel' => __('Cancel'),
                'CancelOrder' => __('Cancel_order?'),
                'ReallyCancelOrder' => __('Really_cancel_order?'),
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
                'ShowLess' => __('Show_less'),
                'Close' => __('Close'),
                'YouHaveAlreadyOrdered01TimesFor2' => __('You_have_already_ordered_{0}_{1}_times_for_{2}.'),
                'Firstname' => __('Firstname'),
                'Lastname' => __('Lastname'),
                'ContactPerson' => __('Contact_person'),
                'CompanyName' => __('Company_name'),
                'PleaseEnterTheContactPerson' => __('Please_enter_the_contact_person'),
                'PleaseEnterYourLastname' => __('Please_enter_your_lastname'),
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
                'PickupDay' => __('Pickup_day'),
                'selfServiceConfirmPurchaseDialog' => __('Confirm_self_service_purchase_dialog'),
                'selfServiceConfirmPurchase' => __('Confirm_self_service_purchase'),
                'selfServiceConfirmPurchaseButton' => __('Confirm_self_service_purchase_button'),
                'selfServiceDenyPurchaseButton' => __('Deny_self_service_purchase_button'),
                'selfServiceAmountToBePaid' => __('Amount_to_be_paid'),
            ],
            'mobile' => [
                'home' => __('Home'),
                'routeManufacturerList' => __('route_manufacturer_list'),
                'manufacturers' => __('Manufacturers'),
                'pages' => __('Pages'),
                'routeAllCategories' => Configure::read('app.slugHelper')->getAllProducts(),
                'show' => __('Show'),
                'showAllProducts' => __('Show_all_products')
            ],
            'admin' => [
                'routeCartFinished' => '/'.__('route_cart') . '/' . __('route_cart_finished'), //! careful, without $cartId argument,
                'PlaceOrderFor' => __('Place_order_for'),
                'CloseAllOrders' => __('Close_all_orders?'),
                'ReallyCloseAllOrders' => __('Really_close_all_orders?'),
                'EmailAddresses' => __('Email_addresses'),
                'DecreaseAmount' => __('Decrease_amount'),
                'Stock' => __('Stock'),
                'NewAmount' => __('New_amount'),
                'WhyIsAmountDecreased' => __('Why_is_amount_decreased_(mandatory_field)?'),
                'AdaptPrice' => __('Adapt_price?'),
                'orderedBy' => __('ordered_by'),
                'ExplainationTextApdaptPriceFormApaptWeight' => __('Explaination_text_apdapt_price_form_apapt_weight.'),
                'WhyIsPriceAdapted' => __('Why_is_price_adapted_(optional_field)?'),
                'OriginalPriceWithoutReductionOfPriceInTime' => __('Original_price_without_reduction_of_price_in_time'),
                'FromWhichReallyPaidIn' => __('From_which_really_paid_in'),
                'AdaptWeight' => __('Adapt_weight?'),
                'DeliveredWeight' => __('Delivered_weight'),
                'DeliveredTotalWeight' => __('Delivered_total_weight'),
                'BasePrice' => __('Base_price'),
                'PriceIsAutomaticallyAdaptedAfterSave' => __('Price_is_automatically_adapted_after_save.'),
                'DoNotAutomaticallyAdaptPriceJustChangeWeight' => __('Do_not_automatically_apapt_price_just_change_weight.'),
                'Calculator' => __('Calculator'),
                'ExampleGivenAbbr' => __('Example_given_abbr'),
                'ReallyDeleteOrderedProduct' => __('Really_delete_ordered_product?'),
                'ProductCancellation' => __('Product_cancellation'),
                'DoYouReallyWantToCancelProduct0' => __('Do_you_really_want_to_cancel_product_{0}?'),
                'DoYouReallyWantToCancelProduct0From1' => __('Do_you_really_want_to_cancel_product_{0}_from_{1}?'),
                'WhyIsProductCancelled' => __('Why_is_product_cancelled_(mandatory_field)?'),
                'WhyAreProductsCancelled' => __('Why_are_products_cancelled_(mandatory_field)?'),
                'YesDoCancelButton' => __('Yes_do_cancel_button!'),
                'PleaseOnlyCancelIfOkForManufacturer' => __('Please_only_cancel_if_ok_for_manufacturer!'),
                'YouSelectedOneProduct' => __('You_selected_1_product.'),
                'YouSelected0Products' => __('You_selected_{0}_products.'),
                'AdaptAmountReasonIsMandatory' => __('Adapt_amount_reason_is_mandatory.'),
                'AdaptPriceReasonIsMandatory' => __('Adapt_price_reason_is_mandatory.'),
                'CancellationReasonIsMandatory' => __('Cancellation_reason_is_mandatory.'),
                'PleaseEnterANumber' => __('Please_enter_a_number.'),
                'AddNewAttributeForProduct' => __('Add_new_attribute_for_product'),
                'PleaseChoseTheNewAttributeForProduct0' => __('Please_chose_the_new_attribute_for_product_{0}.'),
                'AttentionAttributesAreShownInSameOrderAsAddedAndThisCannotBeChangedAfterwards' => __('Attention_attributes_are_shown_in_same_order_as_added_and_this_cannot_be_changed_afterwards.'),
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
                'Activate' => __('Activate'),
                'Deactivate' => __('Deactivate'),
                'ActivateProduct' => __('Activate_product'),
                'DeactivateProduct' => __('Deactivate_product'),
                'ActivateMember' => __('Activate_member?'),
                'DeactivateMember' => __('Deactivate_member?'),
                'ReallyActivateMember0' => __('Really_activate_member_{0}_?'),
                'ReallyDeactivateMember0' => __('Really_deactivate_member_{0}?'),
                'YesInfoMailWillBeSent' => __('Yes_info_mail_will_be_sent'),
                'ReallyActivateProduct0' => __('Really_activate_product_{0}_?'),
                'ReallyDeactivateProduct0' => __('Really_deactivate_product_{0}?'),
                'EditAttribute' => __('Edit_attribute'),
                'DeleteAttribute0' => __('Delete_attribute_{0}?'),
                'DeleteExplanation' => __('Check_and_do_not_forget_to_click_save_button.'),
                'PleaseChoseIfPaybackOrCreditUpload' => __('Please_chose_if_it_is_a_payback_or_a_credit_upload.'),
                'PleaseChoseTypeOfPayment' => __('Please_chose_the_type_of_your_payment.'),
                'DeletePayment' => __('Delete_payment'),
                'ReallyDeletePayment' => __('Really_delete_payment?'),
                'Date' => __('Date'),
                'Amount' => __('Amount'),
                'AmountMoney' => __('Amount_(Money)'),
                'PleaseChoseAtLeastOneMonth' => __('Please_chose_at_least_one_month.'),
                'DeleteMember' => __('Delete_member?'),
                'ReallyDeleteMember' => __('Really_delete_member?'),
                'BeCarefulNoWayBack' => __('Be_careful_there_is_no_way_back!'),
                'ErrorsOccurredWhileMemberWasDeleted' => __('Errors_occurred_while_member_was_deleted'),
                'ErrorsOccurredWhileProductStatusWasChanged' => __('Errors_occurred_while_product_status_was_changed'),
                'DeleteProducts' => __('Delete_products?'),
                'ReallyDelete0Products' => __('Really_delete_{0}_products?'),
                'ErrorsOccurredWhileProductsWereDeleted' => __('Errors_occurred_while_products_were_deleted'),
                'DeleteProduct' => __('Delete_product?'),
                'ReallyDeleteOneProduct' => __('Really_delete_1_product?'),
                'ErrorsOccurredWhileProductWasDeleted' => __('Errors_occurred_while_product_was_deleted'),
                'AddComment' => __('Add_comment'),
                'PleaseCancelAllOrderedProductsBeforeCancellingTheOrder' => __('Please_cancel_all_ordered_products_before_cancelling_the_order.'),
                'AddNewProduct' => __('Add_new_product?'),
                'ThisFunctionIsNotAvailableToday' => __('This_function_is_not_available_today.'),
                'ManuallySendOrderList' => __('Manually_send_order_list?'),
                'ReallyManuallySendOrderList' => __('Really_manually_send_order_list_to_{0}?'),
                'OrderPeriod' => __('Order_period'),
                'AnExistingOrderListWillBeOverwritten' => __('An_existing_order_list_will_be_overwritten!'),
                'ChangeGroupFor' => __('Change_group_for'),
                'TheUserNeedsToSignInAgain' => __('The_user_needs_to_sign_again.'),
                'Member' => __('Member'),
                'WhyIsPickupDayChanged' => __('Why_is_pickup_day_changed_(optional_field)?'),
                'ChangePickupDay' => __('Change_pickup_day'),
                'NewPickupDay' => __('New_pickup_day'),
                'ChangePickupDayInvoicesInfoText' => __('Change_pickup_day_invoices_info_text'),
                'ChangePickupDayResetOrderStateInfoText' => __('Change_pickup_day_invoices_reset_order_state_info_text'),
                'products' => __('products'),
                'product' => __('product'),
                'EmailAddressesSuccessfullyCopiedToClipboard' => __('{0}_email_addresses_have_been_copied_successfully_to_your_clipboard.'),
                'OneEmailAddressSuccessfullyCopiedToClipboard' => __('1_email_address_has_been_copied_successfully_to_your_clipboard.'),
                'TheUrlOfTheFileHasBeenCopiedToYourClipboard' => __('The_url_of_the_file_has_been_copied_successfully_to_your_clipboard.'),
                'UploadImageOrFile' => __('Upload_image_or_file'),
                'EmojiExplanationText' => __('Emoji_explanation_text'),
                'SuccessfullyCopiedTableContentToClipboard' => __('The_table_content_was_copied_successfully_to_your_clipboard.'),
                'ChangeMemberReasonIsMandatory' => __('The_reason_for_changing_the_member_is_mandatory.'),
                'WhyIsMemberEdited' => __('Why_is_the_member_edited?'),
                'ChangeMember' => __('Change_member'),
                'ToWhichMemberShouldTheOrderedProduct0Of1BeAssignedTo' => __('To_which_member_should_the_ordered_product_{0}_of_{1}_be_assigned_to?'),
                'PleaseSelectNewMember' => __('Please_select_new_member.'),
                'PleaseSelectAMember' => __('Please_select_a_member.'),
                'AmountThatShouldBeChangedToMember' => __('Amount_that_should_be_changed_to_member?'),
                'PleaseSelect' => __('Please_select...'),
                'all' => __('all'),
                'AddProductFeedback' => __('Add_product_feedback'),
                'AddProductFeedbackExplanationText0' => __('Add_product_feedback_explanation_text_{0}.'),
                'ChangeDeliveryRhythmForMultipleProductsTip' => __('Tip:_Change_delivery_rhythm_for_multiple_products:_Select_checkboxes_and_click_bottom_button.'),
                'GenerateInvoice' => __('Generate_invoice'),
                'ReallyGenerateInvoiceFor0' => __('Really_generate_invoice_for_{0}?'),
                'ShowPreview' => __('Show_preview'),
                'PaidInCash' => __('Paid_in_cash'),
                'ReallyCancelInvoiceNumber0OfCustomer1' => __('Really_cancel_invoice_number_{0}_of_{1}?'),
                'CancelInvoice' => __('Cancel_invoice'),
                'TheOrderWasPlacedSuccessfully' => __('The_order_was_placed_successfully.'),
                'ErrorsOccurredWhileCalculatingSellingPrice' => __('Errors_occurred_while_calculating_selling_price.'),
                'CalculateSellingPrice' => __('Calculate_selling_price'),
                'SurchargeInPercentFromPurchasePriceNet' => __('Surcharge_in_percent_from_purchase_price_net'),
                'CalculateSellingPriceExplanationText' => __('Calculate_selling_price_explanation_text.'),
                'ChangeProductName' => __('Change_product_name'),
                'GivenAmount' => __('Given_amount'),
                'back' => __('back'),
                'SendEmailToMember' => __('Send_email_to_member'),
                'SendEmailToBothMembers' => __('Send_email_to_both_members'),
                'ChangeStatus' => __('Change_status'),
            ],
            'pickupDay' => [
                'WereTheProductsPickedUp' => __('Were_the_products_picked_up?'),
                'AllProductsPickedUp' => __('All_products_picked_up?'),
                'WereTheProductsOfAllMembersPickedUp' => __('Were_the_products_of_all_members_picked_up?'),
                'ChangePickupDayComment' => __('Change_pickup_day_comment')
            ],
            'modalCustomer' => [
                'ChangeMemberComment' => __('Change_member_comment'),
                'ChangeGroup' => __('Change_group')
            ],
            'dialogProduct' => [
                'ChangeAmount' => __('Change_amount'),
                'ChangeStock' => __('Change_stock'),
                'ChangePrice' => __('Change_price'),
                'PricePerUnit' => __('Price_per_unit'),
                'gross' => __('gross'),
                'PricePerWeightForAdaptionAfterDelivery' => __('Price_per_weight_(for_adaption_after_delivery)'),
                'for' => __('for'),
                'approximateDeliveryWeightIn0PerUnit' => __('approximate_delivery_weight_in_{0}_per_unit'),
                'EditPriceChangeOpenOrderDetailsInfoText' => __('Edit_price_change_open_order_details_info_text'),
                'EditPriceUseWeightAsAmount' => __('Edit_price_use_weight_as_amount'),
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
                'EnterDeposit' => __('Enter_deposit'),
                'Deposit' => __('Deposit'),
                'EnterZeroForDelete' => __('Enter_zero_for_delete'),
                'IsProductAStockProduct' => __('Is_the_product_a_stock_product?'),
                'StockProduct' => __('Stock_product'),
                'CurrentStock' => __('Current_stock'),
                'AvailableAmount' => __('Available_amount'),
                'OrdersPossibleUntilAmountOf' => __('Orders_possible_until_amount_of'),
                'zeroOrSmallerZero' => __('zero_or_smaller_zero'),
                'ReasonForChange' => __('Reason_for_change'),
                'Manufacturer' => __('Manufacturer'),
                'ForManufacturersAndContactPersonsCanBeChangedInManufacturerSettings' => __('For_manufacturers_and_contact_persons._Can_be_changed_in_manufacturer_settings.'),
                'NotificationIfAmountLowerThan' => __('Notification_if_amount_lower_than'),
                'MinimalStockAmount' => __('Minimal_stock_amount'),
                'IsProductStockProduct' => __('Is_product_a_stock_product?'),
                'TheDeliveryRhythmOfStockProductsIsAlwaysWeekly' => __('The_delivery_rhythm_of_stock_products_is_always_weekly.'),
                'DeliveryRhythm' => __('Delivery_rhythm'),
                'ChangeDeliveryRhythm' => __('Change_delivery_rhythm'),
                'FirstDeliveryDay' => __('First_delivery_day'),
                'DeliveryDay' => __('Delivery_day'),
                'FirstDeliveryDayInfoOneProduct' => __('First_delivery_day_info_(one_product).'),
                'FirstDeliveryDayInfoMultipleProducts' => __('First_delivery_day_info_(multiple_products).'),
                'canBeLeftBlank' => __('can_be_left_blank'),
                'OrderPossibleUntil' => __('Order_possible_until'),
                'InfoPageForDeliveryRhythm' => __('Info_page_for_delivery_rhythm'),
                'DocsUrlOrderHandling' => Configure::read('app.htmlHelper')->getDocsUrl(__('docs_route_order_handling')),
                'LastOrderWeekday' => __('Last_order_weekday'),
                'SendOrderListsDay' => __('Send_order_lists_day'),
                'OrderListsAreSentAutomaticallyNextDayInTheMorning' => __('Order_lists_are_sent_automatically_next_day_in_the_morning.'),
                'OrderListsAreSentAutomaticallyOnThisDay' => __('Order_lists_are_sent_automatically_on_this_day.'),
                'IsTheProductAlwaysAvailable' => __('Is_the_product_always_available?'),
                'DefaultQuantityAfterSendingOrderLists' => __('Default_quantity_after_sending_order_lists'),
                'DefaultQuantityAfterSendingOrderListsHelpText' => __('After_the_order_lists_are_sent_available_amount_is_set_to_this_value.'),
                'EnterPurchasePrice' => __('Enter_purchase_price'),
                'StorageLocation' => __('Storage_location'),
                'BarcodeDescription' => __('EAN_13_code'),
            ],
            'upload' => [
                'delete' => __('delete'),
                'DeleteImage' => __('Delete_image?'),
                'ReallyDeleteImage' => __('Really_delete_image?'),
                'rotateAntiClockwise' => __('rotate_anti_clockwise?'),
                'rotateClockwise' => __('rotate_clockwise?'),
                'ChangeGeneralTermsAndConditions' => __('Change_general_terms_and_conditions'),
                'PleaseUploadAnImage' => __('Please_upload_an_image.'),
            ],

            'syncBase' => [
                'UsernameOrPasswordWrongPleaseCheckThatThereAreNoSpecialCharactersInYourPassword' => __('Username_or_password_is_wrong._Please_check_that_there_are_no_special_characters_in_your_password.')
            ],
            'syncProducts' => [
                'Id' => __('Id'),
                'Product' => __('Product'),
                'AnAttributeCannotBeAssignedToAProduct' => __('An_attribute_cannot_be_assigned_to_a_product.'),
                'AProductCannotBeAssignedToAnAttribute' => __('A_product_cannot_be_assigned_to_an_attribute.'),
                'ThisProductIsAlwaysAvailable' => __('This_product_is_always_available.'),
                'UpdateSoftwareNotification' => __('Please_update_FoodCoopShop.'),
            ],

            'syncProductData' => [
                'Image' => __('Image'),
                'Name' => __('Name'),
                'NameAdditionalInfo' => __('Name_additional_info'),
                'StockProduct' => __('Stock_product'),
                'Quantity' => __('Amount'),
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

    public function renderAsJsFile(): void
    {
        $this->response = $this->response->withType('application/javascript');
        $this->viewBuilder()->setLayout('ajax');
        $this->set('localizedJs', $this->getStrings());
    }

    public function afterFilter(EventInterface $event): void
    {
        parent::afterFilter($event);
        if (Configure::check('app.outputStringReplacements')) {
            $newOutput = OutputFilterService::replace($this->response->getBody()->__toString(), Configure::read('app.outputStringReplacements'));
            $this->response = $this->response->withStringBody($newOutput);
        }
    }

}

?>

<?php
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
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td style="font-weight:bold;font-size:18px;padding-bottom:20px;">
                <?php echo __('Hello'); ?> <?php echo $appAuth->getUsername(); ?>,
            </td>
        </tr>
        <tr>
            <td>
                <?php
                   echo __('thank_you_for_your_order_from_{0}.', [
                       $cart['Cart']->modified->i18nFormat(
                           Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'))
                   ]);
                ?>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>

<?php
foreach($cart['CartProducts'] as $pickupDay => $cartProducts) {
    echo $this->element('email/tableHead', ['cellpadding' => 6]);
        echo $this->element('email/orderedProductsTable', [
            'manufacturerId' => null,
            'pickupDay' => $pickupDay,
            'pickupDayEntities' => $pickupDayEntities,
            'cartProducts' => $cartProducts['Products'],
            'depositSum' => $cartProducts['CartDepositSum'],
            'productSum' => $cartProducts['CartProductSum'],
            'productAndDepositSum' => $cartProducts['CartDepositSum'] + $cartProducts['CartProductSum'],
            'selfServiceModeEnabled' => false
        ]);
    echo $this->element('email/tableFoot');
}
?>

<?php echo $this->element('email/tableHead'); ?>
    <tbody>

        <?php if ($appAuth->Cart->getProductsWithUnitCount() > 0) { ?>
            <tr><td style="padding-top:20px;">
                * <?php echo __('The_delivered_weight_will_eventually_be_adapted_which_means_the_price_can_change_slightly.'); ?>
            </td></tr>
        <?php } ?>

        <tr><td style="padding-top:20px;">
            <?php echo __('Including_vat'); ?> <?php echo $this->MyNumber->formatAsCurrency($appAuth->Cart->getTaxSum()); ?>
        </td></tr>

        <tr><td>
            <?php
                if ($this->MyHtml->paymentIsCashless()) {
                    echo __('The_amount_was_reduced_from_your_credit_balance.');
                } else {
                    echo __('Please_pay_when_picking_up_products.');
                }
            ?>
        </td></tr>

        <?php if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && Configure::read('app.manufacturerComponensationInfoText') != '') { ?>
            <tr><td style="padding-top:20px;"><b>
                <?php echo Configure::read('app.manufacturerComponensationInfoText'); ?>
            </b></td></tr>
        <?php } ?>


        <?php if (Configure::read('app.showPickupPlaceInfo')) { ?>
            <tr><td><p>
                <?php
                    echo __(
                        'Pickup_place:_{0}', [
                            str_replace('<br />', ', ', $this->MyHtml->getAddressFromAddressConfiguration())
                        ]
                    );
                ?>
            </p></td></tr>
        <?php } ?>

        <?php if (Configure::read('app.generalTermsAndConditionsEnabled') && Configure::read('app.rightOfWithdrawalEnabled')) { ?>
            <tr><td style="font-size:12px;">
                <?php
                    if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
                        echo __('You_can_find_a_detailed_list_of_your_order_in_the_attached_order_confirmation.') . ' ';
                    }
                    echo __('The_information_about_right_of_withdrawal_is_mandatory_but_it_is_not_valid_for_perishable_goods.');
                ?>
            </td></tr>
        <?php } ?>

    </tbody>
<?php echo $this->element('email/tableFoot'); ?>

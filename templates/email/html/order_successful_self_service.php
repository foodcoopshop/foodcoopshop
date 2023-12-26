<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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
                <?php echo __('Hello'); ?> <?php echo $identity->name; ?>,
            </td>
        </tr>
        <tr>
            <td>
                <?php
                   echo __('thank_you_for_your_purchase.');
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
            'cartProducts' => $cartProducts['Products'],
            'depositSum' => $cartProducts['CartDepositSum'],
            'productSum' => $cartProducts['CartProductSum'],
            'productAndDepositSum' => $cartProducts['CartDepositSum'] + $cartProducts['CartProductSum'],
            'selfServiceModeEnabled' => true
        ]);
    echo $this->element('email/tableFoot');
}
?>

<?php echo $this->element('email/tableHead'); ?>
    <tbody>

        <tr><td style="padding-top:20px;">

            <?php if (Configure::read('app.showTaxInOrderConfirmationEmail')) { ?>

                <?php echo __('Including_vat'); ?> <?php echo $this->MyNumber->formatAsCurrency($identity->CartService->getTaxSum()); ?>
            </td></tr>

        <tr><td>

            <?php } ?>

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

        <?php
            if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED') && isset($identity) && !$identity->get('newsletter_enabled')) {
                echo '<tr><td style="font-size:12px;">';
                    echo __('You_can_subscribe_our_newsletter_<a href="{0}">in_the_admin_areas_menu_point_my_data</a>.', [Configure::read('App.fullBaseUrl') . $this->Slug->getCustomerProfile()]);
                echo '</td></tr>';
            }
        ?>

    </tbody>
<?php echo $this->element('email/tableFoot'); ?>

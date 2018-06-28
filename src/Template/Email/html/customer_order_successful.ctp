<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
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
            <td style="padding-bottom:20px;">
            	<?php echo __('thank_you_for_your_order_number_{0}_from_{1}.', [$order->id_order, $order->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'))]); ?>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>

<?php echo $this->element('email/tableHead', ['cellpadding' => 6]); ?>
    <?php echo $this->element('email/orderedProductsTable', [
        'manufacturerId' => null,
        'cartProducts' => $cart['CartProducts'],
        'depositSum' => $appAuth->Cart->getDepositSum(),
        'productSum' => $appAuth->Cart->getProductSum(),
        'productAndDepositSum' => $appAuth->Cart->getProductAndDepositSum()
    ]); ?>
<?php echo $this->element('email/tableFoot'); ?>

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
                    echo __('The_amount_will_be_reduced_from_your_credit_balance.');
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

        <tr><td><p>
            <?php
                echo __(
                    'Please_pick_up_your_products_on_{0}_at_{1}.', [
                        '<b>'.$this->MyTime->getFormattedDeliveryDateByCurrentDay().'</b>',
                        str_replace('<br />', ', ', $this->MyHtml->getAddressFromAddressConfiguration())
                    ]
                );
            ?>
        </p></td></tr>
        
        <tr><td style="font-size:12px;">
        	<?php echo __('You_can_find_a_detailed_list_of_your_order_in_the_attached_order_confirmation.'); ?>
        </td></tr>
        
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>

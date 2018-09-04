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
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>
    
    <?php echo $this->element('email/greeting', ['data' => $oldOrderDetail->customer]); ?>
    
    <tr>
        <td>

            <p>
            	<?php echo __d('admin', 'The_weight_of_the_product_{0}_has_been_adapted.', ['<b>'.$oldOrderDetail->product_name.'</b>']); ?> <?php echo __d('admin', 'You_have_ordered_{0}_units_of_it_on_{1}_at_manufacturer_{2}.', [
            	    $oldOrderDetail->product_amount,
            	    $oldOrderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')),
            	    '<b>'.$oldOrderDetail->product->manufacturer->name.'</b>'
            	]); ?>
            </p>

            <ul style="padding-left: 10px;">
                <li><?php echo __d('admin', 'Old_price_for'); ?> <?php echo $this->MyNumber->formatUnitAsDecimal($oldOrderDetail->order_detail_unit->product_quantity_in_units) . ' ' . $oldOrderDetail->order_detail_unit->unit_name; ?>: <b><?php echo $this->MyNumber->formatAsCurrency($oldOrderDetail->total_price_tax_incl); ?></b></li>
                <li><?php echo __d('admin', 'New_price_for'); ?> <?php echo $this->MyNumber->formatUnitAsDecimal($newProductQuantityInUnits) . ' ' . $oldOrderDetail->order_detail_unit->unit_name; ?>: <b><?php echo $this->MyNumber->formatAsCurrency($newOrderDetail->total_price_tax_incl); ?></b></li>
            </ul>
            
            <p>
            	<?php echo __d('admin', 'The_base_price_is_{0}.', [$this->PricePerUnit->getPricePerUnitBaseInfo($oldOrderDetail->order_detail_unit->price_incl_per_unit, $oldOrderDetail->order_detail_unit->unit_name, $oldOrderDetail->order_detail_unit->unit_amount)]); ?>
            </p>

            <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                <p><?php echo __d('admin', 'PS:_Your_credit_has_been_adapted_automatically.'); ?></p>
            <?php } ?>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>

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
    
    <?php echo $this->element('email/greeting', ['data' => $oldOrderDetail->customer]); ?>
    
    <tr>
        <td>

            <p>
            	<?php echo __d('admin', 'The_amount_of_the_product_{0}_has_been_adapted.', ['<b>'.$oldOrderDetail->product_name.'</b>']); ?> <?php echo __d('admin', 'You_have_ordered_it_on_{0}_at_manufacturer_{1}.', [
            	    $oldOrderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')),
            	    '<b>'.$oldOrderDetail->product->manufacturer->name.'</b>'
            	]); ?>
            </p>

            <ul style="padding-left: 10px;">
                <li><?php echo __d('admin', 'Old_amount'); ?>: <b><?php echo $oldOrderDetail->product_amount; ?></b></li>
                <li><?php echo __d('admin', 'New_amount'); ?>: <b><?php echo $newOrderDetail->product_amount; ?></b></li>
            </ul>

            <p>
                <?php echo __d('admin', 'Why_has_the_amount_been_adpated?'); ?><br />
                <b><?php echo '"' . $editAmountReason . '"'; ?></b>
            </p>
                
            <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                <p><?php echo __d('admin', 'PS:_Your_credit_has_been_adapted_automatically.'); ?></p>
            <?php } ?>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>

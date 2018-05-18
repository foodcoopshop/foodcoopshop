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
    
        <?php echo $this->element('email/greeting', ['data' => $oldOrderDetail->order->customer]); ?>
        
        <tr>
        <td>

            <p>
                Die Anzahl des Produktes <b><?php echo $oldOrderDetail->product_name; ?></b> wurde angepasst. Du hast am <?php echo $oldOrderDetail->order->date_add->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort')); ?> beim Hersteller <b><?php echo $oldOrderDetail->product->manufacturer->name; ?></b>
                bestellt.
            </p>

            <ul style="padding-left: 10px;">
                <li>Alte Anzahl: <b><?php echo $oldOrderDetail->product_amount; ?></b></li>
                <li>Neue Anzahl: <b><?php echo $newOrderDetail->product_amount; ?></b></li>
            </ul>

            <p>
                Warum wurde die Anzahl angepasst?<br />
                <b>
                <?php

                if ($editAmountReason != '') {
                    echo '"' . $editAmountReason . '"';
                } else {
                    echo 'Kein Grund angegeben.';
                }
                ?>
                </b>
            </p>
                
                <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                    <p>PS: Dein Guthaben wurde automatisch angepasst.</p>
                <?php } ?>

            </td>

    </tr>

</tbody>
</table>

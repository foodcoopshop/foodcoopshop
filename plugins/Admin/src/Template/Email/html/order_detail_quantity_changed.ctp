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
                Das Gewicht des Produktes <b><?php echo $oldOrderDetail->product_name; ?></b> wurde korrigiert. Du hast <?php echo $oldOrderDetail->product_amount; ?> Stück davon am <?php echo $oldOrderDetail->order->date_add->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort')); ?> beim Hersteller <b><?php echo $oldOrderDetail->product->manufacturer->name; ?></b>
                bestellt.
            </p>

            <ul style="padding-left: 10px;">
                <li>Alter Preis für <?php echo $this->MyHtml->formatAsDecimal($oldOrderDetail->quantity_in_units) . ' ' . $oldOrderDetail->unit_name; ?>: <b><?php echo $this->MyHtml->formatAsDecimal($oldOrderDetail->total_price_tax_incl); ?> €</b></li>
                <li>Neuer Preis für <?php echo $this->MyHtml->formatAsDecimal($newOrderDetail->quantity_in_units) . ' ' . $newOrderDetail->unit_name; ?>: <b><?php echo $this->MyHtml->formatAsDecimal($newOrderDetail->total_price_tax_incl); ?> €</b></li>
            </ul>

                <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                    <p>PS: Dein Guthaben wurde automatisch angepasst.</p>
                <?php } ?>

            </td>

    </tr>

</tbody>
</table>

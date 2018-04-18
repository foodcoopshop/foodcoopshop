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
    
        <?php echo $this->element('email/greeting', ['data' => $orderDetail->order->customer]); ?>
                
        <tr>
        <td>

            <p>
                <b><?php echo $orderDetail->product_name; ?></b>
                kann leider nicht geliefert werden.
            </p>

            <ul style="padding-left: 10px;">
                <li>Preis: <b><?php echo $this->MyHtml->formatAsDecimal($orderDetail->total_price_tax_incl); ?> €</b></li>
                <li>Anzahl: <b><?php echo $orderDetail->product_quantity; ?></b></li>
                <li>Hersteller: <b><?php echo $orderDetail->product->manufacturer->name; ?></b></li>
                <li>Bestelldatum: <b><?php echo $orderDetail->order->date_add->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort')); ?></b></li>
            </ul>

            <p>
                Warum wurde das Produkt storniert?<br />
                <b>
                <?php

                if ($cancellationReason != '') {
                    echo '"' . $cancellationReason . '"';
                } else {
                    echo 'Kein Grund angegeben.';
                }
                ?>
                </b>
            </p>

            <p>Unsere Produzenten können leider ab und zu die bestellte Ware
                nicht liefern. Du erhältst du diese Mail, damit du rechtzeitig für
                Ersatz sorgen kannst.</p>
            <p>Vielen Dank für dein Verständnis!</p>
                
                <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                    <p>PS: Dein Guthaben wurde automatisch angepasst.</p>
                <?php } ?>

            </td>

    </tr>

</tbody>
</table>

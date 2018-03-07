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
?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <tr>
        <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                Hallo <?php echo $manufacturer->address_manufacturer->firstname; ?>,
            </td>
    </tr>

    <tr>
        <td>

            <p>
                In dieser Rechnung sind alle Bestellungen enthalten, die im <b><?php echo $invoicePeriodMonthAndYear; ?></b>
                getätigt wurden. <b>Achtung:</b> es wird das Bestelldatum
                berücksichtigt, nicht das Lieferdatum.
            </p>

            <p>Sollten der Rechnungsbetrag nicht mit deinen Aufzeichnungen
                übereinstimmen, sag uns bitte umgehend Bescheid.</p>

            <p>Der Rechnungsbetrag wird in den nächsten Tagen auf dein Konto
                überwiesen.</p>

            <p>
                <b>Vielen Dank, dass du uns belieferst!</b>
            </p>

        </td>

    </tr>

</tbody>
</table>

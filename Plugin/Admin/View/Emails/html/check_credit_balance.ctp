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
    
        <?php echo $this->element('email/greeting', array('data' => $customer)); ?>
        
        <tr>
        <td>

            <p>
                dein Guthaben ist aufgebraucht und beträgt <b
                    style="color: #f3515c;"><?php echo $delta; ?></b>.
            </p>

            <p>Bitte überweise bald wieder ein neues Guthaben (z.B. € 100,00) auf
                unser Konto.</p>

            <p>
                Vergiss bitte nicht, diesen Betrag <b>in unser Guthaben-System
                    einzutragen</b>, da es ansonsten zwar auf unserem Bankkonto
                gutgeschrieben ist, aber nicht in deinem Guthaben-System aufscheint.
            </p>

            <p>
                Hier der Link zum Eintragen:<br /> <a
                    href="<?php echo Configure::read('app.cakeServerName').'/admin/payments/product'; ?>"><?php echo Configure::read('app.cakeServerName').'/admin/payments/product'; ?></a>
            </p>

        </td>

    </tr>

</tbody>
</table>
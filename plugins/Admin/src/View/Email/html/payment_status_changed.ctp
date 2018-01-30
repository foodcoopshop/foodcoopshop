<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>
    
        <?php echo $this->element('email/greeting', ['data' => $data]); ?>
        
        <tr>
        <td>

            <p>
                Der Status deiner Guthaben-Aufladung vom <b><?php echo Configure::read('app.timeHelper')->formatToDateNTimeShort($request['Payments']['date_add']); ?></b> über <b>€ <?php echo Configure::read('app.htmlHelper')->formatAsDecimal($request['Payments']['amount']); ?></b> wurde auf <b><?php echo $newStatusAsString; ?></b> geändert.
                
                <?php if ($request['Payments']['approval'] == -1) { ?>
                    Bitte überprüfe die Guthaben-Aufladung, die du im System eingetragen hast, mit den Zahlungen auf deinem Bankkonto.
                <?php } ?>
                
            </p>
            
            <?php
            if ($request['Payments']['approval_comment'] != '') {
                echo '<p>Kommentar:<br />';
                echo '<b>"'.$request['Payments']['approval_comment'] . '</b>"';
                echo '</p>';
            }
            ?>
            
            <p>
                Hier der Link zu deinem Guthaben-System:<br />
                <a href="<?php echo Configure::read('app.cakeServerName').'/admin/payments/product'; ?>"><?php echo Configure::read('app.cakeServerName').'/admin/payments/product'; ?></a>
            </p>
            
        </td>

    </tr>

</tbody>
</table>

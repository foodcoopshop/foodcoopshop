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

            <p><?php echo $lastOrderDayAsString; ?> ist schon wieder der letzte Bestelltag. Bis <?php echo $lastOrderDayAsString; ?> Mitternacht hast
                du noch Zeit.</p>

            <p>
                Hier geht's zur Homepage:<br /> <a
                    href="<?php echo Configure::read('AppConfig.cakeServerName'); ?>"><?php echo Configure::read('AppConfig.cakeServerName'); ?></a>
            </p>

            <p>
                Und hier kannst du diese E-Mail abbestellen:<br /> <a
                    href="<?php echo Configure::read('AppConfig.cakeServerName').$this->Slug->getCustomerProfile(); ?>"><?php echo Configure::read('AppConfig.cakeServerName').$this->Slug->getCustomerProfile(); ?></a>
            </p>

        </td>

    </tr>

</tbody>
</table>

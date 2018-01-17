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
            <td style="font-weight:bold;font-size:18px;padding-bottom:20px;">
                Hallo <?php echo $appAuth->getUsername(); ?>,
            </td>
        </tr>
        <tr>
            <td style="padding-bottom:20px;">
                vielen Dank für deine Bestellung Nr. <?php echo $order['Order']['id_order']; ?> vom <?php echo $this->MyTime->formatToDateNTimeLongWithSecs($order['Order']['date_add']); ?>.
            </td>
        </tr>
    </tbody>
</table>

<?php echo $this->element('email/tableHead', array('cellpadding' => 6)); ?>
    <?php echo $this->element('email/orderedProductsTable', array(
        'manufacturerId' => null,
        'cartProducts' => $cart['CartProducts'],
        'depositSum' => $depositSum,
        'productSum' => $productSum,
        'productAndDepositSum' => $productAndDepositSum
    )); ?>
</table>

<?php echo $this->element('email/tableHead'); ?>
    <tbody>
    
        <tr><td style="padding-top:20px;">
            Enthaltene Umsatzsteuer: <?php echo $this->MyHtml->formatAsEuro($appAuth->Cart->getTaxSum()); ?>
        </td></tr>
        
        <tr><td>
            <?php
            if ($this->MyHtml->paymentIsCashless()) {
                $paymentText = 'Der Gesamtbetrag wurde von deinem Guthaben abgezogen.';
            } else {
                $paymentText = 'Bitte vergiss nicht, den Betrag beim Abholen so genau wie möglich in bar mitzunehmen.';
            }
                echo $paymentText;
            ?>
        </td></tr>
        
        <?php if (Configure::read('AppConfig.db_config_FCS_USE_VARIABLE_MEMBER_FEE') && Configure::read('AppConfig.manufacturerComponensationInfoText') != '') { ?>
            <tr><td style="padding-top:20px;"><b>
                <?php echo Configure::read('AppConfig.manufacturerComponensationInfoText'); ?>
            </b></td></tr>
        <?php } ?>

        <tr><td><p>
            Bitte hole deine Produkte am <b><?php echo $this->MyTime->getFormattedDeliveryDateByCurrentDay(); ?></b> bei uns (<?php echo str_replace('<br />', ', ', $this->MyHtml->getAddressFromAddressConfiguration()); ?>) ab.
        </p></td></tr>
        
        <tr><td style="font-size:12px;">
            Eine detaillierte Auflistung deiner Bestellung findest du in der angehängten Bestellübersicht (PDF). Die Informationen zum Rücktrittsrecht sind gesetzlich vorgeschrieben, das Rücktrittsrecht für verderbliche Waren ist allerdings ausgeschlossen.
        </td></tr>
        
    </tbody>
</table>

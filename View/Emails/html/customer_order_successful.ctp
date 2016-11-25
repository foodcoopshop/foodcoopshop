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
	<tbody>
	
		<tr>
    		<?php
    		  $columns = array('Anzahl', 'Produkte', 'Hersteller', 'Preis', 'Pfand');
    		  foreach($columns as $column) {
    		     echo '<td align="center" style="padding: 10px;font-weight:bold;border:1px solid #d6d4d4;background-color:#fbfbfb;">'.$column.'</td>';
    		  }
    		?>
    	</tr>
		
        <?php foreach($cart['CakeCartProducts'] as $product) { ?>
        	<tr>
        		<?php
        		  $amountStyle = '';
        		  if ($product['amount'] > 1) {
        		      $amountStyle = 'font-weight:bold;';
        		  }
        		?>
        		<td valign="middle" align="center" style="border:1px solid #d6d4d4;<?php echo $amountStyle;?>">
        			<?php echo $product['amount']; ?>x
        		</td>
        		<td valign="middle" style="border:1px solid #d6d4d4;">
        			<?php
        			     echo $product['productName'];
        			     if ($product['unity'] != '') {
        			         echo ' : ' . $product['unity'];
        			     }
        			 ?>
        		</td>
        		<td valign="middle" style="border:1px solid #d6d4d4;">
        			<?php echo $product['manufacturerName']; ?>
        		</td>
        		<td valign="middle" align="right" style="border:1px solid #d6d4d4;">
        			<?php echo $this->MyHtml->formatAsEuro($product['price']); ?>
        		</td>
        		<td valign="middle" align="right" style="border:1px solid #d6d4d4;">
        			<?php
        			    if ($product['deposit'] > 0) {
        			        echo $this->MyHtml->formatAsEuro($product['deposit']);
                        }
                    ?>
        		</td>
        	</tr>
        <?php } ?>
		
		<tr>
			<td style="border:1px solid #d6d4d4;" colspan="3"></td>
			<td align="right" style="font-weight:bold;border:1px solid #d6d4d4;border-right:none;"><?php echo $this->MyHtml->formatAsEuro($appAuth->Cart->getProductSum()); ?></td>
			<td align="right" style="font-weight:bold;border:1px solid #d6d4d4;">
				<?php
				    if ($appAuth->Cart->getDepositSum() > 0) {
				        echo $this->MyHtml->formatAsEuro($appAuth->Cart->getDepositSum());
				    }
				?>
			</td>
		</tr>
		
		<tr>
			<td style="background-color:#fbfbfb;border:1px solid #d6d4d4;" colspan="2"></td>
			<td align="right" style="font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;border-right:none;">Gesamt</td>
			<td align="center" style="font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;" colspan="2">
				<?php
				    echo $this->MyHtml->formatAsEuro($appAuth->Cart->getProductAndDepositSum());
				?>
			</td>
		</tr>
		
	</tbody>
</table>

<?php echo $this->element('email/tableHead'); ?>
	<tbody>
		<tr>
			<td style="padding-top:20px;">
				Enthaltene Umsatzsteuer: <?php echo $this->MyHtml->formatAsEuro($appAuth->Cart->getTaxSum()); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php
    				if ($this->MyHtml->paymentIsCashless()) {
    				    $paymentText = 'Der Gesamtbetrag wurde von deinem Guthaben abgezogen.';
    				} else {
    				    $paymentText = 'Bitte vergiss nicht, den Betrag beim Abholen so genau wie möglich in bar mitzunehmen.';
    				}
    				echo $paymentText;
				?>
			</td>
		</tr>
		<tr>
			<td>
				Eine detaillierte Auflistung deiner Bestellung findest du im beiliegenden Dokument "Bestelluebersicht-<?php echo $order['Order']['id_order']; ?>.pdf".
			</td>
		</tr>
		<tr>
			<td>
				Die Informationen zum Rücktrittsrecht und das Rücktrittsformular sind gesetzlich vorgeschrieben. Sie machen für Foodcoops aber wenig Sinn, da das Rücktrittsrecht für verderbliche Waren ausgeschlossen ist.
			</td>
		</tr>
	</tbody>
</table>

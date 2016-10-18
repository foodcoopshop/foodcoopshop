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
					du hast um ein neues Passwort angefragt, es lautet:<br />
					<b><?php echo $password; ?></b>
				</p>
				
				<p>Du kannst dich hier anmelden und das Passwort ändern:<br />
				<a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getChangePassword(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getChangePassword(); ?></a>
				
			</td>
			
		</tr>
		
	</tbody>
</table>
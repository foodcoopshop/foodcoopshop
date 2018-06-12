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
    
        <?php echo $this->element('email/greeting', ['data' => $data]); ?>
        
        <tr>
        <td>

            <p>
                Deine Zeit-Eintragung vom <b>
                	<?php echo $payment->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')); ?>
            	</b>
            	<?php if ($payment->working_day) { ?>
            		(Arbeitstag: <b><?php echo $payment->working_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')); ?></b>)
            	<?php } ?>
            	wurde geändert.
			</p>

            <?php if ($unchangedPaymentSeconds != $payment->seconds) { ?>
                <p>Die eingetragene Zeit wurde von <b><?php echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($unchangedPaymentSeconds); ?></b> auf <b><?php echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($payment->seconds); ?></b> angepasst.</p>
            <?php } ?>
            
            <?php if ($unchangedPaymentApproval != -1 && $payment->approval == -1) { ?>
                <p>Deine Zeit-Aufladung wurde als "<?php echo $this->MyHtml->getApprovalStates()[$payment->approval]; ?>" markiert.</p>
            <?php } ?>
            
            <?php
            if ($payment->approval_comment != '') {
                echo '<p>Kommentar:<br />';
                echo '<b>"'.$payment->approval_comment . '</b>"';
                echo '</p>';
            }
            ?>
            
            <p>
                Hier der Link zu deinem <?php echo $this->TimebasedCurrency->getName(); ?><br />
                <a href="<?php echo Configure::read('app.cakeServerName').$this->Slug->getMyTimebasedCurrencyBalanceForCustomers(); ?>"><?php echo Configure::read('app.cakeServerName').$this->Slug->getMyTimebasedCurrencyBalanceForCustomers(); ?></a>
            </p>
            
        </td>

    </tr>

</tbody>
</table>

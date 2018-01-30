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

    <tr>
        <td style="font-weight: bold; font-size: 18px; padding-bottom: 10px;">
            <p>Liebe(r) Finanz-Verantwortliche(r),</p>
        </td>
    </tr>

    <tr>
        <td>

            <p>
                die Rechnungen vom <b><?php echo $this->MyTime->getLastMonthNameAndYear(); ?></b> wurden soeben verschickt.
            </p>

            <p>
                Hier findest du die Übersicht zum Überweisen: <br />
                <?php $link = Configure::read('app.cakeServerName').'/admin/order_details/index/dateFrom:'.$dateFrom.'/dateTo:'.$dateTo.'/orderState:'.ORDER_STATE_CASH.','.ORDER_STATE_CASH_FREE.','.ORDER_STATE_OPEN.'/groupBy:manufacturer'; ?>
                <a href="<?php echo $link; ?>"><?php echo $link; ?></a>
            </p>
            
            <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                <p>
                    Bei dieser Gelegenheit könntest du auch gleich die ins System eingetragenen Guthaben-Aufladungen mit den tatsächlichen Überweisungen vergleichen und bestätigen. Das spart am Ende des Jahres eine Menge Arbeit und macht das Guthaben-System weniger fehleranfällig.<br />
                    <?php $link = Configure::read('app.cakeServerName').$this->Slug->getReport('product'); ?>
                    <a href="<?php echo $link; ?>"><?php echo $link; ?></a> (Link nur für Superadmins).
                </p>
            <?php } ?>

            <p>Vielen Dank für deine Arbeit!</p>

        </td>

    </tr>

</tbody>
</table>

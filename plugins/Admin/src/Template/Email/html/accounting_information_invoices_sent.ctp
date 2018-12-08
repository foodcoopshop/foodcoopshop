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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <tr>
        <td style="font-weight: bold; font-size: 18px; padding-bottom: 10px;">
            <p><?php echo __d('admin', 'Dear_financial_responsible'); ?>,</p>
        </td>
    </tr>

    <tr>
        <td>

            <p>
                <?php echo __d('admin', 'the_invoices_from_{0}_have_just_been_sent.', [$this->MyTime->getLastMonthNameAndYear()]); ?>
            </p>

            <p>
                <?php echo __d('admin', 'Here_you_find_the_overview_for_making_the_transfers:'); ?><br />
                <?php $formattedCurrentDay = $this->MyTime->formatToDateShort($cronjobRunDay); ?>
                <?php $link = Configure::read('app.cakeServerName') . $this->Slug->getActionLogsList() . '?types[]=cronjob_send_invoices&dateFrom='.$formattedCurrentDay.'&dateTo='.$formattedCurrentDay; ?>
                <a href="<?php echo $link; ?>"><?php echo $link; ?></a>
            </p>
            
            <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                <p>
                    <?php echo __d('admin', 'This_is_a_great_opportunity_to_check_the_credit_uploads_link_below.')?><br />
                    <?php $link = Configure::read('app.cakeServerName').$this->Slug->getReport('product'); ?>
                    <a href="<?php echo $link; ?>"><?php echo $link; ?></a> <?php echo __d('admin', '(Link_only_works_for_superadmins).'); ?>
                </p>
            <?php } ?>

            <p><?php echo __d('admin', 'Thank_you_very_much_for_your_work!'); ?></p>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>

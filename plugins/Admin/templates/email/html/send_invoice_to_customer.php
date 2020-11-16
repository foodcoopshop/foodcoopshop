<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <tr>
        <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                <?php echo __d('admin', 'Hello'); ?> <?php echo $customerName; ?>,
            </td>
    </tr>

    <tr>
        <td>

            <p><?php echo __d('admin', 'Please_find_your_current_invoice_attached.'); ?></p>

            <p><?php echo __d('admin', '{0}_thanks_you_for_your_purchase!', [Configure::read('appDb.FCS_APP_NAME')]); ?></p>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>

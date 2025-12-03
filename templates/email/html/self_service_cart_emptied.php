<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
        <tr>
            <td>
                <p>
                    <?php
                    echo __('The following cart was emptied because the user was logged out.')
                    ?>
                </p>
            </td>
        </tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>

<?php
foreach($cart['CartProducts'] as $pickupDay => $cartProducts) {
    echo $this->element('email/tableHead', ['cellpadding' => 6]);
        echo $this->element('email/orderedProductsTable', [
            'manufacturerId' => null,
            'pickupDay' => $pickupDay,
            'cartProducts' => $cartProducts['Products'],
            'depositSum' => $cartProducts['CartDepositSum'],
            'productSum' => $cartProducts['CartProductSum'],
            'productAndDepositSum' => $cartProducts['CartDepositSum'] + $cartProducts['CartProductSum'],
            'selfServiceModeEnabled' => true
        ]);
    echo $this->element('email/tableFoot');
}
?>

<?php echo $this->element('email/tableHead'); ?>
    <tbody>

        <tr><td style="padding-top:20px;">
            <?php echo __('Including_vat'); ?> <?php echo $this->MyNumber->formatAsCurrency($identity->getTaxSum()); ?>
        </td></tr>

        <tr><td>
            <?php
                echo __('Date');
            ?>: <?php
                echo date(Configure::read('app.timeHelper')->getI18Format('DateNTimeShortWithSecsAlt'), time());
            ?>
        </td></tr>
    </tbody>
<?php echo $this->element('email/tableFoot'); ?>

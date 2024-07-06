<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;
use App\Services\OrderCustomerService;

$beforeTotalPriceColspan = 1;
$priceColspan = 2;
$columns = [
    __('Amount'),
    __('Product'),
];
if (Configure::read('app.showManufacturerListAndDetailPage')) {
    $columns[] = __('Manufacturer');
    $beforeTotalPriceColspan++;
    $priceColspan++;
}
$columns[] = __('Price');
if ($depositSum > 0) {
    $columns[] = __('Deposit');
}

?>
  <tbody>

        <?php if (!$selfServiceModeEnabled) { ?>
            <tr>
                <td colspan="<?php echo count($columns); ?>" style="padding-top:20px;padding-bottom:10px;">
                    <?php
                       echo __('Pickup_day') . ': <b> ' . $this->MyTime->getDateFormattedWithWeekday(strtotime($pickupDay)).'</b>';
                    ?>
                    <?php
                        if (!empty($pickupDayEntities)) {
                            foreach($pickupDayEntities as $pickupDayEntity) {
                                if ($pickupDayEntity->comment != ''
                                    && $pickupDayEntity->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database')) == $pickupDay) {
                                    echo '<br />';
                                    echo __('Comment') . ': "<b>' . $pickupDayEntity->comment . '</b>"';
                                    break;
                                }
                            }
                        }
                    ?>
                </td>
            </tr>
        <?php } ?>

        <tr>
            <?php
                foreach ($columns as $column) {
                    echo '<td align="center" style="padding: 10px;font-weight:bold;border:1px solid #d6d4d4;background-color:#fbfbfb;">'.$column.'</td>';
                }
            ?>
    </tr>

    <?php foreach ($cartProducts as $product) { ?>
        <?php
            if ($manufacturerId > 0 && $manufacturerId != $product['manufacturerId']) {
                continue;
            }
        ?>

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
                $unity = '';
                $orderCustomerService = new OrderCustomerService();
                if (isset($product['orderedQuantityInUnits']) &&  $product['orderedQuantityInUnits'] > 0 && $orderCustomerService->isSelfServiceMode()) {
                    $unity = $this->MyNumber->formatUnitAsDecimal($product['orderedQuantityInUnits']) . ' ' . $product['unitName'];
                } else {
                    $unity = $product['unity_with_unit'];
                }
                if ($unity != '') {
                    echo ' : ' . $unity;
                }
                ?>
            </td>

            <?php if (Configure::read('app.showManufacturerListAndDetailPage')) { ?>
                <td valign="middle" style="border:1px solid #d6d4d4;">
                    <?php echo $product['manufacturerName']; ?>
                </td>
            <?php } ?>

            <td valign="middle" align="right" style="border:1px solid #d6d4d4;">
                <?php echo $this->MyNumber->formatAsCurrency($product['price']); ?>
                <?php
                    if (!$selfServiceModeEnabled && $product['unitName'] != '') {
                        echo ' *';
                    }
                ?>
            </td>

            <?php if ($depositSum > 0) { ?>
                <td valign="middle" align="right" style="border:1px solid #d6d4d4;">
                    <?php
                        if ($product['deposit'] > 0) {
                            echo $this->MyNumber->formatAsCurrency($product['deposit']);
                        }
                    ?>
                </td>
            <?php } ?>

        </tr>

    <?php } ?>

    <?php if ($depositSum > 0) { ?>
        <tr>
            <td style="border:1px solid #d6d4d4;" colspan="<?php echo $priceColspan; ?>"></td>
            <td align="right" style="font-weight:bold;border:1px solid #d6d4d4;"><?php echo $this->MyNumber->formatAsCurrency($productSum); ?></td>
            <td align="right" style="font-weight:bold;border:1px solid #d6d4d4;">
                <?php
                if ($depositSum > 0) {
                    echo $this->MyNumber->formatAsCurrency($depositSum);
                }
                ?>
            </td>
        </tr>
    <?php } ?>

    <tr>
        <td style="background-color:#fbfbfb;border:1px solid #d6d4d4;" colspan="<?php echo $beforeTotalPriceColspan; ?>"></td>
        <td align="right" style="font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;"><?php echo __('Total'); ?></td>
        <td align="<?php echo ($depositSum > 0 ? 'center' : 'right'); ?>" style="font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;" colspan="<?php echo ($depositSum > 0 ? 2 : 1); ?>">
            <?php
                echo $this->MyNumber->formatAsCurrency($productAndDepositSum);
            ?>
        </td>
    </tr>

</tbody>

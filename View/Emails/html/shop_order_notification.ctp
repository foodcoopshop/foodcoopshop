<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
    
        <tr>
            <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                Hallo <?php echo $manufacturer['Address']['firstname']; ?>,
            </td>
        </tr>

        <tr>
            <td>
                <p>Soeben wurde eine Sofort-Bestellung für <?php echo $shopOrderCustomerName; ?> getätigt.</p>
            </td>
        </tr>
        
    </tbody>
</table>

<?php echo $this->element('email/tableHead', array('cellpadding' => 6)); ?>
    <?php echo $this->element('email/orderedProductsTable', array(
        'manufacturerId' => $manufacturer['Manufacturer']['id_manufacturer'],
        'cakeCartProducts' => $cart['CakeCartProducts'],
        'depositSum' => $depositSum,
        'productSum' => $productSum,
        'productAndDepositSum' => $productAndDepositSum
    )); ?>
</table>

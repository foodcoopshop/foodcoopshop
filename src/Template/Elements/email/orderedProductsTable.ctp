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

<tbody>
    
        <tr>
            <?php
            $columns = ['Anzahl', 'Produkte', 'Hersteller', 'Preis', 'Pfand'];
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
            <td align="right" style="font-weight:bold;border:1px solid #d6d4d4;border-right:none;"><?php echo $this->MyHtml->formatAsEuro($productSum); ?></td>
            <td align="right" style="font-weight:bold;border:1px solid #d6d4d4;">
                <?php
                if ($depositSum > 0) {
                    echo $this->MyHtml->formatAsEuro($depositSum);
                }
                ?>
            </td>
        </tr>
        
        <tr>
            <td style="background-color:#fbfbfb;border:1px solid #d6d4d4;" colspan="2"></td>
            <td align="right" style="font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;border-right:none;">Gesamt</td>
            <td align="center" style="font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;" colspan="2">
                <?php
                    echo $this->MyHtml->formatAsEuro($productAndDepositSum);
                ?>
            </td>
        </tr>
        
    </tbody>

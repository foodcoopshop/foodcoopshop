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
use Cake\Core\Configure;

?>

<tbody>
    
    <tr>
        <?php
        $columns = [
            __('Amount'),
            __('Product'),
            __('Manufacturer'),
            __('Price'),
            __('Deposit')
        ];
        if (!$this->request->getSession()->check('Auth.instantOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer()) {
            $columns[] = Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME');
        }
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
                if ($product['unity_with_unit'] != '') {
                    echo ' : ' . $product['unity_with_unit'];
                }
                ?>
            </td>
            <td valign="middle" style="border:1px solid #d6d4d4;">
                <?php echo $product['manufacturerName']; ?>
            </td>
            <td valign="middle" align="right" style="border:1px solid #d6d4d4;">
                <?php echo $this->MyNumber->formatAsCurrency($product['price']); ?>
                <?php
                    if ($product['unitName'] != '') {
                        echo ' *';
                    }
                ?>
            </td>
            
            <td valign="middle" align="right" style="border:1px solid #d6d4d4;">
                <?php
                    if ($product['deposit'] > 0) {
                        echo $this->MyNumber->formatAsCurrency($product['deposit']);
                    }
                ?>
            </td>
            
            <?php if (!$this->request->getSession()->check('Auth.instantOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
                <td valign="middle" align="right" style="border:1px solid #d6d4d4;">
                    <?php
                        if (isset($product['timebasedCurrencySeconds'])) {
                            echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($product['timebasedCurrencySeconds']);
                        }
                    ?>
                </td>
            <?php } ?>
            
        </tr>           
        
    <?php } ?>
 
     <tr>
        <td style="border:1px solid #d6d4d4;" colspan="3"></td>
        <td align="right" style="font-weight:bold;border:1px solid #d6d4d4;"><?php echo $this->MyNumber->formatAsCurrency($productSum); ?></td>

        <td align="right" style="font-weight:bold;border:1px solid #d6d4d4;">
            <?php
            if ($depositSum > 0) {
                echo $this->MyNumber->formatAsCurrency($depositSum);
            }
            ?>
        </td>
        
        <?php if (!$this->request->getSession()->check('Auth.instantOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
            <td align="right" style="font-weight:bold;border:1px solid #d6d4d4;">
                <?php
                    echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($appAuth->Cart->getTimebasedCurrencySecondsSum());
                ?>
            </td>
        <?php } ?>
        
    </tr>
    
    <tr>
        <td style="background-color:#fbfbfb;border:1px solid #d6d4d4;" colspan="2"></td>
        <td align="right" style="font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;">Gesamt</td>
        <td align="center" style="font-size:18px;font-weight:bold;background-color:#fbfbfb;border:1px solid #d6d4d4;" colspan="2">
            <?php
                echo $this->MyNumber->formatAsCurrency($productAndDepositSum);
            ?>
        </td>
        <?php if (!$this->request->getSession()->check('Auth.instantOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
            <td style="background-color:#fbfbfb;border:1px solid #d6d4d4;"></td>
        <?php } ?>
    </tr>
    
</tbody>

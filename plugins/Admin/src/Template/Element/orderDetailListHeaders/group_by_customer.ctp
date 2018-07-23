<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetails.product_amount', __d('admin', 'Amount'));
echo '</th>';

echo '<th>';
    echo $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Member'));
echo '</th>';

echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetails.total_price_tax_incl', __d('admin', 'Price'));
echo '</th>';

echo '<th class="right">';
    echo $this->Paginator->sort('OrderDetails.deposit', __d('admin', 'Deposit'));
echo '</th>';

?>
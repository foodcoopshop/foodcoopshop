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

echo $this->Form->control('Manufacturers.address_manufacturer.firstname', [
    'label' => __d('admin', 'Firstname'),
]);
echo $this->Form->control('Manufacturers.address_manufacturer.lastname', [
    'label' => __d('admin', 'Lastname'),
]);
echo $this->Form->control('Manufacturers.address_manufacturer.address1', [
    'label' => __d('admin', 'Street'),
]);
echo $this->Form->control('Manufacturers.address_manufacturer.address2', [
    'label' => __d('admin', 'Additional_address_information'),
]);
echo $this->Form->control('Manufacturers.address_manufacturer.postcode', [
    'label' => __d('admin', 'Zip'),
]);
echo $this->Form->control('Manufacturers.address_manufacturer.city', [
    'label' => __d('admin', 'City'),
]);

?>
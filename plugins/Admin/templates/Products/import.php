<?php
declare(strict_types=1);

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();"
]);

 if (empty($csvRecords)) {
     echo $this->Form->create(null, [
         'type' => 'file',
         'id' => 'csv-upload',
     ]);
     echo $this->Form->control('upload', [
         'type' => 'file',
         'accept' => '.csv',
         'onchange' => 'form.submit()',
         'label' => __d('admin', 'Upload_CSV_file_with_products') . ': ',
     ]);
     echo $this->Form->end();
 }
 
 echo '<button type="submit" class="btn btn-success">
         <i class="fas fa-check"></i> ' . __d('admin', 'Save') . '
     </button>';
 
 echo $this->Form->end();

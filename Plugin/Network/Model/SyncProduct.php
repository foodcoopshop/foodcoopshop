<?php
/**
 * SyncProduct
 *
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
class SyncProduct extends AppModel
{

    public $belongsTo = array(
        'SyncDomain' => array(
            'foreignKey' => 'sync_domain_id'
        )
    );

    public function findAllSyncProducts()
    {
        $syncProducts = $this->find('all', array(
            'conditions' => array(
                'sync_domain_id > 0'
            )
        ));
        return $syncProducts;
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterSaveTrait;

/**
 * fake model for using associations with foreign keys that are not the id of the model
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class UnitProductAttributesTable extends AppTable
{

    use ProductCacheClearAfterSaveTrait;

    public function initialize(array $config): void
    {
        $this->setTable('units');
        parent::initialize($config);
        $this->setPrimaryKey('id_product_attribute');
    }
}

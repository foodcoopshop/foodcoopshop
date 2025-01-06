<?php
declare(strict_types=1);

namespace App\Model\Traits;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Datasource\EntityInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait ProductCacheClearAfterSaveAndDeleteTrait
{

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->clearProductCache();
    }

    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->clearProductCache();
    }

    public function clearProductCache(): void
    {
        $clearCache = true;
        
        if ($this->getRegistryAlias() == 'OrderDetails') {
            $clearCache = false;
            if (Configure::read('app.showOrderedProductsTotalAmountInCatalog')) {
                $clearCache = true;
            }
        }
        
        if ($clearCache) {
            Cache::clearAll();
        }

    }

}
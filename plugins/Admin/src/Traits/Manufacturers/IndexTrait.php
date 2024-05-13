<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers;

use Admin\Traits\Manufacturers\Filter\ManufacturersFilterTrait;
use Cake\Core\Configure;
use App\Services\CatalogService;
use App\Services\DeliveryRhythmService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait IndexTrait
{

    use ManufacturersFilterTrait;

    public function index()
    {

        $active = h($this->getRequest()->getQuery('active', $this->getDefaultActive()));
        $this->set('active', $active);

        $dateFrom = h($this->getRequest()->getQuery('dateFrom', $this->getDefaultDate()));
        $this->set('dateFrom', $dateFrom);

        $dateTo = h($this->getRequest()->getQuery('dateTo', $this->getDefaultDate()));
        $this->set('dateTo', $dateTo);

        $manufacturers = $this->getManufacturers($active, $dateFrom);
        $this->set('manufacturers', $manufacturers);

        $this->set('title_for_layout', __d('admin', 'Manufacturers'));

    }

}
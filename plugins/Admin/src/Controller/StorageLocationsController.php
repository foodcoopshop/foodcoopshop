<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Model\Entity\StorageLocation;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Admin\Traits\UploadTrait;
use App\Services\SanitizeService;
use App\Model\Entity\Slider;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Martin Hatlauf <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class StorageLocationsController extends AdminAppController
{

    use UploadTrait;

    public function add(): void
    {
    }

    public function edit(int $storageLocationID): void
    {
        $slidersTable = $this->getTableLocator()->get('Sliders');
        $slider = $slidersTable->find('all', conditions: [
            'Sliders.id_slider' => $storageLocationID
        ])->first();

        if (empty($slider)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_slider'));
        $this->_processForm($slider, true);
    }

    private function _processForm(StorageLocation $storageLocation, bool $isEditMode): void
    {

    }

    public function index(): void
    {

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $query = $storageLocationsTable->find('all');
        $storageLocations = $this->paginate($query, [
//            'sortableFields' => [
//                'Sliders.position', 'Sliders.active', 'Sliders.link', 'Sliders.is_private'
//            ],
            'order' => [
                'Storage_Locations.position' => 'ASC'
            ]
        ]);
//        dd($storageLocations);
        $this->set('storageLocations', $storageLocations);
        $this->set('title_for_layout', __d('admin', 'Storage locations'));
    }
}

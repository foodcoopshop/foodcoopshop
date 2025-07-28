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
 * @since         FoodCoopShop 4.2.0
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
        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->newEntity(
            [
                'position' => 0,
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('admin', 'Add {0}', [__d('admin', 'Storage_location')]));
        $this->_processForm($storageLocation, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit(int $storageLocationID): void
    {
        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->find('all', conditions: [
            'StorageLocations.id' => $storageLocationID
        ])->first();

        if (empty($storageLocation)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit {0}', [__d('admin', 'Storage_location')]));
        $this->_processForm($storageLocation, true);
    }

    private function _processForm(StorageLocation $storageLocation, bool $isEditMode): void
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        $productsTable = $this->getTableLocator()->get('Products');

        if (empty($this->getRequest()->getData())) {
            $this->set('storageLocation', $storageLocation);

            $productCount = $productsTable->find('all', conditions: [
                'id_storage_location IS' => $storageLocation->id,
                'active IN' => [APP_ON, APP_OFF],
            ])->select(['id_storage_location'])->count();

            $this->set('productCount', $productCount);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->patchEntity($storageLocation, $this->getRequest()->getData());
        if ($storageLocation->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('storageLocation', $storageLocation);
            $this->render('edit');
        } else {
            $storageLocation = $storageLocationsTable->save($storageLocation);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'storage_location_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'storage_location_changed';
            }

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('StorageLocations.delete_storage_location'))) {
                $storageLocationsTable->deleteAll(['id' => $storageLocation->id]);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'storage_location_deleted';
            }

            $message = __d('admin', 'The storage location {0} has been {1}.', ['<b>' . $storageLocation->name . '</b>', $messageSuffix]);
            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $storageLocation->id, 'storage_Locations', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $storageLocation->id);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('storageLocation', $storageLocation);
    }

    public function index(): void
    {
        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $query = $storageLocationsTable->find('all');
        $query->select($storageLocationsTable)
            ->select([
                'product_count' => $query->func()->count('Products.id_product')
            ])
            ->leftJoinWith('Products', function ($q) {
                return $q->where(['Products.active IN' => [APP_ON, APP_OFF]]);
            })
            ->groupBy(['StorageLocations.id']);

        $storageLocations = $this->paginate($query, [
            'sortableFields' => [
                'StorageLocations.position',
                'StorageLocations.name',
                'StorageLocations.product_count',
            ],
            'order' => [
                'StorageLocations.position' => 'ASC'
            ]
        ]);

        $this->set('storageLocations', $storageLocations);
        $this->set('title_for_layout', __d('admin', 'Storage locations'));
    }
}

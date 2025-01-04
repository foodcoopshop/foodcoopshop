<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Admin\Traits\UploadTrait;
use App\Services\SanitizeService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SlidersController extends AdminAppController
{

    use UploadTrait;

    public function add(): void
    {
        $slidersTable = $this->getTableLocator()->get('Sliders');
        $slider = $slidersTable->newEntity(
            [
                'is_private' => Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') ? APP_OFF : APP_ON,
                'active' => APP_ON,
                'position' => 10,
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('admin', 'Add_slider'));
        $this->_processForm($slider, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($sliderId): void
    {
        if ($sliderId === null) {
            throw new NotFoundException;
        }

        $slidersTable = $this->getTableLocator()->get('Sliders');
        $slider = $slidersTable->find('all', conditions: [
            'Sliders.id_slider' => $sliderId
        ])->first();

        if (empty($slider)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_slider'));
        $this->_processForm($slider, true);
    }

    private function _processForm($slider, $isEditMode): void
    {

        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('slider', $slider);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $slidersTable = $this->getTableLocator()->get('Sliders');
        $slider = $slidersTable->patchEntity($slider, $this->getRequest()->getData());
        if ($slider->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('slider', $slider);
            $this->render('edit');
        } else {
            $slider = $slidersTable->save($slider);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'slider_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'slider_changed';
            }

            if (!empty($this->getRequest()->getData('Sliders.tmp_image'))) {
                $filename = $this->saveUploadedImage($slider->id_slider, $this->getRequest()->getData('Sliders.tmp_image'), Configure::read('app.htmlHelper')->getSliderThumbsPath(), Configure::read('app.sliderImageSizes'));
                $slider = $slidersTable->patchEntity($slider, ['image' => $filename]);
                $slidersTable->save($slider);
            }

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('Sliders.delete_slider'))) {
                $this->deleteUploadedImage($slider->id_slider, Configure::read('app.htmlHelper')->getSliderThumbsPath());
                $slider = $slidersTable->patchEntity($slider, ['active' => APP_DEL]);
                $slidersTable->save($slider);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'slider_deleted';
            }
            $message = __d('admin', 'The_slider_{0}_has_been_{1}.', ['<b>' . $slider->id_slider . '</b>', $messageSuffix]);
            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $slider->id_slider, 'sliders', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $slider->id_slider);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('slider', $slider);
    }

    public function index(): void
    {
        $conditions = [
            'Sliders.active > ' . APP_DEL
        ];

        $slidersTable = $this->getTableLocator()->get('Sliders');
        $query = $slidersTable->find('all', conditions: $conditions);
        $sliders = $this->paginate($query, [
            'sortableFields' => [
                'Sliders.position', 'Sliders.active', 'Sliders.link', 'Sliders.is_private'
            ],
            'order' => [
                'Sliders.position' => 'ASC'
            ]
        ]);

        $this->set('sliders', $sliders);
        $this->set('title_for_layout', __d('admin', 'Slideshow'));
    }
}

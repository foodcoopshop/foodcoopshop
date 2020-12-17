<?php

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SlidersController extends AdminAppController
{

    public function add()
    {
        $this->Slider = $this->getTableLocator()->get('Sliders');
        $slider = $this->Slider->newEntity(
            [
                'is_private' => APP_ON,
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

    public function edit($sliderId)
    {
        if ($sliderId === null) {
            throw new NotFoundException;
        }

        $this->Slider = $this->getTableLocator()->get('Sliders');
        $slider = $this->Slider->find('all', [
            'conditions' => [
                'Sliders.id_slider' => $sliderId
            ]
        ])->first();

        if (empty($slider)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_slider'));
        $this->_processForm($slider, true);
    }

    private function _processForm($slider, $isEditMode)
    {

        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('slider', $slider);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $slider = $this->Slider->patchEntity($slider, $this->getRequest()->getData());
        if ($slider->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('slider', $slider);
            $this->render('edit');
        } else {
            $slider = $this->Slider->save($slider);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'slider_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'slider_changed';
            }

            if (!empty($this->getRequest()->getData('Sliders.tmp_image'))) {
                $filename = $this->saveUploadedImage($slider->id_slider, $this->getRequest()->getData('Sliders.tmp_image'), Configure::read('app.htmlHelper')->getSliderThumbsPath(), Configure::read('app.sliderImageSizes'));
                $slider = $this->Slider->patchEntity($slider, ['image' => $filename]);
                $this->Slider->save($slider);
            }

            if (!empty($this->getRequest()->getData('Sliders.delete_image'))) {
                $this->deleteUploadedImage($slider->id_slider, Configure::read('app.htmlHelper')->getSliderThumbsPath(), Configure::read('app.sliderImageSizes'));
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('Sliders.delete_slider'))) {
                $slider = $this->Slider->patchEntity($slider, ['active' => APP_DEL]);
                $this->Slider->save($slider);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'slider_deleted';
            }
            $message = __d('admin', 'The_slider_{0}_has_been_{1}.', ['<b>' . $slider->id_slider . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $slider->id_slider, 'sliders', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $slider->id_slider);
            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('slider', $slider);
    }

    public function index()
    {
        $conditions = [
            'Sliders.active > ' . APP_DEL
        ];

        $this->Slider = $this->getTableLocator()->get('Sliders');
        $query = $this->Slider->find('all', [
            'conditions' => $conditions
        ]);
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

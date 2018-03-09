<?php

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * SlidersController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SlidersController extends AdminAppController
{

    public function add()
    {
        $this->Slider = TableRegistry::get('Sliders');
        $slider = $this->Slider->newEntity(
            [
                'active' => APP_ON,
                'position' => 10
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', 'Slideshow-Bild erstellen');
        $this->_processForm($slider, false);

        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }

    public function edit($sliderId)
    {
        if ($sliderId === null) {
            throw new NotFoundException;
        }

        $this->Slider = TableRegistry::get('Sliders');
        $slider = $this->Slider->find('all', [
            'conditions' => [
                'Sliders.id_slider' => $sliderId
            ]
        ])->first();

        if (empty($slider)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', 'Slideshow-Bild bearbeiten');
        $this->_processForm($slider, true);
    }

    private function _processForm($slider, $isEditMode)
    {

        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->request->getData())) {
            $this->set('slider', $slider);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->request->data = $this->Sanitize->trimRecursive($this->request->getData());
        $this->request->data = $this->Sanitize->stripTagsRecursive($this->request->getData());

        $slider = $this->Slider->patchEntity($slider, $this->request->getData());
        if (!empty($slider->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            $this->set('slider', $slider);
            $this->render('edit');
        } else {
            $slider = $this->Slider->save($slider);

            if (!$isEditMode) {
                $messageSuffix = 'erstellt';
                $actionLogType = 'slider_added';
            } else {
                $messageSuffix = 'geändert';
                $actionLogType = 'slider_changed';
            }

            if (!empty($this->request->getData('Sliders.tmp_image'))) {
                $filename = $this->saveUploadedImage($slider->id_slider, $this->request->getData('Sliders.tmp_image'), Configure::read('app.htmlHelper')->getSliderThumbsPath(), Configure::read('app.sliderImageSizes'));
                $slider = $this->Slider->patchEntity($slider, ['image' => $filename]);
                $this->Slider->save($slider);
            }

            if (!empty($this->request->getData('Sliders.delete_image'))) {
                $this->deleteUploadedImage($slider->id_slider, Configure::read('app.htmlHelper')->getSliderThumbsPath(), Configure::read('app.sliderImageSizes'));
            }

            $this->ActionLog = TableRegistry::get('ActionLogs');
            if (!empty($this->request->getData('Sliders.delete_slider'))) {
                $slider = $this->Slider->patchEntity($slider, ['active' => APP_DEL]);
                $this->Slider->save($slider);
                $messageSuffix = 'gelöscht';
                $actionLogType = 'slider_deleted';
            }
            $message = 'Das Slideshow-Bild <b>' . $slider->id_slider . '</b> wurde ' . $messageSuffix . '.';
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $slider->id_slider, 'sliders', $message);
            $this->Flash->success($message);

            $this->request->getSession()->write('highlightedRowId', $slider->id_slider);
            $this->redirect($this->request->getData('referer'));
        }

        $this->set('slider', $slider);
    }

    public function index()
    {
        $conditions = [
            'Sliders.active > ' . APP_DEL
        ];

        $this->Slider = TableRegistry::get('Sliders');
        $query = $this->Slider->find('all', [
            'conditions' => $conditions
        ]);
        $sliders = $this->paginate($query, [
            'sortWhitelist' => [
                'Sliders.position', 'Sliders.active'
            ],
            'order' => [
                'Sliders.position' => 'ASC'
            ]
        ]);

        $this->set('sliders', $sliders);
        $this->set('title_for_layout', 'Slideshow');
    }
}

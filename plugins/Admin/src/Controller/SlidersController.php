<?php

namespace Admin\Controller;
use Cake\Core\Configure;
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
        $this->edit();
        $this->set('title_for_layout', 'Slideshow-Bild erstellen');
        $this->render('edit');
    }

    public function edit($sliderId = null)
    {
        $this->setFormReferer();

        if ($sliderId > 0) {
            $unsavedSlider = $this->Slider->find('all', [
                'conditions' => [
                    'Sliders.id_slider' => $sliderId
                ]
            ])->first();
            // default value
            $unsavedSlider['Sliders']['update_modified_field'] = APP_ON;
        } else {
            // default values for new sliders
            $unsavedSlider = [
                'Sliders' => [
                    'active' => APP_ON,
                    'position' => 10
                ]
            ];
        }
        $this->set('title_for_layout', 'Slideshow-Bild bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedSlider;
        } else {
            // validate data - do not use $this->Slider->saveAll()
            $this->Slider->id = $sliderId;
            $this->Slider->set($this->request->data['Sliders']);

            $errors = [];
            if (! $this->Slider->validates()) {
                $errors = array_merge($errors, $this->Slider->validationErrors);
            }

            if (empty($errors)) {
                $this->ActionLog = TableRegistry::get('ActionLogs');

                $this->Slider->save($this->request->data['Sliders'], [
                    'validate' => false
                ]);
                if (is_null($sliderId)) {
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'slider_added';
                } else {
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'slider_changed';
                }

                if ($this->request->data['Sliders']['tmp_image'] != '') {
                    $filename = $this->saveUploadedImage($this->Slider->id, $this->request->data['Sliders']['tmp_image'], Configure::read('app.htmlHelper')->getSliderThumbsPath(), Configure::read('app.sliderImageSizes'));
                    $this->Slider->saveField('image', $filename, false);
                }

                if (isset($this->request->data['Slider']['delete_slider']) && $this->request->data['Slider']['delete_slider']) {
                    $this->Slider->saveField('active', APP_DEL, false);
                    $this->deleteUploadedImage($this->Slider->id, Configure::read('htmlHelper')->getSliderThumbsPath(), Configure::read('app.sliderImageSizes'));
                    $message = 'Das Slideshow-Bild ' . $this->Slider->id . ' wurde erfolgreich gelöscht.';
                    $this->ActionLog->customSave('slider_deleted', $this->AppAuth->getUserId(), $this->Slider->id, 'slides', $message);
                    $this->Flash->success('Das Slideshow-Bild wurde erfolgreich gelöscht.');
                } else {
                    $message = 'Das Slideshow-Bild ' . $this->Slider->id . ' wurde ' . $messageSuffix;
                    $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Slider->id, 'slides', $message);
                    $this->Flash->success('Das Slideshow-Bild wurde erfolgreich gespeichert.');
                }
                
                $this->request->getSession()->write('highlightedRowId', $this->Slider->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            }
        }
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

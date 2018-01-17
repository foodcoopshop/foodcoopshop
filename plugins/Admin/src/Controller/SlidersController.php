<?php

use Admin\Controller\AdminAppController;
use Cake\Core\Configure;

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
            $unsavedSlider = $this->Slider->find('first', array(
                'conditions' => array(
                    'Slider.id_slider' => $sliderId
                )
            ));
            // default value
            $unsavedSlider['Slider']['update_modified_field'] = APP_ON;
        } else {
            // default values for new sliders
            $unsavedSlider = array(
                'Slider' => array(
                    'active' => APP_ON,
                    'position' => 10
                )
            );
        }
        $this->set('title_for_layout', 'Slideshow-Bild bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedSlider;
        } else {
            // validate data - do not use $this->Slider->saveAll()
            $this->Slider->id = $sliderId;
            $this->Slider->set($this->request->data['Slider']);

            $errors = array();
            if (! $this->Slider->validates()) {
                $errors = array_merge($errors, $this->Slider->validationErrors);
            }

            if (empty($errors)) {
                $this->ActionLog = TableRegistry::get('ActionLogs');

                $this->Slider->save($this->request->data['Slider'], array(
                    'validate' => false
                ));
                if (is_null($sliderId)) {
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'slider_added';
                } else {
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'slider_changed';
                }

                if ($this->request->data['Slider']['tmp_image'] != '') {
                    $filename = $this->saveUploadedImage($this->Slider->id, $this->request->data['Slider']['tmp_image'], Configure::read('AppConfig.htmlHelper')->getSliderThumbsPath(), Configure::read('AppConfig.sliderImageSizes'));
                    $this->Slider->saveField('image', $filename, false);
                }

                if (isset($this->request->data['Slider']['delete_slider']) && $this->request->data['Slider']['delete_slider']) {
                    $this->Slider->saveField('active', APP_DEL, false);
                    $this->deleteUploadedImage($this->Slider->id, Configure::read('AppConfig.htmlHelper')->getSliderThumbsPath(), Configure::read('AppConfig.sliderImageSizes'));
                    $message = 'Der Slideshow-Bild "' . $this->request->data['Slider']['id_slider'] . '" wurde erfolgreich gelöscht.';
                    $this->ActionLog->customSave('slider_deleted', $this->AppAuth->getUserId(), $this->Slider->id, 'slides', $message);
                    $this->Flash->success('Der Slideshow-Bild wurde erfolgreich gelöscht.');
                } else {
                    $message = 'Der Slideshow-Bild "' . $this->request->data['Slider']['id_slider'] . '" wurde ' . $messageSuffix;
                    $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Slider->id, 'slides', $message);
                    $this->Flash->success('Der Slideshow-Bild wurde erfolgreich gespeichert.');
                }

                $this->request->session()->write('highlightedRowId', $this->Slider->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }

    public function index()
    {
        $conditions = array();
        $conditions[] = 'Slider.active > ' . APP_DEL;

        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'Slider.position' => 'ASC'
            )
        ), $this->Paginator->settings);
        $sliders = $this->Paginator->paginate('Slider');
        $this->set('sliders', $sliders);
        $this->set('title_for_layout', 'Slideshow');
    }
}

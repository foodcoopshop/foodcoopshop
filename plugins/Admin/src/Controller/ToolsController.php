<?php

use Admin\Controller\AdminAppController;
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

/**
 * ToolsController
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
use Intervention\Image\ImageManagerStatic as Image;

class ToolsController extends AdminAppController
{

    public function doTmpImageUpload()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        // check if uploaded file is image file
        $formatInfo = getimagesize($this->params['form']['upload']['tmp_name']);
        // non-image files will return false
        if ($formatInfo === false || $formatInfo['mime'] != 'image/jpeg') {
            $message = 'Die hochgeladene Datei muss im Format "jpg" sein.';
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        $extension = strtolower(pathinfo($this->params['form']['upload']['name'], PATHINFO_EXTENSION));
        if ($extension == 'jpeg') {
            $extension = 'jpg';
        }
        $filename = StringComponent::createRandomString(10) . '.' . $extension;
        $filenameWithPath = Configure::read('AppConfig.tmpUploadImagesDir') . DS . $filename;
        Image::make($this->params['form']['upload']['tmp_name'])
            ->widen($this->getMaxTmpUploadFileSize())
            ->save(WWW_ROOT . $filenameWithPath);

        die(json_encode(array(
            'status' => 1,
            'filename' => $filenameWithPath
        )));
    }

    public function rotateImage()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        // check if uploaded file is image file
        $uploadedFile = $_SERVER['DOCUMENT_ROOT'] . $this->params['data']['filename'];

        $direction = $this->params['data']['direction'];

        $directionInDegrees = null;
        if ($direction == 'CW') {
            $directionInDegrees = 90;
        }
        if ($direction == 'ACW') {
            $directionInDegrees = -90;
        }
        if (is_null($directionInDegrees)) {
            $message = 'direction wrong';
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        $formatInfo = getimagesize($uploadedFile);

        // non-image files will return false
        if ($formatInfo === false || $formatInfo['mime'] != 'image/jpeg') {
            $message = 'Die hochgeladene Datei muss im Format "jpg" sein.';
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        Image::make($uploadedFile)
        ->rotate($directionInDegrees)
            ->save($uploadedFile);

        $rotatedImageSrc = $this->params['data']['filename'] . '?' . StringComponent::createRandomString(3);
        die(json_encode(array(
            'status' => 1,
            'rotatedImageSrc' => $rotatedImageSrc
        )));
    }

    public function ajaxCancelFormPage()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $referer = $this->params['data']['referer'];
        if ($referer == '') {
            $referer = '/';
        }

        $objectClass = $this->params['data']['objectClass'];
        $id = $this->params['data']['id'];

        $this->loadModel($objectClass);

        $object = $this->$objectClass->find('first', array(
            'conditions' => array(
                $objectClass . '.' . $this->$objectClass->primaryKey => $id
            )
        ));

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok',
            'referer' => $referer
        )));
    }

    /*
     * On uploading images are resized to fit to the maximum possibly required ...ImageSizes from app.config.php
     * return int
     */
    protected function getMaxTmpUploadFileSize()
    {

        $actTmpUploadFileSize = 0;
        $confKey = 'ImageSizes';

        // get all config keys "*ImageSizes"
        $imageSizes = Configure::read('app');
        foreach (array_keys($imageSizes) as $appKey) {
            if (strlen($appKey) < strlen($confKey) // prevent warnings of strrpos()
                || strrpos($appKey, $confKey, strlen($confKey) * (-1)) === false
                || !is_array($imageSizes[$appKey])
            ) {
                continue;
            }

            foreach ($imageSizes[$appKey] as $key => $value) {
                if ($key <= $actTmpUploadFileSize) {
                    continue;
                }
                $actTmpUploadFileSize = $key;
            }
        }

        return $actTmpUploadFileSize;
    }
}

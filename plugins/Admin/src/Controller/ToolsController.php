<?php

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Filesystem\File;

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
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Intervention\Image\ImageManagerStatic as Image;

class ToolsController extends AdminAppController
{

    public function doTmpFileUpload()
    {
        $this->RequestHandler->renderAs($this, 'json');
        
        // check if uploaded file is pdf
        $upload = $this->getRequest()->getData('upload');
        
        // non-pdf files will return false
        if ($upload->getClientMediaType() != 'application/pdf') {
            $message = 'only pdf format is allowed';
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }
        
        $extension = strtolower(pathinfo($upload->getClientFilename(), PATHINFO_EXTENSION));
        $filename = StringComponent::createRandomString(10) . '.' . $extension;
        $filenameWithPath = Configure::read('app.tmpUploadFilesDir') . DS . $filename;
        
        $upload->moveTo(WWW_ROOT . $filenameWithPath);
        
        $this->set([
            'status' => 1,
            'text' => __d('admin', 'Filename_General-terms-and-conditions') . '.pdf',
            'filename' => $filenameWithPath,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'text', 'filename']);
    }
    
    public function doTmpImageUpload()
    {
        $this->RequestHandler->renderAs($this, 'json');

        // check if uploaded file is image file
        $upload = $this->getRequest()->getData('upload');
        
        // non-image files will return false
        if ($upload->getClientMediaType() != 'image/jpeg') {
            $message = 'the uploaded file needs to have jpg format.';
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $extension = strtolower(pathinfo($upload->getClientFilename(), PATHINFO_EXTENSION));
        if ($extension == 'jpeg') {
            $extension = 'jpg';
        }
        $filename = StringComponent::createRandomString(10) . '.' . $extension;
        $filenameWithPath = Configure::read('app.tmpUploadImagesDir') . DS . $filename;
        $upload->moveTo(WWW_ROOT . $filenameWithPath);
        
        Image::make(WWW_ROOT . $filenameWithPath) 
            ->widen($this->getMaxTmpUploadFileSize())
            ->save(WWW_ROOT . $filenameWithPath);

        $this->set([
            'status' => 1,
            'filename' => $filenameWithPath,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'filename']);
    }

    public function rotateImage()
    {
        $this->RequestHandler->renderAs($this, 'json');

        // check if uploaded file is image file
        $uploadedFile = $_SERVER['DOCUMENT_ROOT'] . $this->getRequest()->getData('filename');

        $direction = $this->getRequest()->getData('direction');

        $directionInDegrees = null;
        if ($direction == 'CW') {
            $directionInDegrees = 90;
        }
        if ($direction == 'ACW') {
            $directionInDegrees = -90;
        }
        if (is_null($directionInDegrees)) {
            $message = 'direction wrong';
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $formatInfo = getimagesize($uploadedFile);

        // non-image files will return false
        if ($formatInfo === false || $formatInfo['mime'] != 'image/jpeg') {
            $message = 'the uploaded file needs to have jpg format.';
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        Image::make($uploadedFile)
        ->rotate($directionInDegrees)
            ->save($uploadedFile);

        $rotatedImageSrc = $this->getRequest()->getData('filename') . '?' . StringComponent::createRandomString(3);
        
        $this->set([
            'status' => 1,
            'rotatedImageSrc' => $rotatedImageSrc
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'rotatedImageSrc']);
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

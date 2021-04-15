<?php

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

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
use Intervention\Image\ImageManagerStatic as Image;

class ToolsController extends AdminAppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->loadComponent('FormProtection');
        $this->FormProtection->setConfig('validate', false);
    }

    public function doTmpFileUpload()
    {
        $this->RequestHandler->renderAs($this, 'json');

        // check if uploaded file is pdf
        $upload = $this->getRequest()->getData('upload');

        // non-pdf files will return false
        if (mime_content_type($upload->getStream()->getMetadata('uri')) != 'application/pdf') {
            $message = __d('admin', 'The_uploaded_file_needs_to_have_the_format:_{0}', ['PDF']);
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
        if (mime_content_type($upload->getStream()->getMetadata('uri')) != 'image/jpeg') {
            $message = __d('admin', 'The_uploaded_file_needs_to_have_the_format:_{0}', ['JPG']);
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
            ->widen($this->getMaxTmpUploadFileSize(), function ($constraint) {
                // prevent upsizing
                $constraint->upsize();
            })
            ->save(WWW_ROOT . $filenameWithPath, 100);

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
            $message = __d('admin', 'The_uploaded_file_needs_to_have_the_format:_{0}', ['JPG']);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        Image::make($uploadedFile)
            ->rotate($directionInDegrees)
            ->save($uploadedFile, 100);

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

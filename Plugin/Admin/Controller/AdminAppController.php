<?php

App::uses('AppController', 'Controller');

/**
 * AdminAppController
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
class AdminAppController extends AppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->loggedIn();
    }

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel(Inflector::singularize($this->name)); // force cake to load corresponding model in main app folder
    }

    public function setFormReferer()
    {
        $this->set('referer', isset($this->request->data['referer']) ? $this->request->data['referer'] : $this->referer());
    }

    /**
     * deletes physical files (thumbs)
     */
    protected function deleteUploadedImage($imageId, $thumbsPath, $imageSizes)
    {

        // delete physical files
        foreach ($imageSizes as $thumbSize => $options) {
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.jpg';
            if (file_exists($thumbsFileName)) {
                unlink($thumbsFileName);
            }
        }
    }

    /**
     *
     * @param int $imageId
     * @param string $filename
     * @param string $thumbsPath
     * @param array $imageSizes
     * @return string
     */
    protected function saveUploadedImage($imageId, $filename, $thumbsPath, $imageSizes)
    {

        // if image was rotatet, cut off ?xyz (random string)
        $explodedFilename = explode('?', $filename);
        if (count($explodedFilename) == 2) {
            $filename = $explodedFilename[0];
        }
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        foreach ($imageSizes as $thumbSize => $options) {
            $thumb = PhpThumbFactory::create(WWW_ROOT . $filename);
            $dimensions = $thumb->getCurrentDimensions();
            // make portrait images smaller
            if ($dimensions['height'] > $dimensions['width']) {
                $thumbSize = round($thumbSize * ($dimensions['width'] / $dimensions['height']), 0);
            }
            $thumb->resize($thumbSize);
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . $extension;
            $thumb->save($thumbsFileName);
        }

        return $imageId . $options['suffix'] . '.' . $extension;
    }
}

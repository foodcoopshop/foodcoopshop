<?php

namespace Admin\Controller;

use App\Controller\AppController;
use Intervention\Image\ImageManagerStatic as Image;

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class AdminAppController extends AppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->user();
    }

    public function setReferer()
    {
        $this->set('referer', ! empty($this->getRequest()->getData('referer')) ? $this->getRequest()->getData('referer') : $this->referer());
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
            $image = Image::make(WWW_ROOT . $filename);
            // make portrait images smaller
            if ($image->getHeight() > $image->getWidth()) {
                $thumbSize = round($thumbSize * ($image->getWidth() / $image->getHeight()), 0);
            }
            $image->widen($thumbSize);
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . $extension;
            $image->save($thumbsFileName);
        }

        return $imageId . $options['suffix'] . '.' . $extension;
    }
}

<?php

namespace Admin\Controller;

use App\Controller\AppController;
use Intervention\Image\ImageManagerStatic as Image;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

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
    protected function deleteUploadedImage($imageId, $thumbsPath)
    {
        $dir = new Folder($thumbsPath);
        $files = $dir->read();
        if (!empty($files[1])) {
            foreach($files[1] as $file) {
                if (preg_match('/^' . $imageId . '-/', $file)) {
                    $file = new File($thumbsPath . DS . $file);
                    $file->delete();
                }
            }
        }
    }

    /**
     *
     * @param int $imageId
     * @param string $filename
     * @param string $thumbsPath
     * @param array $imageSizes
     */
    protected function saveUploadedImage($imageId, $filename, $thumbsPath, $imageSizes)
    {

        $this->deleteUploadedImage($imageId, $thumbsPath);

        // if image was rotated, cut off ?xyz (random string)
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
            $image->save($thumbsFileName, 100);
        }

        return $imageId . $options['suffix'] . '.' . $extension;
    }
}

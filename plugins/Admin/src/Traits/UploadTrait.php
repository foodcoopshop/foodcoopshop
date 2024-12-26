<?php
declare(strict_types=1);

namespace Admin\Traits;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait UploadTrait
{

    protected function deleteUploadedImage(int $imageId, string $thumbsPath): void
    {
        $dir = new \DirectoryIterator($thumbsPath);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $filename = $fileinfo->getFilename();
                if (preg_match('/^' . $imageId . '-/', $filename)) {
                    unlink($thumbsPath . DS . $filename);
                }
            }
        }
    }

    protected function saveUploadedImage(int $imageId, string $filename, string $thumbsPath, array $imageSizes): string|bool
    {

        $this->deleteUploadedImage($imageId, $thumbsPath);

        // if image was rotated, cut off ?xyz (random string)
        $explodedFilename = explode('?', $filename);
        if (count($explodedFilename) == 2) {
            $filename = $explodedFilename[0];
        }
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $manager = new ImageManager(new Driver());

        foreach ($imageSizes as $thumbSize => $options) {
            $image = $manager->read(WWW_ROOT . $filename);
            // make portrait images smaller
            if ($image->height() > $image->width()) {
                $thumbSize = (int) round($thumbSize * ($image->width() / $image->height()), 0);
            }
            $image->scale($thumbSize);
            $thumbsFileName = $thumbsPath . DS . $imageId . $options['suffix'] . '.' . $extension;
            $image
                ->encodeByMediaType(quality: 100)
                ->save($thumbsFileName);
        }

        if (isset($options)) {
            return $imageId . $options['suffix'] . '.' . $extension;
        }

        return false;

    }

}


<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;
use App\Services\FolderService;
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

trait SaveUploadedImageTrait {

    public function saveUploadedImageProduct()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productId = $this->getRequest()->getData('objectId');
        $filename = $this->getRequest()->getData('filename');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $product = $this->Product->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'Images',
                'Manufacturers'
            ]
        )->first();

        if (empty($product->image)) {
            // product does not yet have image => create the necessary record
            $image = $this->Product->Images->save(
                $this->Product->Images->newEntity(
                    ['id_product' => $productId]
                )
            );
        } else {
            $image = $product->image;
            // cache needs to be cleared manually because neither image nor product record is changed
            $this->Product->clearProductCache();
        }

        // not (yet) implemented for attributes, only for productIds!
        $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($image->id_image);
        $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);

        FolderService::nonRecursivelyRemoveAllFiles($thumbsPath);
        if (!file_exists($thumbsPath)) {
            mkdir($thumbsPath, 0755, true);
        }

        $manager = new ImageManager(new Driver());
        foreach (Configure::read('app.productImageSizes') as $thumbSize => $options) {

            $physicalImage = $manager->read(WWW_ROOT . $filename);
            // make portrait images smaller
            if ($physicalImage->height() > $physicalImage->width()) {
                $thumbSize = (int) round($thumbSize * ($physicalImage->width() / $physicalImage->height()), 0);
            }
            $physicalImage->scale($thumbSize);
            $thumbsFileName = $thumbsPath . DS . $image->id_image . $options['suffix'] . '.' . $extension;
            $physicalImage
                ->encodeByMediaType(quality: 100)
                ->save($thumbsFileName);
        }

        $actionLogMessage = __d('admin', 'A_new_image_was_uploaded_to_product_{0}_from_manufacturer_{1}.', [
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>'
        ]);
        $this->Flash->success($actionLogMessage);
        $this->ActionLog->customSave('product_image_added', $this->identity->getId(), $productId, 'products', $actionLogMessage);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'success',
            'imageId' => $image->id_image,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'imageId']);
    }

}

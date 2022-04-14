<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Shell;

use Cake\Core\Configure;

/**
 * Due to a not yet found bug some Products have an association record to the Image entity, but
 * the physical image is not available
 * This script lists those products
 */
class DetectMissingProductImagesShell extends AppShell
{
    public function main()
    {

        $this->Product = $this->getTableLocator()->get('Products');

        $products = $this->Product->find('all', [
            'conditions' => [
                'Products.active' => APP_ON,
            ],
            'contain' => [
                'Images',
            ],
            'order' => [
                'Products.modified' => 'DESC',
                'Images.id_image' => 'ASC',
            ],
        ]);

        $i = 0;
        foreach($products as $product) {
            if (!empty($product->image)) {
                $imageIdAsPath = Configure::read('app.htmlHelper')->getProductImageIdAsPath($product->image->id_image);
                $thumbsPath = Configure::read('app.htmlHelper')->getProductThumbsPath($imageIdAsPath);
                $size = 'home';
                $imageFilename = Configure::read('app.htmlHelper')->getImageFile($thumbsPath, $product->image->id_image . '-' . $size . '_default');
                $src = $thumbsPath . DS . $imageFilename;

                if (!file_exists($src)) {
                    $i++;
                    $this->out('Product modified: ' . $product->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseWithTime')) . ' / Product id: ' . $product->id_product);
                }
            }
        }

        $this->out('Sum: ' . $i);

    }

}

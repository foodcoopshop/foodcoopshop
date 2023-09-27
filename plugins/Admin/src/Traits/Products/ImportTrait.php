<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Lib\Csv\ProductReader;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait ImportTrait {

    public function import()
    {

        $this->set('title_for_layout', __d('admin', 'Product_import'));

        if (!empty($this->getRequest()->getData('upload'))) {

            $upload = $this->getRequest()->getData('upload');
            $content = $upload->getStream()->getContents();
            $reader = ProductReader::createFromString($content);

            try {
                $productEntities = $reader->import();
                $this->Flash->success(__d('admin', 'Product_import_successful.' . count($productEntities)));
            } catch(\Exception $e) {
                $this->Flash->error(__d('admin', 'The_uploaded_file_is_not_valid.'));
                $this->redirect($this->referer());
            }

        }

    }

}

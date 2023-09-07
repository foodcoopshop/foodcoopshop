<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;

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

trait EditCategoriesTrait {

    public function editCategories()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $productId = (int) $this->getRequest()->getData('productId');
        $selectedCategories = [];
        if (!empty($this->getRequest()->getData('selectedCategories'))) {
            $selectedCategories = $this->getRequest()->getData('selectedCategories');
        }

        $selectedCategories[] = Configure::read('app.categoryAllProducts'); // always add 'all-products'
        $selectedCategories = array_unique($selectedCategories);

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        $this->CategoryProduct = $this->getTableLocator()->get('CategoryProducts');
        $this->CategoryProduct->deleteAll([
            'id_product' => $productId,
        ]);

        $this->Category = $this->getTableLocator()->get('Categories');
        $selectedCategoryNames = [];
        $data = [];
        foreach ($selectedCategories as $selectedCategoryId) {
            // only add if entry of passed id exists in category table
            $oldCategory = $this->Category->find('all', [
                'conditions' => [
                    'Categories.id_category' => $selectedCategoryId
                ]
            ])->first();
            if (! empty($oldCategory)) {
                // do not track "all-products"
                if ($selectedCategoryId != Configure::read('app.categoryAllProducts')) {
                    $selectedCategoryNames[] = $oldCategory->name;
                }
                $data[] = [
                    'id_product' => $productId,
                    'id_category' => $selectedCategoryId,
                ];
            }
        }
        if (!empty($data)) {
            $tmpPrimaryKey = $this->CategoryProduct->getPrimaryKey();
            $this->CategoryProduct->setPrimaryKey(null);
            $categoryProducts = $this->CategoryProduct->newEntities($data);
            $this->CategoryProduct->saveMany($categoryProducts);
            $this->CategoryProduct->setPrimaryKey($tmpPrimaryKey);
        }

        $messageString = __d('admin', 'The_categories_of_the_product_{0}_from_manufacturer_{1}_have_been_changed:_{2}', ['<b>' . $oldProduct->name . '</b>', '<b>' . $oldProduct->manufacturer->name . '</b>', join(', ', $selectedCategoryNames)]);
        $this->Flash->success($messageString);
        $this->ActionLog->customSave('product_categories_changed', $this->AppAuth->getUserId(), $productId, 'products', $messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => __d('admin', 'Saving_successful.'),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}

<?php
declare(strict_types=1);

namespace Admin\Traits\Products;


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

trait GetProductsForDropdownTrait 
{

    public function ajaxGetProductsForDropdown(int $manufacturerId = 0): void
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productsTable = $this->getTableLocator()->get('Products');
        $products = $productsTable->getForDropdown($manufacturerId);
        $productsForDropdown = [];
        foreach ($products as $key => $ps) {
            $options = [];
            foreach ($ps as $pId => $p) {
                $options[] = [
                    'value' => (string) $pId,
                    'text' => $p,
                ];
            }
            $productsForDropdown[] = [
                'label' => $key,
                'options' => $options,
            ];
        }

        array_unshift($productsForDropdown, [
            'value' => '',
            'text' => __('All_products'),
        ]);

        $this->set([
            'status' => 1,
            'dropdownData' => $productsForDropdown,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'dropdownData']);
    }

}

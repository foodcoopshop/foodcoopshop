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

    public function ajaxGetProductsForDropdown($manufacturerId = 0)
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $products = $this->Product->getForDropdown($manufacturerId);
        $productsForDropdown = [];
        foreach ($products as $key => $ps) {
            $productsForDropdown[] = '<optgroup label="' . $key . '">';
            foreach ($ps as $pId => $p) {
                $productsForDropdown[] = '<option value="' . $pId . '">' . $p . '</option>';
            }
            $productsForDropdown[] = '</optgroup>';
        }

        $emptyElement = ['<option value="">' . __d('admin', 'All_products') . '</option>'];
        $productsForDropdown = array_merge($emptyElement, $productsForDropdown);

        $this->set([
            'status' => 1,
            'dropdownData' => join('', $productsForDropdown),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'dropdownData']);
    }

}

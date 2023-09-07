<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Lib\PdfWriter\ProductCardsPdfWriter;
use App\Lib\Error\Exception\InvalidParameterException;
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

trait GenerateProductCardsTrait {

    public function generateProductCards()
    {
        $productIds = h($this->getRequest()->getQuery('productIds'));
        $productIds = explode(',', $productIds);

        if (empty($productIds)) {
            throw new InvalidParameterException('no product id passed');
        }

        $this->Product = $this->getTableLocator()->get('Products');
        $products = $this->Product->getProductsForBackend($this->AppAuth, $productIds, 'all', 'all', '', false, false, true);

        $preparedProducts = [];
        foreach($products as &$product) {

            if (Configure::read('app.selfServiceModeShowOnlyStockProducts') && !($product->manufacturer->stock_management_enabled && $product->is_stock_product)) {
                continue;
            }

            if (!empty($product->product_attributes)) {
                // avoid rendering main product if product has attributes
                continue;
            }
            $price = Configure::read('app.numberHelper')->formatAsCurrency($product->gross_price);
            if (!empty($product->unit) && $product->unit->price_per_unit_enabled) {
                $price = Configure::read('app.pricePerUnitHelper')->getPricePerUnitBaseInfo($product->unit->price_incl_per_unit, $product->unit->name, $product->unit->amount);
                if (!preg_match('/main-product/', $product->row_class)) {
                    $product->name = $product->nameForBarcodePdf;
               }
            }
            if (preg_match('/main-product/', $product->row_class)) {
                $product->system_bar_code .= '0000';
            }
            $product->prepared_price = $price;
            $preparedProducts[] = $product;
        }

        if (empty($preparedProducts)) {
            throw new InvalidParameterException('no stock product selected');
        }
        $pdfWriter = new ProductCardsPdfWriter();
        $pdfWriter->setFilename(__d('admin', 'Products').'.pdf');
        $pdfWriter->setData([
            'products' => $preparedProducts
        ]);
        die($pdfWriter->writeInline());
    }

}

<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Csv;

use Cake\Datasource\FactoryLocator;
use App\Services\DomDocumentService;

class ProductCsvWriterService extends BaseCsvWriterService
{

    private $productIds;

    public function setProductIds($productIds)
    {
        $this->productIds = $productIds;
    }

    public function getHeader()
    {
        return [
            __('Id'),
            __('Product'),
            __('Manufacturer'),
            __('Unit'),
        ];
    }

    public function getRecords()
    {
        $productsTable =FactoryLocator::get('Table')->get('Products');
        $products = $productsTable->getProductsForBackend(
            productIds: $this->productIds,
            manufacturerId: 'all',
            active: 'all',
            addProductNameToAttributes: true,
        );

        $records = [];
        foreach ($products as $product) {

            $domDocumentService = new DomDocumentService();
            $domDocumentService->loadHTML($product->name);
            $productName = $domDocumentService->getItemByClass('product-name')->item(0)?->nodeValue;
            $quantityInUnits = $domDocumentService->getItemByClass('quantity-in-units')->item(0)?->nodeValue;

            $records[] = [
                $product->id_product,
                $productName,
                $product->manufacturer->name,
                $quantityInUnits,
            ];
        }

        return $records;
    }

}
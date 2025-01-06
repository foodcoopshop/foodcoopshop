<?php

declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Services\Csv\Writer\ProductCsvWriterService;
use Cake\Http\Response;

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

trait ExportTrait
{

    public function export(): Response
    {

        $productIds = h($this->getRequest()->getData('productIds', ''));
        if ($productIds == '') {
            throw new \Exception('no product ids passed');
        }

        $productIds = explode(',', $productIds);

        $writerService = new ProductCsvWriterService();
        $writerService->setProductIds($productIds);
        $writerService->setFilename(__('Products') . '_' . date('YmdHis') . '.csv');
        $writerService->render();
        return $writerService->forceDownload($this->getResponse());

    }

}

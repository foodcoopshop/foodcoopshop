<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use App\Services\Csv\Writer\CustomerCsvWriterService;

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

trait ExportTrait {

    public function export()
    {

        $writerService = new CustomerCsvWriterService();
        $writerService->setRequestQueryParams($this->getRequest()->getQueryParams());
        $writerService->setFilename(__d('admin', 'Members') . '_' . date('YmdHis') . '.csv');
        $writerService->render();
        return $writerService->forceDownload($this->getResponse());

    }

}
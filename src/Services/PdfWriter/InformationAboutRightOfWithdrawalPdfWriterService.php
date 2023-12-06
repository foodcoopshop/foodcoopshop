<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\PdfWriter;

use App\Services\Pdf\ListTcpdfService;
use App\Services\PdfWriter\PdfWriterService;

class InformationAboutRightOfWithdrawalPdfWriterService extends PdfWriterService
{

    public function __construct()
    {
        $this->setFilename(__('Filename_Information-about-right-of-withdrawal').'.pdf');
        $this->setPdfLibrary(new ListTcpdfService());
    }

}


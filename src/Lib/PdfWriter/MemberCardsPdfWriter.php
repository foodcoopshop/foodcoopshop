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
namespace App\Lib\PdfWriter;

use App\Lib\Pdf\BarCodeTcpdf;
use App\Lib\PdfWriter\Traits\MemberCardDataTrait;

class MemberCardsPdfWriter extends PdfWriter
{

    use MemberCardDataTrait;

    public function __construct()
    {
        $this->plugin = 'Admin';
        $this->setPdfLibrary(new BarCodeTcpdf());
    }

}


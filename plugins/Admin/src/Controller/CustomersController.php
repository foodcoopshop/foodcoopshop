<?php
declare(strict_types=1);

namespace Admin\Controller;

use Admin\Traits\Customers\ChangePasswordTrait;
use Admin\Traits\Customers\ChangeStatusTrait;
use Admin\Traits\Customers\CreditBalanceSumTrait;
use Admin\Traits\Customers\DeleteTrait;
use Admin\Traits\Customers\EditCommentTrait;
use Admin\Traits\Customers\EditGroupTrait;
use Admin\Traits\Customers\EditTrait;
use Admin\Traits\Customers\ExportTrait;
use Admin\Traits\Customers\GenerateMemberCardsTrait;
use Admin\Traits\Customers\GetCreditBalanceTrait;
use Admin\Traits\Customers\GetCustomersForDropdownTrait;
use Admin\Traits\Customers\IndexTrait;
use App\Services\PdfWriter\TermsOfUsePdfWriterService;
use Admin\Traits\UploadTrait;
use App\Controller\Traits\RenewAuthSessionTrait;
use Cake\View\JsonView;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class CustomersController extends AdminAppController
{

    use ChangePasswordTrait;
    use ChangeStatusTrait;
    use CreditBalanceSumTrait;
    use DeleteTrait;
    use EditTrait;
    use EditCommentTrait;
    use ExportTrait;
    use GenerateMemberCardsTrait;
    use GetCreditBalanceTrait;
    use GetCustomersForDropdownTrait;
    use EditGroupTrait;
    use IndexTrait;
    use UploadTrait;
    use RenewAuthSessionTrait;
    
    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    private function generateTermsOfUsePdf(): string
    {
        $pdfWriter = new TermsOfUsePdfWriterService();
        return $pdfWriter->writeAttachment();
    }

}

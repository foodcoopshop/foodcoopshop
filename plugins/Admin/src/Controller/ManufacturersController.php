<?php
declare(strict_types=1);

namespace Admin\Controller;

use Admin\Traits\Manufacturers\EditOptionsTrait;
use Admin\Traits\Manufacturers\GetInvoiceTrait;
use Admin\Traits\Manufacturers\GetOrderListTrait;
use Admin\Traits\Manufacturers\IndexTrait;
use Cake\Event\EventInterface;
use Admin\Traits\UploadTrait;
use App\Controller\Traits\RenewAuthSessionTrait;
use App\Model\Table\FeedbacksTable;
use App\Model\Table\OrderDetailsTable;
use App\Model\Table\PaymentsTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\TaxesTable;
use Cake\View\JsonView;
use Network\Model\Table\SyncDomainsTable;
use Admin\Traits\Manufacturers\AddEditTrait;
use Admin\Traits\Manufacturers\SetElFinderUploadPathTrait;
use Admin\Traits\Manufacturers\GetDeliveryNoteTrait;
use Admin\Traits\Manufacturers\ExportTrait;

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

class ManufacturersController extends AdminAppController
{
    protected FeedbacksTable $Feedback;
    protected ProductsTable $Product;
    protected TaxesTable $Tax;
    protected SyncDomainsTable $SyncDomain;

    use GetOrderListTrait;
    use AddEditTrait;
    use EditOptionsTrait;
    use ExportTrait;
    use IndexTrait;
    use GetDeliveryNoteTrait;
    use GetInvoiceTrait;
    use SetElFinderUploadPathTrait;
    use RenewAuthSessionTrait;
    use UploadTrait;

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

}
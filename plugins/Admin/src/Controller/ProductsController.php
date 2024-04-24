<?php
declare(strict_types=1);

namespace Admin\Controller;

use Admin\Traits\Products\AddProductAttributeTrait;
use Admin\Traits\Products\AddTrait;
use Admin\Traits\Products\CalculateSellingChargeWithSurchargeTrait;
use Admin\Traits\Products\DeleteImageTrait;
use Admin\Traits\Products\DeleteTrait;
use Admin\Traits\Products\EditCategoriesTrait;
use Admin\Traits\Products\EditDefaultAttributeTrait;
use Admin\Traits\Products\EditDeliveryRhythmTrait;
use Admin\Traits\Products\EditDepositTrait;
use Admin\Traits\Products\EditIsStockProductTrait;
use Admin\Traits\Products\EditNameTrait;
use Admin\Traits\Products\EditNewStatusTrait;
use Admin\Traits\Products\EditPriceTrait;
use Admin\Traits\Products\EditProductAttributeTrait;
use Admin\Traits\Products\EditPurchasePriceTrait;
use Admin\Traits\Products\EditQuantityTrait;
use Admin\Traits\Products\EditStatusTrait;
use Admin\Traits\Products\EditTaxTrait;
use Admin\Traits\Products\ExportTrait;
use Admin\Traits\Products\GenerateProductCardsTrait;
use Admin\Traits\Products\GetProductsForDropdownTrait;
use Admin\Traits\Products\ImportTrait;
use Admin\Traits\Products\IndexTrait;
use Admin\Traits\Products\SaveUploadedImageTrait;
use App\Model\Table\ProductsTable;
use Cake\Event\EventInterface;
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
class ProductsController extends AdminAppController
{

    use AddTrait;
    use AddProductAttributeTrait;
    use CalculateSellingChargeWithSurchargeTrait;
    use DeleteTrait;
    use DeleteImageTrait;
    use EditCategoriesTrait;
    use EditDefaultAttributeTrait;
    use EditDeliveryRhythmTrait;
    use EditDepositTrait;
    use EditNameTrait;
    use EditNewStatusTrait;
    use EditIsStockProductTrait;
    use EditPriceTrait;
    use EditProductAttributeTrait;
    use EditPurchasePriceTrait;
    use EditQuantityTrait;
    use EditStatusTrait;
    use EditTaxTrait;
    use GenerateProductCardsTrait;
    use GetProductsForDropdownTrait;
    use IndexTrait;
    use ImportTrait;
    use ExportTrait;
    use SaveUploadedImageTrait;

    protected ProductsTable $Product;

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }
    
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->Product = $this->getTableLocator()->get('Products');
    }

}

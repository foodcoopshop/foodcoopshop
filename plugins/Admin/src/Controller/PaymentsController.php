<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\View\JsonView;
use Admin\Traits\Payments\AddTrait;
use Admin\Traits\Payments\EditTrait;
use Admin\Traits\Payments\ChangeStatusTrait;
use Admin\Traits\Payments\OverviewTrait;
use Admin\Traits\Payments\PreviewEmailTrait;
use Admin\Traits\Payments\ProductTrait;

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
class PaymentsController extends AdminAppController
{
    use AddTrait;
    use EditTrait;
    use ChangeStatusTrait;
    use PreviewEmailTrait;
    use ProductTrait;
    use OverviewTrait;

    protected string $paymentType;
    public int|string $customerId;

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

}

<?php
declare(strict_types=1);

namespace Admin\Traits\Payments;

use App\Model\Entity\Payment;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait OverviewTrait
{

    public function overview(): void
    {
        $this->customerId = $this->identity->getId();
        $this->paymentType = Payment::TYPE_PRODUCT;

        if (!Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
            $customersTable = $this->getTableLocator()->get('Customers');
            $personalTransactionCode = $customersTable->getPersonalTransactionCode($this->customerId);
            $this->set('personalTransactionCode', $personalTransactionCode);
        }

        $this->product();
        $this->render('product');
    }

}
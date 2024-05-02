<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Core\Configure;
use Admin\Traits\Customers\Filter\CustomersFilterTrait;
use Admin\Traits\QueryFilterTrait;

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

trait IndexTrait {

    use QueryFilterTrait;
    use CustomersFilterTrait;

    public function index()
    {
        $active = h($this->getRequest()->getQuery('active', APP_ON));
        $this->set('active', $active);

        $year = h($this->getRequest()->getQuery('year', date('Y')));
        $this->set('year', $year);

        $newsletter = h($this->getRequest()->getQuery('newsletter', ''));
        $this->set('newsletter', $newsletter);

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');

        $firstOrderYear = $orderDetailsTable->getFirstOrderYear();
        $this->set('firstOrderYear', $firstOrderYear);

        $lastOrderYear = $orderDetailsTable->getLastOrderYear();
        $this->set('lastOrderYear', $lastOrderYear);

        $years = null;
        if ($lastOrderYear !== false && $firstOrderYear !== false) {
            $years = Configure::read('app.timeHelper')->getAllYearsUntilThisYear($lastOrderYear, $firstOrderYear, __d('admin', 'Member_fee') . ' ');
        }
        $this->set('years', $years);

        $customers = $this->getCustomers($active, $year, $newsletter);
        $this->set('customers', $customers);

        $this->set('title_for_layout', __d('admin', 'Members'));
    }

}
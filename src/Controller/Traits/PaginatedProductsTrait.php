<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait PaginatedProductsTrait {

    public function redirectIfPageIsSetTo1() {
        if ($this->getRequest()->getQuery('page') !== null && $this->getRequest()->getQuery('page') == 1) {
            $this->redirect($this->getRequest()->getAttribute('here'));
        }
    }

    public function throw404IfNoProductsOnPaginatedPageFound($products, $page) {
        if (count($products) == 0 && $page > 1) {
            throw new RecordNotFoundException('page ' . $page . ' does not contain any products');
        }
    }

}


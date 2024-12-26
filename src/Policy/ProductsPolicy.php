<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Authorization\Policy\ResultInterface;
use Authorization\IdentityInterface;
use Cake\ORM\TableRegistry;

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
class ProductsPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        $productId = (int) $request->getParam('idAndSlug');
        $productsTable = TableRegistry::getTableLocator()->get('Products');

        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
                'Products.active' => APP_ON,
            ],
            contain: [
                'Manufacturers',
            ]
        )->first();

        if (empty($product)) {
            throw new RecordNotFoundException('product not found');
        }

        if ($identity === null) {
            if (!Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                return false;
            }
            if (!empty($product->manufacturer) && $product->manufacturer->is_private) {
                return false;
            }
        }

        return true;

    }

}
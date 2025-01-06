<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;
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

        if ($identity === null) {
            return false;
        }

        switch ($request->getParam('action')) {
            case 'myImport':
                return $identity->isManufacturer();
            case 'generateProductCards':
                return Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($identity->isSuperadmin() || $identity->isAdmin());
            case 'editPurchasePrice':
            case 'calculateSellingPriceWithSurcharge':
                return Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && ($identity->isSuperadmin() || $identity->isAdmin());
            case 'import':
            case 'export':
                return $identity->isSuperadmin() || $identity->isAdmin();
            case 'editPrice':
            case 'editDeposit':
            case 'editTax':
                if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                    if ($identity->isSuperadmin() || $identity->isAdmin()) {
                        if ((!empty($request->getData('productId')) && !$this->productExists($request))
                            || !$this->manufacturerIsProductOwner($identity, $request)) {
                            return false;
                        }
                        return true;
                    }
                } else {
                    if ($identity->isSuperadmin() || $identity->isAdmin() || $identity->isManufacturer()) {
                        if ((!empty($request->getData('productId')) && !$this->productExists($request))
                            || !$this->manufacturerIsProductOwner($identity, $request)) {
                            return false;
                        }
                        return true;
                    }
                }
                return false;
            case 'index':
            case 'add':
            case 'ajaxGetProductsForDropdown':
                return $identity !== null;
            default:
                if (!empty($request->getData('productId')) && !$this->productExists($request)) {
                    return false;
                }
                if ($identity->isSuperadmin() || $identity->isAdmin()) {
                    return true;
                }
                if (!$this->manufacturerIsProductOwner($identity, $request)) {
                    return false;
                }
                return true;
        }

    }

    protected function productExists($request): bool
    {
        $productTable = TableRegistry::getTableLocator()->get('Products');
        $ids = $productTable->getProductIdAndAttributeId($request->getData('productId'));
        $productId = $ids['productId'];
        $product = $productTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ]
        )->first();
        return !empty($product);
    }

    protected function manufacturerIsProductOwner($identity, $request): bool
    {
        if (!$identity->isManufacturer()) {
            return true;
        }

        $productTable = TableRegistry::getTableLocator()->get('Products');

        // param productIds is passed via ajaxCall
        if (!empty($request->getData('productIds'))) {
            $productIds = $request->getData('productIds');
        }
        // param productId is passed via ajaxCall
        if (!empty($request->getData('productId'))) {
            $ids = $productTable->getProductIdAndAttributeId($request->getData('productId'));
            $productIds = [$ids['productId']];
        }
        // param objectId is passed via ajaxCall
        if (!empty($request->getData('objectId'))) {
            $ids = $productTable->getProductIdAndAttributeId($request->getData('objectId'));
            $productIds = [$ids['productId']];
        }
        // param productId is passed as first argument of url
        if (!empty($request->getParam('pass')[0])) {
            $productIds = [$request->getParam('pass')[0]];
        }
        if (!isset($productIds)) {
            return false;
        }
        $result = true;
        foreach($productIds as $productId) {
            $product = $productTable->find('all',
                conditions: [
                    'Products.id_product' => $productId
                ]
            )->first();
            if (empty($product) || $product->id_manufacturer != $identity->getManufacturerId()) {
                $result = false;
                break;
            }
        }
        if ($result) {
            return true;
        }

        return $result;

    }

}
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
use Admin\Traits\Products\GenerateProductCardsTrait;
use Admin\Traits\Products\GetProductsForDropdownTrait;
use Admin\Traits\Products\IndexTrait;
use Admin\Traits\Products\SaveUploadedImageTrait;
use Cake\Event\EventInterface;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;

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
    use SaveUploadedImageTrait;

    protected $Product;

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'generateProductCards':
                return Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin());
                break;
            case 'editPurchasePrice':
            case 'calculateSellingPriceWithSurcharge':
                return Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin());
                break;
            case 'detectMissingProductImages':
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                return false;
            case 'editPrice':
            case 'editDeposit':
            case 'editTax':
                if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                    if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                        if ((!empty($this->getRequest()->getData('productId')) && !$this->productExists())
                            || !$this->manufacturerIsProductOwner()) {
                            $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                            return false;
                        }
                        return true;
                    }
                } else {
                    if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isManufacturer()) {
                        if ((!empty($this->getRequest()->getData('productId')) && !$this->productExists())
                            || !$this->manufacturerIsProductOwner()) {
                            $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                            return false;
                        }
                        return true;
                    }
                }
                $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                return false;
                break;
            case 'index':
            case 'add':
            case 'ajaxGetProductsForDropdown':
                return $this->AppAuth->user();
                break;
            default:
                if (!empty($this->getRequest()->getData('productId')) && !$this->productExists()) {
                    $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                    return false;
                }
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                if (!$this->manufacturerIsProductOwner()) {
                    $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                    return false;
                }
                return true;
                break;
        }
    }

    protected function productExists()
    {
        $ids = $this->Product->getProductIdAndAttributeId($this->getRequest()->getData('productId'));
        $productId = $ids['productId'];
        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId,
            ]
        ])->first();
        return !empty($product);
    }

    protected function manufacturerIsProductOwner()
    {
        if (!$this->AppAuth->isManufacturer()) {
            return true;
        }

        // param productIds is passed via ajaxCall
        if (!empty($this->getRequest()->getData('productIds'))) {
            $productIds = $this->getRequest()->getData('productIds');
        }
        // param productId is passed via ajaxCall
        if (!empty($this->getRequest()->getData('productId'))) {
            $ids = $this->Product->getProductIdAndAttributeId($this->getRequest()->getData('productId'));
            $productIds = [$ids['productId']];
        }
        // param objectId is passed via ajaxCall
        if (!empty($this->getRequest()->getData('objectId'))) {
            $ids = $this->Product->getProductIdAndAttributeId($this->getRequest()->getData('objectId'));
            $productIds = [$ids['productId']];
        }
        // param productId is passed as first argument of url
        if (!empty($this->getRequest()->getParam('pass')[0])) {
            $productIds = [$this->getRequest()->getParam('pass')[0]];
        }
        if (!isset($productIds)) {
            return false;
        }
        $result = true;
        foreach($productIds as $productId) {
            $product = $this->Product->find('all', [
                'conditions' => [
                    'Products.id_product' => $productId
                ]
            ])->first();
            if (empty($product) || $product->id_manufacturer != $this->AppAuth->getManufacturerId()) {
                $result = false;
                break;
            }
        }
        if ($result) {
            return true;
        }

        return $result;

    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->Product = $this->getTableLocator()->get('Products');
    }

}

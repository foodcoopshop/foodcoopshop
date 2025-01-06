<?php
declare(strict_types=1);

namespace Network\Controller;

use App\Services\SanitizeService;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Controller\Controller;
use Cake\Database\Expression\QueryExpression;
use Cake\Routing\Router;
use Cake\View\JsonView;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ApiController extends Controller
{

    protected mixed $identity = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function beforeFilter(EventInterface $event): void
    {
        $identity = Router::getRequest()->getAttribute('identity');
        $this->identity = $identity;
        $this->set('identity', $identity);
    }

    private function getProductDetailLinks($productsData): string
    {
        $productDetailLinks = [];
        $productsTable = $this->getTableLocator()->get('Products');
        foreach ($productsData as $originalProduct) {
            $productIds = $productsTable->getProductIdAndAttributeId($originalProduct['remoteProductId']);
            $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productIds['productId'],
            ],
            contain: [
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ])->first();
            if ($productIds['attributeId'] == 0) {
                $linkName = $product->name;
            } else {
                foreach ($product->product_attributes as $attribute) {
                    if ($attribute->id_product_attribute == $productIds['attributeId']) {
                        $linkName = $product->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                    }
                }
            }
            if (isset($linkName)) {
                $productDetailLinks[] = Configure::read('app.htmlHelper')->link($linkName, Configure::read('app.slugHelper')->getProductDetail($productIds['productId'], $product->name));
            }
        }
        return join(', ', $productDetailLinks);
    }

    public function updateProducts(): void
    {

        $productsData = $this->getRequest()->getData('data.data');

        if (empty($productsData)) {
            throw new \Exception('Keine Produkte vorhanden.');
        }

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $productsTable = $this->getTableLocator()->get('Products');
        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');

        $products2saveForImage = [];
        $products2saveForName = [];
        $products2saveForIsStockProduct = [];
        $products2saveForQuantity = [];
        $products2saveForPrice = [];
        $products2saveForDeposit = [];
        $products2saveForDeliveryRhythm = [];
        $products2saveForStatus = [];

        $products = [];
        $attributes = [];

        foreach ($productsData as $product) {

            $productIds = $productsTable->getProductIdAndAttributeId($product['remoteProductId']);

            $manufacturerIsOwner = $productsTable->find('all', conditions: [
                'Products.id_product' => $productIds['productId'],
                'Products.id_manufacturer' => $this->identity->getManufacturerId()
            ])->count();
            if (!$manufacturerIsOwner) {
                throw new \Exception('the product' . $productIds['productId'] . ' is not associated with manufacturer ' . $this->identity->getManufacturerName());
            }

            if ($productIds['attributeId'] == 0) {
                $products[] = $product;
            } else {
                $attributes[] = $product;
            }

            if (isset($product['image'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForImage[] = [
                        $productIds['productId'] => $product['image']
                    ];
                }
            }
            if (isset($product['name'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForName[] = [
                        $productIds['productId'] => $product['name']
                    ];
                }
            }

            if (isset($product['is_stock_product'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForIsStockProduct[] = [
                        $productIds['productId'] => $product['is_stock_product']
                    ];
                }
            }

            if (isset($product['quantity'])) {
                $product['quantity'] = [
                    'quantity' => $product['quantity']['stock_available_quantity'],
                    'quantity_limit' => $product['quantity']['stock_available_quantity_limit'],
                    'sold_out_limit' => $product['quantity']['stock_available_sold_out_limit'],
                    'always_available' => $product['quantity']['stock_available_always_available'],
                    'default_quantity_after_sending_order_lists' => $product['quantity']['stock_available_default_quantity_after_sending_order_lists'],
                ];
                $products2saveForQuantity[] = [
                    $product['remoteProductId'] => $product['quantity']
                ];
            }
            if (isset($product['price'])) {

                $variableMemberFee = $manufacturersTable->getOptionVariableMemberFee($this->identity->getManufacturerVariableMemberFee());

                if ($variableMemberFee > 0) {

                    $price = Configure::read('app.numberHelper')->getStringAsFloat($product['price']['gross_price']);
                    $product['price']['gross_price'] = $manufacturersTable->increasePriceWithVariableMemberFee($price, $variableMemberFee);

                    if (isset($product['price']['unit_product_price_incl_per_unit'])) {
                        $pricePerUnit = Configure::read('app.numberHelper')->getStringAsFloat($product['price']['unit_product_price_incl_per_unit']);
                        $product['price']['unit_product_price_incl_per_unit'] = $manufacturersTable->increasePriceWithVariableMemberFee($pricePerUnit, $variableMemberFee);
                    }

                }

                if (!isset($product['price']['unit_product_price_per_unit_enabled'])) {
                    $product['price']['unit_product_price_per_unit_enabled'] = 0;
                    $product['price']['unit_product_price_incl_per_unit'] = 0;
                    $product['price']['unit_product_name'] = '';
                    $product['price']['unit_product_amount'] = 0;
                    $product['price']['unit_product_quantity_in_units'] = 0;
                    $product['price']['unit_product_use_weight_as_amount'] = 0;
                }

                $products2saveForPrice[] = [
                    $product['remoteProductId'] => $product['price']
                ];
            }

            if (isset($product['deposit'])) {
                $products2saveForDeposit[] = [
                    $product['remoteProductId'] => Configure::read('app.numberHelper')->getStringAsFloat($product['deposit'])
                ];
            }

            if (isset($product['delivery_rhythm'])) {
                $products2saveForDeliveryRhythm[] = [
                    $product['remoteProductId'] => $product['delivery_rhythm']
                ];
            }

            if (isset($product['active'])) {
                if ($productIds['attributeId'] == 0) {
                    $products2saveForStatus[] = [
                        $productIds['productId'] => (int) $product['active']
                    ];
                }
            }
        }

        $syncFieldsOk = [];
        $syncFieldsError = [];

        if (empty($products2saveForImage) &&
            empty($products2saveForName) &&
            empty($products2saveForIsStockProduct) &&
            empty($products2saveForQuantity) &&
            empty($products2saveForPrice) &&
            empty($products2saveForDeposit) &&
            empty($products2saveForDeliveryRhythm) &&
            empty($products2saveForStatus)) {
            $message = __d('network', 'No_fields_were_selected_for_synchronizing.');
        } else {

            if (!empty($products2saveForImage)) {
                $syncFieldsOk[] = __d('network', 'Image');
                $updateStatus = $productsTable->changeImage($products2saveForImage);
                $productIds = [];
                foreach ($products2saveForImage as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForName)) {
                $syncFieldsOk[] = __d('network', 'Name');
                $updateStatus = $productsTable->changeName($products2saveForName);
                $productIds = [];
                foreach ($products2saveForName as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForIsStockProduct)) {
                $fieldName = __d('network', 'Stock_product');
                try {
                    $updateIsStockProduct = $productsTable->changeIsStockProduct($products2saveForIsStockProduct);
                    if ($updateIsStockProduct) {
                        $syncFieldsOk[] = $fieldName;
                        $productIds = [];
                        foreach ($products2saveForIsStockProduct as $p) {
                            $productIds[] = key($p);
                        }
                    } else {
                        $syncFieldsError[] = $fieldName;
                    }
                } catch (\Exception $e) {
                    $syncFieldsError[] = $fieldName;
                }
            }

            if (!empty($products2saveForQuantity)) {
                $syncFieldsOk[] = __d('network', 'Amount');
                $productsTable->changeQuantity($products2saveForQuantity);
                $productIds = [];
                foreach ($products2saveForQuantity as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForPrice)) {
                $fieldName = __d('network', 'Price');
                try {
                    $updateStatus = $productsTable->changePrice($products2saveForPrice);
                    if ($updateStatus) {
                        $syncFieldsOk[] = $fieldName;
                        $productIds = [];
                        foreach ($products2saveForPrice as $p) {
                            $productIds[] = key($p);
                        }
                    } else {
                        $syncFieldsError[] = $fieldName;
                    }
                } catch (\Exception $e) {
                    $syncFieldsError[] = $fieldName;
                }
            }

            if (!empty($products2saveForDeposit)) {
                $syncFieldsOk[] = __d('network', 'Deposit');
                $updateStatus = $productsTable->changeDeposit($products2saveForDeposit);
                $productIds = [];
                foreach ($products2saveForDeposit as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForDeliveryRhythm)) {
                $syncFieldsOk[] = __d('network', 'Delivery_rhythm');
                $updateStatus = $productsTable->changeDeliveryRhythm($products2saveForDeliveryRhythm);
                $productIds = [];
                foreach ($products2saveForDeliveryRhythm as $p) {
                    $productIds[] = key($p);
                }
            }

            if (!empty($products2saveForStatus)) {
                $fieldName = __d('network', 'Status');
                try {
                    $updateStatus = $productsTable->changeStatus($products2saveForStatus);
                    if ($updateStatus) {
                        $syncFieldsOk[] = $fieldName;
                        $productIds = [];
                        foreach ($products2saveForStatus as $p) {
                            $productIds[] = key($p);
                        }
                    } else {
                        $syncFieldsError[] = $fieldName;
                    }
                } catch (\Exception $e) {
                    $syncFieldsError[] = $fieldName;
                }
            }

            $message = '';
            $actionLogMessage = '';
            $errorMessage = '';

            $syncronizedProductsString = count($products) . ' '. (count($products) == 1 ? __d('network', 'product') : __d('network', 'products'));
            $syncronizedAttributesString = count($attributes) . ' '. (count($attributes) == 1 ? __d('network', 'attribute') : __d('network', 'attributes'));
            $listOfSyncFieldsOk = join(', ', $syncFieldsOk);

            if (count($syncFieldsOk) > 0) {
                $message = __d('network', '{0}_and_{1}_({2})_have_been_successfully_synchronized.', [$syncronizedProductsString, $syncronizedAttributesString, $listOfSyncFieldsOk]);
            }
            $actionLogMessage = __d('network', 'Via_{0}_there_have_been_{1}_and_{2}_({3})_successfully_synchronized.', [$this->getRequest()->getData('data.metaData.baseDomain'), $syncronizedProductsString, $syncronizedAttributesString, $listOfSyncFieldsOk]);
            $actionLogMessage .= ' ' . $this->getProductDetailLinks($productsData);

            if (count($syncFieldsError) > 0) {
                $errorMessage .=  '<br /><b>'.__d('network', 'Errors_occurred_while_synchronizing!').'</b><br />';
                $errorMessage .= '<b>';
                if (count($syncFieldsError) == 1) {
                    $errorMessage .=  __d('network', '{0}_has_not_been_updated.', [join(', ', $syncFieldsError)]);
                } else {
                    $errorMessage .=  __d('network', '{0}_have_not_been_updated.', [join(', ', $syncFieldsError)]);
                }
                $errorMessage .= '</b><br />';
                $message .= $errorMessage;
                $actionLogMessage .= $errorMessage;
            }

            if ($actionLogMessage != '') {
                $actionLogsTable->customSave('product_remotely_changed', $this->identity->getId(), 0, 'products', $actionLogMessage);
            }
        }

        $this->set([
            'app' => [
                'name' => $this->getInstallationName(),
                'domain' => Configure::read('App.fullBaseUrl')
            ],
            'status' => count($syncFieldsError) == 0,
            'msg' => $message,
        ]);
        $this->viewBuilder()->setOption('serialize', ['app', 'status', 'msg']);

    }

    private function getInstallationName(): string
    {

        return Configure::check('appDb.FCS_APP_NAME') ? Configure::read('appDb.FCS_APP_NAME') : Configure::read('App.fullBaseUrl');
    }

    public function getProducts(): void
    {

        $productsTable = $this->getTableLocator()->get('Products');
        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');

        $variableMemberFee = $manufacturersTable->getOptionVariableMemberFee(
            $this->identity->getManufacturerVariableMemberFee()
        );
        $preparedProducts = $productsTable->getProductsForBackend(
            productIds: '',
            manufacturerId: $this->identity->getManufacturerId(),
            active: 'all',
            addProductNameToAttributes: true,
        );

        $this->set([
            'app' => [
                'name' => $this->getInstallationName(),
                'domain' => Configure::read('App.fullBaseUrl'),
                'variableMemberFee' => $variableMemberFee
            ],
            'identity' => $this->identity->toArray(),
            'products' => $preparedProducts
        ]);
        $this->viewBuilder()->setOption('serialize', ['app', 'identity', 'products']);
    }

    public function getOrders(): void
    {

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $pickupDay = h($this->getRequest()->getQuery('pickupDay'));
        $formattedPickupDay = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);

        if (empty($pickupDay) || $formattedPickupDay == '1970-01-01') {
            $this->set([
                'error' => 'wrong pickupDay format',
            ]);
            $this->viewBuilder()->setOption('serialize', ['error']);
            return;
        }
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $conditions = [
            'Products.id_manufacturer' => $this->identity->getManufacturerId(),
        ];
        $exp = new QueryExpression();
        $conditions[] = $exp->eq('DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\')', $formattedPickupDay);

        $orderDetails = $orderDetailsTable->find('all',
        conditions: $conditions,
        contain: [
            'Products',
            'OrderDetailUnits',
        ]);

        $preparedOrders = [];
        foreach($orderDetails as $orderDetail) {
            $preparedOrder = [
                'id' => $orderDetail->id_order_detail,
                'product_id' => $orderDetail->product_id,
                'attribute_id' => $orderDetail->product_attribute_id,
                'name' => $orderDetail->product_name,
                'amount' => $orderDetail->product_amount,
                'order_state' => $orderDetail->order_state,
                'created' => $orderDetail->created,
            ];
            if (!empty($orderDetail->order_detail_unit)) {
                $preparedOrder['unit'] = [
                    'name' =>  $orderDetail->order_detail_unit->unit_name,
                    'product_quantity_in_units' => $orderDetail->order_detail_unit->product_quantity_in_units,
                    'mark_as_saved' => (bool) $orderDetail->order_detail_unit->mark_as_saved,
                ];
            }
            $preparedOrders[] = $preparedOrder;
        }

        $this->set([
            'app' => [
                'name' => $this->getInstallationName(),
                'domain' => Configure::read('App.fullBaseUrl'),
                'orders' => $preparedOrders,
            ],
        ]);
        $this->viewBuilder()->setOption('serialize', ['app', 'orders']);

    }

}

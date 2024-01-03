<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;

class ProductsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        $productId = (int) $request->getParam('pass')[0];
        $productTable = FactoryLocator::get('Table')->get('Products');

        $product = $productTable->find('all', [
            'conditions' => [
                'Products.id_product' => $productId,
                'Products.active' => APP_ON,
            ],
            'contain' => [
                'Manufacturers',
            ]
        ])->first();

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
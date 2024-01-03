<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

class ManufacturersPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if (!Configure::read('app.showManufacturerListAndDetailPage')) {
            throw new NotFoundException();
        }

        switch ($request->getParam('action')) {
            case 'detail':
                $manufacturerId = (int) $request->getParam('pass')[0];
                $manufacturerTable = FactoryLocator::get('Table')->get('Manufacturers');
                $manufacturer = $manufacturerTable->find('all', [
                    'conditions' => [
                        'Manufacturers.id_manufacturer' => $manufacturerId,
                        'Manufacturers.active' => APP_ON,
                    ]
                ])->first();
                if (!empty($manufacturer) && $identity === null && $manufacturer->is_private) {
                    return false;
                }
                break;
        }

        return true;

    }

}
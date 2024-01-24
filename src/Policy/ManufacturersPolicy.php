<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Authorization\Policy\ResultInterface;
use Authorization\IdentityInterface;

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
class ManufacturersPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if (!Configure::read('app.showManufacturerListAndDetailPage')) {
            throw new NotFoundException();
        }

        switch ($request->getParam('action')) {
            case 'detail':
                $manufacturerId = (int) $request->getParam('pass')[0];
                $manufacturerTable = FactoryLocator::get('Table')->get('Manufacturers');
                $manufacturer = $manufacturerTable->find('all',
                    conditions: [
                        'Manufacturers.id_manufacturer' => $manufacturerId,
                        'Manufacturers.active' => APP_ON,
                    ]
                )->first();
                if (!empty($manufacturer) && $identity === null && $manufacturer->is_private) {
                    return false;
                }
                break;
        }

        return true;

    }

}
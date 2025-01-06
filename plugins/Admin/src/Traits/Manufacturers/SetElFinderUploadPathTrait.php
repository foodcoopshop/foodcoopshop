<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers;

use Cake\Core\Configure;

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

trait SetElFinderUploadPathTrait
{

    public function setElFinderUploadPath($manufacturerId): void
    {
        $this->request = $this->request->withParam('_ext', 'json');

        if ($this->identity->isManufacturer()) {
            $manufacturerId = $this->identity->getManufacturerId();
        } else {
            $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
            $manufacturer = $manufacturersTable->find('all', conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ])->first();
            $manufacturerId = $manufacturer->id_manufacturer;
        }

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('App.fullBaseUrl') . "/files/kcfinder/manufacturers/" . $manufacturerId,
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/manufacturers/" . $manufacturerId
        ];

        $this->set([
            'status' => true,
            'msg' => 'OK',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

}
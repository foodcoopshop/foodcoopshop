<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Admin\Traits\ManufacturerIdTrait;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Services\Csv\Reader\ProductPriceUpdateReaderService;
use Cake\ORM\TableRegistry;
use Cake\Http\Response;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Volker Peters
 * @link          https://www.foodcoopshop.com
 */

trait PriceUpdateTrait
{

    use ManufacturerIdTrait;

    public function myPriceUpdate(): Response
    {
        $this->manufacturerId = $this->identity->getManufacturerId();
        $this->priceUpdate();
        return $this->render('price_update');
    }

    public function priceUpdate(): void
    {
        $manufacturerId = (int) $this->getManufacturerId();
        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturer = $manufacturersTable->find('all',
            conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId,
            ]
        )->first();

        if (empty($manufacturer)) {
            throw new RecordNotFoundException('manufacturer not found or not active');
        }
        $this->set('manufacturer', $manufacturer);
        $this->set('title_for_layout', __('Price_update_for_{0}', [$manufacturer->name]));

        if (!empty($this->getRequest()->getData('upload'))) {

            $upload = $this->getRequest()->getData('upload');
            if (!in_array($upload->getClientMediaType(), ProductPriceUpdateReaderService::ALLOWED_UPLOAD_MIME_TYPES)) {
                $this->Flash->error(__('The_uploaded_file_is_not_valid.'));
                return;
            }

            $surcharge = Configure::read('app.numberHelper')->getStringAsFloat(
                (string) $this->getRequest()->getData('surcharge', '10')
            );
            if ($surcharge < 0) {
                $this->Flash->error(__('Surcharge_needs_to_be_greater_than_0.'));
                return;
            }

            $content = $upload->getStream()->getContents();
            $reader = ProductPriceUpdateReaderService::fromString($content);
            $reader->configureType();

            $result = $reader->priceUpdate($manufacturerId, $surcharge);

            if (empty($result['errors'])) {
                $message = __('Price_update_successful.') . ' '
                    . __('Price_update_result_{0}_{1}_{2}', [
                        $result['updated'],
                        $result['inserted'],
                        $result['deactivated'],
                    ]);
                $this->Flash->success($message);
                $actionLogsTable = TableRegistry::getTableLocator()->get('ActionLogs');
                $actionLogsTable->customSave('product_price_changed', $this->identity->getId(), $manufacturer->id_manufacturer, 'products', $message);
            } else {
                $this->Flash->error(__('The_uploaded_file_is_not_valid.'));
            }
        }
    }

}

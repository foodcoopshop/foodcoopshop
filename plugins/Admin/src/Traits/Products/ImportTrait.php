<?php

declare(strict_types=1);

namespace Admin\Traits\Products;

use Admin\Traits\ManufacturerIdTrait;
use Cake\Datasource\Exception\RecordNotFoundException;
use League\Csv\Writer;
use App\Services\Csv\Reader\ProductReaderService;
use Cake\ORM\TableRegistry;
use Cake\Http\Response;

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

trait ImportTrait
{

    use ManufacturerIdTrait;

    private array $columnsFieldMap = [];

    public function initializeImportTrait(): void
    {
        $this->columnsFieldMap = [
            __d('admin', 'Name') => 'name',
            __d('admin', 'Description_short') => 'description_short',
            __d('admin', 'Description') => 'description',
            __d('admin', 'Unit') => 'unity',
            __d('admin', 'Gross_price') => 'price',
            __d('admin', 'Tax_rate') => 'id_tax',
            __d('admin', 'Deposit') => 'deposit_product',
            __d('admin', 'Amount') => 'stock_available',
            __d('admin', 'Status') => 'active',
            __d('admin', 'Product_declaration') => 'is_declaration_ok',
            __d('admin', 'Storage_location') => 'id_storage_location',
        ];
    }

    public function downloadImportTemplate(): Response
    {

        $this->initializeImportTrait();

        $writer = Writer::createFromString();
        $columns = array_keys($this->columnsFieldMap);
        $writer->insertOne($columns);

        // force download
        $this->disableAutoRender();

        $response = $this->response;
        $response = $response->withStringBody($writer->toString());
        $response = $response->withCharset('UTF-8');
        $response = $response->withDownload('product-import-template.csv');

        return $response;
    }

    public function myImport(): void
    {
        $this->manufacturerId = $this->identity->getManufacturerId();
        $this->import();
        $this->render('import');
    }

    public function import(): void
    {

        $this->initializeImportTrait();

        $manufacturerId = $this->getManufacturerId();
        $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
        $manufacturer = $manufacturersTable->find('all',
            conditions: [
                'Manufacturers.id_manufacturer' => (int) $manufacturerId,
            ]
        )->first();

        if (empty($manufacturer)) {
            throw new RecordNotFoundException('manufacturer not found or not active');
        }
        $this->set('manufacturer', $manufacturer);

        $this->set('title_for_layout', __d('admin', 'Product_import_for_{0}', [$manufacturer->name]));

        if (!empty($this->getRequest()->getData('upload'))) {

            $upload = $this->getRequest()->getData('upload');
            if (!in_array($upload->getClientMediaType(), ProductReaderService::ALLOWED_UPLOAD_MIME_TYPES)) {
                $this->Flash->error(__d('admin', 'The_uploaded_file_is_not_valid.'));
                return;
            }

            $content = $upload->getStream()->getContents();
            $reader = ProductReaderService::createFromString($content);
            $reader->configureType();

            $productEntities = $reader->import($manufacturerId);
            $this->set('productEntities', $productEntities);

            if ($reader->areAllEntitiesValid($productEntities)) {
                $messageString = __d('admin', 'Product_import_successful.') . ' ' . count($productEntities) . 'x';
                $this->Flash->success($messageString);
                $actionLogsTable = TableRegistry::getTableLocator()->get('ActionLogs');
                $actionLogsTable->customSave('product_added', $this->identity->getId(), $manufacturer->id_manufacturer, 'products', $messageString);
            } else {
                $errors = $reader->getAllErrors($productEntities);
                $errorRows = [];
                foreach ($errors as $row => $error) {
                    if (empty($error)) {
                        continue;
                    }
                    $header = '<b style="line-height:40px;">' . (!empty($productEntities[$row]['name']) ? $productEntities[$row]['name'] : __('Product') . ' ' . ($row + 1)) . '</b><br />';
                    $errorMessage = '';
                    foreach ($error as $fieldName => $messages) {
                        $mappedFieldName = array_search($fieldName, $this->columnsFieldMap);
                        $errorMessage .= '<li><u>' . $mappedFieldName . '</u>: ';
                        foreach ($messages as $errorType => $message) {
                            if (is_array($message)) {
                                $message = array_unique($message);
                                $message = implode(' / ', $message);
                            }
                            $errorMessage .= $message;
                        }
                        $errorMessage .= '</li>';
                    }
                    $errorRows[] = $header . $errorMessage;
                }
                if (empty($errors)) {
                    $errorMessage = __d('admin', 'The_uploaded_file_was_empty._Please_add_products_and_upload_again.');
                } else {
                    $errorMessage = __d('admin', 'The_uploaded_file_is_not_valid.') . '<br /><ul>' . implode('', $errorRows) . '</ul>';
                }
                $this->Flash->error($errorMessage);
            }
        }
    }
}

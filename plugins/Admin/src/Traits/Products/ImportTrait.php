<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Admin\Traits\ManufacturerIdTrait;
use App\Lib\Csv\ProductReader;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\FactoryLocator;
use League\Csv\Writer;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait ImportTrait {

    use ManufacturerIdTrait;

    public function downloadImportTemplate()
    {
        $columns = [
            __d('admin', 'Name'),
            __d('admin', 'Description_short'),
            __d('admin', 'Description'),
            __d('admin', 'Unit'),
            __d('admin', 'Product_declaration'),
            __d('admin', 'Storage_location'),
            __d('admin', 'Status'),
            __d('admin', 'Gross_price'),
            __d('admin', 'Tax_rate'),
            __d('admin', 'Deposit'),
            __d('admin', 'Amount'),
        ];

        $writer = Writer::createFromString();
        $writer->insertOne($columns);

        // force download
        $this->RequestHandler->renderAs(
            $this,
            'csv',
            [
                'charset' => 'UTF-8'
            ],
        );
        $this->disableAutoRender();

        $response = $this->response;
        $response = $response->withStringBody($writer->toString());
        $response = $response->withDownload('product-import-template.csv');

        return $response;

    }

    public function myImport()
    {
        $this->manufacturerId = $this->AppAuth->getManufacturerId();
        $this->import();
        $this->render('import');
    }

    public function import()
    {

        $manufacturerId = $this->getManufacturerId();
        $manufacturersTable = FactoryLocator::get('Table')->get('Manufacturers');
        $manufacturer = $manufacturersTable->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => (int) $manufacturerId,
            ]
        ])->first();

        if (empty($manufacturer)) {
            throw new RecordNotFoundException('manufacturer not found or not active');
        }
        $this->set('manufacturer', $manufacturer);

        $this->set('title_for_layout', __d('admin', 'Product_import_for_{0}', [$manufacturer->name]));

        if (!empty($this->getRequest()->getData('upload'))) {

            $upload = $this->getRequest()->getData('upload');
            $content = $upload->getStream()->getContents();
            $reader = ProductReader::createFromString($content);
            $reader->configureType();

            $productEntities = $reader->import($manufacturerId);
            $this->set('productEntities', $productEntities);
            
            if ($reader->areAllEntitiesValid($productEntities)) {
                $this->Flash->success(__d('admin', 'Product_import_successful.' . ' ' . count($productEntities) . 'x'));
            } else {
                $errors = $reader->getAllErrors($productEntities);
                $errorRows = [];
                foreach($errors as $row => $error) {
                    $errorMessage = __('Product') . ' ' . $row + 1 . '<br />';
                    foreach($error as $fieldName => $messages) {
                        $errorMessage .= $fieldName . ': ';
                        foreach($messages as $errorType => $message) {
                            if (is_array($message)) {
                                $message = implode(' / ', $message);
                            }
                            $errorMessage .= $errorType . ': ' . $message;
                        }
                        $errorMessage .= '<br />';
                    }
                    $errorRows[] = '<li>' . $errorMessage . '</li>';
                }
                $this->Flash->error(__d('admin', 'The_uploaded_file_is_not_valid.') . '<br /><ul>' . implode('', $errorRows) . '</ul>');
            }

        }

    }

}

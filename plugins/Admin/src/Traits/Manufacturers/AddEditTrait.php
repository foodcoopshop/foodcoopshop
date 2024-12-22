<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers;

use App\Services\SanitizeService;
use App\Controller\Component\StringComponent;
use Cake\Http\Exception\NotFoundException;
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

trait AddEditTrait
{

    public function profile()
    {
        $this->edit($this->identity->getManufacturerId());
        $this->set('referer', $this->getRequest()->getUri()->getPath());
        $this->set('title_for_layout', __d('admin', 'Edit_profile'));
        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function add()
    {
        $manufacturer = $this->Manufacturer->newEntity(
            [
                'active' => APP_ON,
                'is_private' => Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') ? APP_OFF : APP_ON,
                'anonymize_customers' => APP_ON,
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('admin', 'Add_manufacturer'));
        $this->_processForm($manufacturer, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($manufacturerId)
    {
        if ($manufacturerId === null) {
            throw new NotFoundException;
        }

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('App.fullBaseUrl') . "/files/kcfinder/manufacturers/" . $manufacturerId,
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/manufacturers/" . $manufacturerId
        ];

        $manufacturer = $this->Manufacturer->find('all',
        conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ],
        contain: [
            'AddressManufacturers'
        ])->first();

        if (empty($manufacturer)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_manufacturer'));
        $this->_processForm($manufacturer, true);
    }

    private function _processForm($manufacturer, $isEditMode)
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);
        if (empty($this->getRequest()->getData())) {
            $this->set('manufacturer', $manufacturer);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['description', 'short_description'])));

        $iban = $this->getRequest()->getData('Manufacturers.iban') ?? '';
        $this->setRequest($this->getRequest()->withData('Manufacturers.iban', str_replace(' ', '', $iban)));
        $bic = $this->getRequest()->getData('Manufacturers.bic') ?? '';
        $this->setRequest($this->getRequest()->withData('Manufacturers.bic', str_replace(' ', '', $bic)));
        $this->setRequest($this->getRequest()->withData('Manufacturers.homepage', StringComponent::addHttpToUrl($this->getRequest()->getData('Manufacturers.homepage'))));

        if ($isEditMode) {
            // keep original data for getCustomerRecord - clone does not work on nested objects
            $unchangedManufacturerAddress = clone $manufacturer->address_manufacturer;
        }

        $manufacturer = $this->Manufacturer->patchEntity(
            $manufacturer,
            $this->getRequest()->getData(),
            [
                'associated' => [
                    'AddressManufacturers'
                ]
            ]
        );
        if ($manufacturer->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('manufacturer', $manufacturer);
            $this->render('edit');
        } else {
            $manufacturer = $this->Manufacturer->save($manufacturer);

            if (!$isEditMode) {
                $customer = [];
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'manufacturer_added';
            } else {
                $customer = $this->Manufacturer->getCustomerRecord($unchangedManufacturerAddress->email);
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'manufacturer_changed';
            }

            $customersTable = $this->getTableLocator()->get('Customers');
            $customerData = [
                'email' => $this->getRequest()->getData('Manufacturers.address_manufacturer.email'),
                'firstname' => $this->getRequest()->getData('Manufacturers.address_manufacturer.firstname'),
                'lastname' => $this->getRequest()->getData('Manufacturers.address_manufacturer.lastname'),
                'active' => APP_ON
            ];
            if (empty($customer)) {
                $customerEntity = $customersTable->newEntity($customerData);
            } else {
                $customerEntity = $customersTable->patchEntity($customer, $customerData);
            }
            $customersTable->save($customerEntity);

            if (!empty($this->getRequest()->getData('Manufacturers.tmp_image'))) {
                $this->saveUploadedImage($manufacturer->id_manufacturer, $this->getRequest()->getData('Manufacturers.tmp_image'), Configure::read('app.htmlHelper')->getManufacturerThumbsPath(), Configure::read('app.manufacturerImageSizes'));
            }

            if (!empty($this->getRequest()->getData('Manufacturers.delete_image'))) {
                $this->deleteUploadedImage($manufacturer->id_manufacturer, Configure::read('app.htmlHelper')->getManufacturerThumbsPath());
            }

            if (!empty($this->getRequest()->getData('Manufacturers.tmp_general_terms_and_conditions'))) {
                $this->saveUploadedGeneralTermsAndConditions($manufacturer->id_manufacturer, $this->getRequest()->getData('Manufacturers.tmp_general_terms_and_conditions'));
            }

            if (!empty($this->getRequest()->getData('Manufacturers.delete_general_terms_and_conditions'))) {
                $this->deleteUploadedGeneralTermsAndConditions($manufacturer->id_manufacturer);
            }

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $message = __d('admin', 'The_manufacturer_{0}_has_been_{1}.', ['<b>' . $manufacturer->name . '</b>', $messageSuffix]);
            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $manufacturer->id_manufacturer, 'manufacturers', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $manufacturer->id_manufacturer);

            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getManufacturerProfile()) {
                $this->renewAuthSession();
            }

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('manufacturer', $manufacturer);
    }


    private function saveUploadedGeneralTermsAndConditions(int $manufacturerId, string $filename): void
    {
        $newFilename = Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrcTemplate($manufacturerId);
        $path = dirname(WWW_ROOT . $newFilename);
        mkdir($path, 0755, true);
        copy(WWW_ROOT . $filename, WWW_ROOT . $newFilename);
    }

    private function deleteUploadedGeneralTermsAndConditions($manufacturerId)
    {
        $fileName = Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrcTemplate($manufacturerId);
        if (file_exists(WWW_ROOT . $fileName)) {
            unlink(WWW_ROOT . $fileName);
        }
    }    

}
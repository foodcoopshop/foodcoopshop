<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers;

use Cake\Core\Configure;
use App\Services\SanitizeService;
use Cake\Http\Exception\NotFoundException;
use App\Services\DeliveryRhythmService;

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

trait EditOptionsTrait
{

    public function myOptions()
    {
        $this->editOptions($this->identity->getManufacturerId());
        $this->set('referer', $this->getRequest()->getUri()->getPath());
        $this->set('title_for_layout', __d('admin', 'Edit_settings'));
        if (empty($this->getRequest()->getData())) {
            $this->render('editOptions');
        }
    }

    public function editOptions($manufacturerId)
    {
        if ($manufacturerId === null) {
            throw new NotFoundException;
        }

        $manufacturer = $this->Manufacturer->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();

        if (empty($manufacturer)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', $manufacturer->name . ': ' . __d('admin', 'Edit_settings'));

        $this->Tax = $this->getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());

        if (!Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $noDeliveryBreakOptions = (new DeliveryRhythmService())->getNextWeeklyDeliveryDays();
            $this->set('noDeliveryBreakOptions', $noDeliveryBreakOptions);
        }

        // set default data if manufacturer options are null
        if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && is_null($manufacturer->variable_member_fee)) {
            $manufacturer->variable_member_fee = Configure::read('appDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE');
        }
        if (is_null($manufacturer->send_order_list)) {
            $manufacturer->send_order_list = Configure::read('app.defaultSendOrderList');
        }
        if (is_null($manufacturer->send_invoice)) {
            $manufacturer->send_invoice = Configure::read('app.defaultSendInvoice');
        }
        if (is_null($manufacturer->default_tax_id)) {
            $manufacturer->default_tax_id = Configure::read('app.defaultTaxId');
        }
        if (is_null($manufacturer->default_tax_id_purchase_price)) {
            $manufacturer->default_tax_id_purchase_price = Configure::read('app.defaultTaxIdPurchasePrice');
        }
        if (is_null($manufacturer->send_instant_order_notification)) {
            $manufacturer->send_instant_order_notification = Configure::read('app.defaultSendInstantOrderNotification');
        }
        if (is_null($manufacturer->send_ordered_product_deleted_notification)) {
            $manufacturer->send_ordered_product_deleted_notification = Configure::read('app.defaultSendOrderedProductDeletedNotification');
        }
        if (is_null($manufacturer->send_ordered_product_price_changed_notification)) {
            $manufacturer->send_ordered_product_price_changed_notification = Configure::read('app.defaultSendOrderedProductPriceChangedNotification');
        }
        if (is_null($manufacturer->send_ordered_product_amount_changed_notification)) {
            $manufacturer->send_ordered_product_amount_changed_notification = Configure::read('app.defaultSendOrderedProductAmountChangedNotification');
        }

        if (!$this->identity->isManufacturer()) {
            $this->Customer = $this->getTableLocator()->get('Customers');
        }

        $this->setFormReferer();

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) {
            $this->SyncDomain = $this->getTableLocator()->get('Network.SyncDomains');
            $this->viewBuilder()->addHelper('Network.Network');
            $this->set('syncDomainsForDropdown', $this->SyncDomain->getForDropdown());
            $isAllowedEditManufacturerOptionsDropdown = $this->SyncDomain->isAllowedEditManufacturerOptionsDropdown($this->identity);
            $this->set('isAllowedEditManufacturerOptionsDropdown', $isAllowedEditManufacturerOptionsDropdown);
        }

        if (empty($this->getRequest()->getData())) {
            $this->set('manufacturer', $manufacturer);
            return;
        }

        // if checkbox is disabled, false is returned even if checkbox is active
        // as i could not find out how to unset a specific request data index, override with value from database
        if ($this->identity->isManufacturer()) {
            $this->setRequest($this->getRequest()->withData('Manufacturers.active', $manufacturer->active));
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $manufacturer = $this->Manufacturer->patchEntity(
            $manufacturer,
            $this->getRequest()->getData(),
            [
                'validate' => 'editOptions'
            ]
        );

        if ($manufacturer->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('manufacturer', $manufacturer);
            $this->render('edit_options');
        } else {
            // values that are the same as default values => null
            if (!$this->identity->isManufacturer()) {
                // only admins and superadmins are allowed to change variable_member_fee
                if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && $this->getRequest()->getData('Manufacturers.variable_member_fee') == Configure::read('appDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE')) {
                    $this->setRequest($this->getRequest()->withoutData('Manufacturers.variable_member_fee'));
                }
            }
            if ($this->getRequest()->getData('Manufacturers.default_tax_id') == Configure::read('app.defaultTaxId')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.default_tax_id', null));
            }
            if ($this->getRequest()->getData('Manufacturers.default_tax_id_purchase_price') == Configure::read('app.defaultTaxIdPurchasePrice')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.default_tax_id_purchase_price', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_order_list') == Configure::read('app.defaultSendOrderList')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_order_list', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_invoice') == Configure::read('app.defaultSendInvoice')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_invoice', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_instant_order_notification') == Configure::read('app.defaultSendInstantOrderNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_instant_order_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_deleted_notification') == Configure::read('app.defaultSendOrderedProductDeletedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_deleted_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_price_changed_notification') == Configure::read('app.defaultSendOrderedProductPriceChangedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_price_changed_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_amount_changed_notification') == Configure::read('app.defaultSendOrderedProductAmountChangedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_amount_changed_notification', null));
            }

            if (isset($isAllowedEditManufacturerOptionsDropdown) && $isAllowedEditManufacturerOptionsDropdown) {
                if ($this->getRequest()->getData('Manufacturers.enabled_sync_domains')) {
                    $this->setRequest($this->getRequest()->withData('Manufacturers.enabled_sync_domains', implode(',', $this->getRequest()->getData('Manufacturers.enabled_sync_domains'))));
                }
            }

            if (!Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY') && $this->getRequest()->getData('Manufacturers.no_delivery_days')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.no_delivery_days', implode(',', $this->getRequest()->getData('Manufacturers.no_delivery_days'))));
            }

            // remove post data that could theoretically be added
            if ($this->identity->isManufacturer()) {
                $this->setRequest($this->getRequest()->withoutData('Manufacturers.variable_member_fee'));
                $this->setRequest($this->getRequest()->withoutData('Manufacturers.id_customer'));
            }

            // sic! patch again!
            $manufacturer = $this->Manufacturer->patchEntity(
                $manufacturer,
                $this->getRequest()->getData()
            );
            $manufacturer = $this->Manufacturer->save($manufacturer);

            if (!$this->identity->isManufacturer()) {
                $manufacturer = $this->Manufacturer->find('all',
                    conditions: [
                        'Manufacturers.id_manufacturer' => $manufacturer->id_manufacturer,
                    ],
                    contain: [
                        'AddressManufacturers'
                    ])->first();
        
                $customerRecord = $this->Manufacturer->getCustomerRecord($manufacturer->address_manufacturer->email);
                if (!empty($customerRecord)) {
                    $customersTable = $this->getTableLocator()->get('Customers');
                    $customerRecord->active = $manufacturer->active;
                    $customersTable->save($customerRecord);
                }
            }

            $this->getRequest()->getSession()->write('highlightedRowId', $manufacturer->id_manufacturer);

            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getManufacturerProfile()) {
                $this->renewAuthSession();
            }

            $message = __d('admin', 'The_settings_of_manufacturer_{0}_have_been_changed.', ['<b>' . $manufacturer->name . '</b>']);
            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getManufacturerMyOptions()) {
                $message = __d('admin', 'Your_settings_have_been_changed.');
                $this->renewAuthSession();
            }

            $this->Flash->success($message);

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('manufacturer_options_changed', $this->identity->getId(), $manufacturer->id_manufacturer, 'manufacturers', $message);

            $this->redirect($this->getPreparedReferer());
        }

        $this->set('manufacturer', $manufacturer);
    }

}
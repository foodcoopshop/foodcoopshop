<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Services\PdfWriter\InvoiceToManufacturerPdfWriterService;
use App\Services\PdfWriter\OrderListByProductPdfWriterService;
use App\Services\PdfWriter\OrderListByCustomerPdfWriterService;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use App\Services\DeliveryRhythmService;
use Admin\Traits\UploadTrait;
use App\Services\CatalogService;
use App\Services\DeliveryNoteService;
use App\Controller\Traits\RenewAuthSessionTrait;
use App\Model\Table\FeedbacksTable;
use App\Model\Table\OrderDetailsTable;
use App\Model\Table\PaymentsTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\TaxesTable;
use Cake\View\JsonView;
use App\Services\SanitizeService;
use Network\Model\Table\SyncDomainsTable;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class ManufacturersController extends AdminAppController
{
    protected FeedbacksTable $Feedback;
    protected PaymentsTable $Payment;
    protected ProductsTable $Product;
    protected OrderDetailsTable $OrderDetail;
    protected TaxesTable $Tax;
    protected SyncDomainsTable $SyncDomain;

    use UploadTrait;
    use RenewAuthSessionTrait;

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
    }

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

            $this->Customer = $this->getTableLocator()->get('Customers');
            $customerData = [
                'email' => $this->getRequest()->getData('Manufacturers.address_manufacturer.email'),
                'firstname' => $this->getRequest()->getData('Manufacturers.address_manufacturer.firstname'),
                'lastname' => $this->getRequest()->getData('Manufacturers.address_manufacturer.lastname'),
                'active' => APP_ON
            ];
            if (empty($customer)) {
                $customerEntity = $this->Customer->newEntity($customerData);
            } else {
                $customerEntity = $this->Customer->patchEntity($customer, $customerData);
            }
            $this->Customer->save($customerEntity);

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

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $message = __d('admin', 'The_manufacturer_{0}_has_been_{1}.', ['<b>' . $manufacturer->name . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->identity->getId(), $manufacturer->id_manufacturer, 'manufacturers', $message);
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

    public function setElFinderUploadPath($manufacturerId)
    {
        $this->request = $this->request->withParam('_ext', 'json');

        if ($this->identity->isManufacturer()) {
            $manufacturerId = $this->identity->getManufacturerId();
        } else {
            $manufacturer = $this->Manufacturer->find('all', conditions: [
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

    private function getDefaultDate() {
        $defaultDate = '';
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $defaultDate = Configure::read('app.timeHelper')->formatToDateShort(Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        } else {
            $defaultDate = (new DeliveryRhythmService())->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
        }
        return $defaultDate;
    }

    public function index()
    {

        $dateFrom = $this->getDefaultDate();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = $this->getDefaultDate();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $active = 1; // default value
        if (in_array('active', array_keys($this->getRequest()->getQueryParams()))) {
            $active = h($this->getRequest()->getQuery('active'));
        }
        $this->set('active', $active);

        $conditions = [];
        if ($active != 'all') {
            $conditions = [
                'Manufacturers.active' => $active
            ];
        }

        $query = $this->Manufacturer->find('all',
        conditions: $conditions,
        contain: [
            'AddressManufacturers',
            'Customers'
        ])
        ->select($this->Manufacturer)
        ->select($this->Manufacturer->Customers)
        ->select($this->Manufacturer->AddressManufacturers);

        $manufacturers = $this->paginate($query, [
            'sortableFields' => [
                'Manufacturers.name', 'Manufacturers.stock_management_enabled', 'Manufacturers.no_delivery_days', 'Manufacturers.is_private', 'Customers.' . Configure::read('app.customerMainNamePart'),
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ]);

        // extract all email addresses for button
        $emailAddresses = [];
        $emailAddresses = $query->all()->extract('address_manufacturer.email')->toArray();
        $emailAddresses = array_unique($emailAddresses);
        $this->set('emailAddresses', $emailAddresses);

        $this->Product = $this->getTableLocator()->get('Products');
        $this->Payment = $this->getTableLocator()->get('Payments');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Feedback = $this->getTableLocator()->get('Feedbacks');

        foreach ($manufacturers as $manufacturer) {
            $catalogService = new CatalogService();
            $manufacturer->product_count = $catalogService->getProductsByManufacturerId($manufacturer->id_manufacturer, true);
            $sumDepositDelivered = $this->OrderDetail->getDepositSum($manufacturer->id_manufacturer, false);
            $sumDepositReturned = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturer->id_manufacturer, false);
            $manufacturer->sum_deposit_delivered = $sumDepositDelivered[0]['sumDepositDelivered'];
            $manufacturer->deposit_credit_balance = $sumDepositDelivered[0]['sumDepositDelivered'] - $sumDepositReturned[0]['sumDepositReturned'];
            if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
                $manufacturer->variable_member_fee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            }
            if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
                $customer = $this->Manufacturer->getCustomerRecord($manufacturer->address_manufacturer->email);
                if (!empty($customer)) {
                    $manufacturer->feedback = $this->Feedback->find('all', conditions: [
                        'Feedbacks.customer_id' => $customer->id_customer,
                    ])->first();
                }
                $manufacturer->customer_record_id = $customer->id_customer ?? 0;
            }
            $manufacturer->sum_open_order_detail = $this->OrderDetail->getOpenOrderDetailSum($manufacturer->id_manufacturer, $dateFrom);
        }
        $this->set('manufacturers', $manufacturers);

        $this->set('title_for_layout', __d('admin', 'Manufacturers'));
    }

    private function getOptionVariableMemberFee($manufacturerId)
    {
        $manufacturer = $this->Manufacturer->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();
        return $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
    }

    public function myOptions()
    {
        $this->editOptions($this->identity->getManufacturerId());
        $this->set('referer', $this->getRequest()->getUri()->getPath());
        $this->set('title_for_layout', __d('admin', 'Edit_settings'));
        if (empty($this->getRequest()->getData())) {
            $this->render('editOptions');
        }
    }

    public function getDeliveryNote()
    {

        $this->disableAutoRender();

        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        $dateTo = h($this->getRequest()->getQuery('dateTo'));

        $manufacturer = $this->Manufacturer->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getOrderDetailsForDeliveryNotes($manufacturerId, $dateFrom, $dateTo);

        $deliverNoteService = new DeliveryNoteService();
        $spreadsheet = $deliverNoteService->getSpreadsheet($orderDetails);

        $filename = $deliverNoteService->writeSpreadsheetAsFile($spreadsheet, $dateFrom, $dateTo, $manufacturer->name);

        $this->response = $this->response->withHeader('Content-Disposition', 'inline;filename="'.$filename.'"');
        $this->response = $this->response->withFile(TMP . $filename);

        $deliverNoteService->deleteTmpFile($filename);

        return $this->response;

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

    public function getInvoice()
    {
        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        $dateTo = h($this->getRequest()->getQuery('dateTo'));

        $manufacturer = $this->Manufacturer->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();

        $newInvoiceNumber = 'xxx';

        $pdfWriter = new InvoiceToManufacturerPdfWriterService();
        $pdfWriter->prepareAndSetData($manufacturerId, $dateFrom, $dateTo, $newInvoiceNumber, [], '', 'xxx', $manufacturer->anonymize_customers);
        if (isset($pdfWriter->getData()['productResults']) && empty($pdfWriter->getData()['productResults'])) {
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }

        if (!empty($this->request->getQuery('outputType')) && $this->request->getQuery('outputType') == 'html') {
            return $this->response->withStringBody($pdfWriter->writeHtml());
        }

        $invoicePdfFile = Configure::read('app.htmlHelper')->getInvoiceLink($manufacturer->name, $manufacturerId, date('Y-m-d'), $newInvoiceNumber);
        $invoicePdfFile = explode(DS, $invoicePdfFile);
        $invoicePdfFile = end($invoicePdfFile);
        $invoicePdfFile = substr($invoicePdfFile, 11);
        $invoicePdfFile = $this->request->getQuery('dateFrom'). '-' . $this->request->getQuery('dateTo') . '-' . $invoicePdfFile;
        $pdfWriter->setFilename($invoicePdfFile);

        die($pdfWriter->writeInline());
    }

    private function getOrderListFilenameForWriteInline($manufacturerId, $manufacturerName, $pickupDay, $type): string
    {
        $currentDateForOrderLists = Configure::read('app.timeHelper')->getCurrentDateTimeForFilename();
        $productPdfFile = Configure::read('app.htmlHelper')->getOrderListLink($manufacturerName, $manufacturerId, $pickupDay, $type, $currentDateForOrderLists, false);
        $productPdfFile = explode(DS, $productPdfFile);
        $productPdfFile = end($productPdfFile);
        $productPdfFile = substr($productPdfFile, 11);
        $productPdfFile = $pickupDay . '-' . $productPdfFile;
        return $productPdfFile;
    }

    public function getOrderListByProduct()
    {

        $pdfWriter = new OrderListByProductPdfWriterService();
        return $this->getOrderList('product', $pdfWriter);
    }

    public function getOrderListByCustomer()
    {
        $pdfWriter = new OrderListByCustomerPdfWriterService();
        return $this->getOrderList('customer', $pdfWriter);
    }

    protected function getOrderList($type, $pdfWriter)
    {

        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        $pickupDay = h($this->getRequest()->getQuery('pickupDay'));
        $pickupDayDbFormat = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);

        $manufacturer = $this->Manufacturer->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();

        if (!in_array('isAnonymized', array_keys($this->getRequest()->getQueryParams()))) {
            $isAnonymized = $manufacturer->anonymize_customers;
        } else {
            $isAnonymized = h($this->getRequest()->getQuery('isAnonymized'));
        }

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getOrderDetailsForOrderListPreview($pickupDayDbFormat);
        $orderDetails->where(['Products.id_manufacturer' => $manufacturerId]);
        $orderDetailIds = $orderDetails->all()->extract('id_order_detail')->toArray();

        if (empty($orderDetailIds)) {
            // do not throw exception because no debug mails wanted
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }

        if ($type == 'product') {
            $typeString = __d('admin', 'product');
        } else {
            $typeString = __d('admin', 'member');
        }

        $pdfFile = $this->getOrderListFilenameForWriteInline($manufacturerId, $manufacturer->name, $pickupDay, $typeString);
        $pdfWriter->setFilename($pdfFile);

        $pdfWriter->prepareAndSetData($manufacturerId, $pickupDayDbFormat, [], $orderDetailIds, $isAnonymized);
        if (!empty($this->request->getQuery('outputType')) && $this->request->getQuery('outputType') == 'html') {
            return $this->response->withStringBody($pdfWriter->writeHtml());
        }

        die($pdfWriter->writeInline());

    }

}
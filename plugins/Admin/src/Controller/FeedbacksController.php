<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use App\Services\SanitizeService;
use Cake\I18n\DateTime;
use App\Model\Entity\Customer;

/**
* FoodCoopShop - The open source software for your foodcoop
*
* Licensed under the GNU Affero General Public License version 3
* For full copyright and license information, please see LICENSE
* Redistributions of files must retain the above copyright notice.
*
* @since         FoodCoopShop 3.5.0
* @license       https://opensource.org/licenses/AGPL-3.0
* @author        Mario Rothauer <office@foodcoopshop.com>
* @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
* @link          https://www.foodcoopshop.com
*/

class FeedbacksController extends AdminAppController
{

    public int $customerId;
    public bool $isOwnForm;

    private function getCustomerId(): int
    {
        $customerId = '';
        if (!empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = (int) h($this->getRequest()->getQuery('customerId'));
        }
        if ($this->customerId > 0) {
            $customerId = $this->customerId;
        }
        return $customerId;
    }

    private function getCustomer(): ?Customer
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $customer = $customersTable->find('all',
        conditions: [
            'Customers.id_customer' => $this->customerId,
        ],
        contain: [
            'AddressCustomers',
        ])->first();
        return $customer;
    }

    public function myFeedback(): void
    {
        $this->customerId = $this->identity->getId();
        $this->set('title_for_layout', __d('admin', 'My_feedback'));
        $this->isOwnForm = true;
        $this->_processForm();
        if (empty($this->getRequest()->getData())) {
            $this->render('form');
        }
    }

    public function form(int $customerId): void
    {
        $this->customerId = $customerId;
        $customer = $this->getCustomer();
        if (empty($customer)) {
            throw new RecordNotFoundException('customer ' . $customerId . ' not found');
        }
        $name = $customer->name;
        $customersTable = $this->getTableLocator()->get('Customers');
        $manufacturer = $customersTable->getManufacturerByCustomerId($this->customerId);
        if ($manufacturer !== null) {
            $name = $manufacturer->name;
        }
        $this->set('title_for_layout', __d('admin', 'Feedback_from_{0}', [$name]));
        $this->isOwnForm = false;
        $this->_processForm();
        if (empty($this->getRequest()->getData())) {
            $this->render('form');
        }
    }

    public function _processForm(): void
    {

        $customerId = $this->getCustomerId();
        $this->set('isOwnForm', $this->isOwnForm);

        $feedbacksTable = $this->getTableLocator()->get('Feedbacks');
        $customersTable = $this->getTableLocator()->get('Customers');

        $customer = $this->getCustomer();
        $this->set('customer', $customer);

        $manufacturer = $customersTable->getManufacturerByCustomerId($this->customerId);
        $isManufacturer = false;
        if ($manufacturer !== null) {
            $isManufacturer = true;
            $privacyTypes = $feedbacksTable->getManufacturerPrivacyTypes($manufacturer);
        } else {
            $privacyTypes = $feedbacksTable->getCustomerPrivacyTypes($customer);
        }
        $this->set('privacyTypes', $privacyTypes);
        $this->set('isManufacturer', $isManufacturer);

        $feedback = $feedbacksTable->find('all',
        conditions: [
            'Feedbacks.customer_id' => $customerId,
        ],
        contain: [
            'Customers',
        ])->first();

        if (!empty($feedback) && $this->identity->isSuperadmin()) {
            $feedback->approved_checkbox = $feedbacksTable->isApproved($feedback);
        }

        $isEditMode = !empty($feedback);
        $this->set('isEditMode', $isEditMode);

        $this->setCurrentFormAsFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('feedback', $feedback);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['text'])));

        if (!$isEditMode) {
            $feedback = $feedbacksTable->newEntity(
                $this->getRequest()->getData(),
                [
                    'validate' => 'edit',
                ],
            );
        } else {
            $feedback = $feedbacksTable->patchEntity(
                $feedback,
                $this->getRequest()->getData(),
                [
                    'validate' => 'edit',
                ],
            );
        }

        if ($feedback->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('feedback', $feedback);
            $this->render('form');
        } else {

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $userNameForActionLog = !empty($manufacturer) ? $manufacturer->name : $customer->name;

            if (!$isEditMode) {
                $feedback->customer_id = $this->getCustomerId();
            }

            if (!empty($this->getRequest()->getData('Feedbacks.delete_feedback'))) {
                $feedbacksTable->delete($feedback);
                $actionLogType = 'user_feedback_deleted';
                if ($this->isOwnForm) {
                    $message = __d('admin', 'Your_feedback_has_been_{0}.', [
                        __d('admin', 'deleted'),
                    ]);
                } else {
                    $message = __d('admin', 'The_feedback_of_{0}_has_been_{1}.', [
                        '<b>' . $userNameForActionLog . '</b>',
                        __d('admin', 'deleted'),
                    ]);
                }
                $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $feedback->id, 'feedbacks', $message);
                $this->Flash->success($message);
                $this->redirect($this->getPreparedReferer());
                return;
            }

            $oldFeedback = clone $feedback;
            $valueForApproved = DateTime::now();
            $valueForNotApproved = DateTime::createFromDate(1970, 01, 01);

            $feedback->approved = $valueForApproved;
            if ($feedback->isDirty('text') && !($this->identity->isAdmin() || $this->identity->isSuperadmin())) {
                $feedback->approved = $valueForNotApproved;
            }

            if ($isEditMode && $this->identity->isSuperadmin()) {
                $feedback->approved = $valueForNotApproved;
                if ($feedback->approved_checkbox) {
                    $wasApproved = $feedbacksTable->isApproved($oldFeedback);
                    $feedback->approved = $valueForApproved;
                    if (!$wasApproved) {
                        $actionLogType = 'user_feedback_approved';
                        $message = __d('admin', 'The_feedback_of_{0}_has_been_{1}.', [
                            '<b>' . $userNameForActionLog . '</b>',
                            __d('admin', 'approved'),
                        ]);
                        $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $feedback->id, 'feedbacks', $message);
                    }
                }
            }

            if (!($this->identity->isAdmin() || $this->identity->isSuperadmin())) {
                $feedback->approved = $valueForNotApproved;
            }

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'user_feedback_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'user_feedback_changed';
            }

            if ($this->isOwnForm) {
                $message = __d('admin', 'Your_feedback_has_been_{0}.', [
                    $messageSuffix,
                ]);
            } else {
                $message = __d('admin', 'The_feedback_of_{0}_has_been_{1}.', [
                    '<b>' . $userNameForActionLog . '</b>',
                    $messageSuffix,
                ]);
            }

            $isDirty = $feedback->isDirty('text') || $feedback->isDirty('privacy_type');
            $feedback = $feedbacksTable->save($feedback);
            if (!$isEditMode || $isDirty) {
                $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $feedback->id, 'feedbacks', $message);
            }

            $this->Flash->success($message);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('feedback', $feedback);

    }


}

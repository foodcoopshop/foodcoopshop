<?php
declare(strict_types=1);

namespace Admin\Controller;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Datasource\Exception\RecordNotFoundException;

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

    public $customerId;
    public $isOwnForm;

    public function isAuthorized($user)
    {
        return match($this->getRequest()->getParam('action')) {
            'myFeedback' => Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $this->AppAuth->user(),
             default => Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $this->AppAuth->isSuperadmin(),
        };
    }

    private function getCustomerId()
    {
        $customerId = '';
        if (!empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        } if ($this->customerId > 0) {
            $customerId = $this->customerId;
        }
        return $customerId;
    }

    private function getCustomer()
    {
        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $this->customerId,
            ],
            'contain' => [
                'AddressCustomers',
            ],
        ])->first();
        return $customer;
    }

    public function myFeedback()
    {
        $this->customerId = $this->AppAuth->getUserId();
        $this->set('title_for_layout', __d('admin', 'My_feedback'));
        $this->isOwnForm = true;
        $this->_processForm();
        if (empty($this->getRequest()->getData())) {
            $this->render('form');
        }
    }

    public function form($customerId)
    {
        $this->customerId = $customerId;
        $customer = $this->getCustomer();
        if (empty($customer)) {
            throw new RecordNotFoundException('customer ' . $customerId . ' not found');
        }
        $name = $customer->name;
        $manufacturer = $this->Customer->getManufacturerByCustomerId($this->customerId);
        if (!empty($manufacturer)) {
            $name = $manufacturer->name;
        }
        $this->set('title_for_layout', __d('admin', 'Feedback_from_{0}', [$name]));
        $this->isOwnForm = false;
        $this->_processForm();
        if (empty($this->getRequest()->getData())) {
            $this->render('form');
        }
    }

    public function _processForm()
    {

        $customerId = $this->getCustomerId();
        $this->set('isOwnForm', $this->isOwnForm);

        $this->Feedback = $this->getTableLocator()->get('Feedbacks');

        $customer = $this->getCustomer();
        $this->set('customer', $customer);

        $manufacturer = $this->Customer->getManufacturerByCustomerId($this->customerId);
        $isManufacturer = false;
        if (!empty($manufacturer)) {
            $isManufacturer = true;
            $privacyTypes = $this->Feedback->getManufacturerPrivacyTypes($manufacturer);
        } else {
            $privacyTypes = $this->Feedback->getCustomerPrivacyTypes($customer);
        }
        $this->set('privacyTypes', $privacyTypes);
        $this->set('isManufacturer', $isManufacturer);

        $feedback = $this->Feedback->find('all', [
            'conditions' => [
                'Feedbacks.customer_id' => $customerId,
            ],
            'contain' => [
                'Customers',
            ]
        ])->first();

        if (!empty($feedback) && $this->AppAuth->isSuperadmin()) {
            $feedback->approved_checkbox = $this->Feedback->isApproved($feedback);
        }

        $isEditMode = !empty($feedback);
        $this->set('isEditMode', $isEditMode);

        $this->setCurrentFormAsFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('feedback', $feedback);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['text'])));

        if (!$isEditMode) {
            $feedback = $this->Feedback->newEntity(
                $this->getRequest()->getData(),
                [
                    'validate' => 'edit',
                ],
            );
        } else {
            $feedback = $this->Feedback->patchEntity(
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

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $userNameForActionLog = !empty($manufacturer) ? $manufacturer->name : $customer->name;

            if (!$isEditMode) {
                $feedback->customer_id = $this->getCustomerId();
            }

            if (!empty($this->getRequest()->getData('Feedbacks.delete_feedback'))) {
                $this->Feedback->delete($feedback);
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
                $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $feedback->id, 'feedbacks', $message);
                $this->Flash->success($message);
                $this->redirect($this->getPreparedReferer());
                return;
            }

            $oldFeedback = clone $feedback;
            $valueForApproved = FrozenTime::now();
            $valueForNotApproved = FrozenTime::createFromDate(1970, 01, 01);

            $feedback->approved = $valueForApproved;
            if ($feedback->isDirty('text') && !($this->AppAuth->isAdmin() || $this->AppAuth->isSuperadmin())) {
                $feedback->approved = $valueForNotApproved;
            }

            if ($isEditMode && $this->AppAuth->isSuperadmin()) {
                $feedback->approved = $valueForNotApproved;
                if ($feedback->approved_checkbox) {
                    $wasApproved = $this->Feedback->isApproved($oldFeedback);
                    $feedback->approved = $valueForApproved;
                    if (!$wasApproved) {
                        $actionLogType = 'user_feedback_approved';
                        $message = __d('admin', 'The_feedback_of_{0}_has_been_{1}.', [
                            '<b>' . $userNameForActionLog . '</b>',
                            __d('admin', 'approved'),
                        ]);
                        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $feedback->id, 'feedbacks', $message);
                    }
                }
            }

            if (!($this->AppAuth->isAdmin() || $this->AppAuth->isSuperadmin())) {
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
            $feedback = $this->Feedback->save($feedback);
            if (!$isEditMode || $isDirty) {
                $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $feedback->id, 'feedbacks', $message);
            }

            $this->Flash->success($message);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('feedback', $feedback);

    }


}

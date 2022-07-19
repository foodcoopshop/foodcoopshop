<?php
namespace Admin\Controller;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

/**
* FoodCoopShop - The open source software for your foodcoop
*
* Licensed under the GNU Affero General Public License version 3
* For full copyright and license information, please see LICENSE
* Redistributions of files must retain the above copyright notice.
*
* @since         FoodCoopShop 1.1.0
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
        switch ($this->getRequest()->getParam('action')) {
            case 'myFeedback':
                return Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED');
                break;
            default:
                return Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $this->AppAuth->isSuperadmin();
                break;
        }
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
        $this->render('form');
    }

    public function form($customerId)
    {
        $this->customerId = $customerId;
        $customer = $this->getCustomer();
        $this->set('title_for_layout', __d('admin', 'Feedback_from_{0}', [
            $customer->name,
        ]));
        $this->isOwnForm = false;
        $this->_processForm();
        $this->render('form');
    }

    public function _processForm()
    {

        $customerId = $this->getCustomerId();
        $this->set('isOwnForm', $this->isOwnForm);

        $this->Feedback = $this->getTableLocator()->get('Feedbacks');

        $customer = $this->getCustomer();
        $this->set('customer', $customer);

        $privacyTypes = $this->Feedback->getPrivacyTypesForDropdown($customer);
        $this->set('privacyTypes', $privacyTypes);

        $feedback = $this->Feedback->find('all', [
            'conditions' => [
                'Feedbacks.customer_id' => $customerId,
            ],
            'contain' => [
                'Customers',
            ]
        ])->first();

        $this->setCurrentFormAsFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('feedback', $feedback);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['text'])));

        if (empty($feedback)) {
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
        } else {
            $feedback->customer_id = $this->getCustomerId();
            $feedback->approved = FrozenTime::createFromDate(1970, 01, 01);
            if (!empty($this->getRequest()->getData('Feedbacks.approved_checkbox'))) {
                $feedback->approved = FrozenTime::now();
            }
            $this->Feedback->save($feedback);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('feedback', $feedback);

    }


}

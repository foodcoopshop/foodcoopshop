<?php
namespace Admin\Controller;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;

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
        $this->set('title_for_layout', __d('admin', 'Feedback'));
        $this->isOwnForm = false;
        $this->_processForm();
        $this->render('form');
    }

    public function _processForm()
    {

        $customerId = $this->getCustomerId();
        $this->set('isOwnForm', $this->isOwnForm);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId,
            ],
            'contain' => [
                'Feedbacks',
                'Manufacturers',
            ]
        ])->first();

        $this->setFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('customer', $customer);
            return;
        }

        $this->set('title_for_layout', __d('admin', 'Feedback_form'));
    }


}

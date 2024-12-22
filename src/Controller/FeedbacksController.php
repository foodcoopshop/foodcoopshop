<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

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
class FeedbacksController extends FrontendController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'index',
        ]);
    }
    
    public function index()
    {

        if (!Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED')) {
            throw new NotFoundException('feedbacks not found');
        }

        if ($this->request->getUri()->getPath() == '/feedbacks') {
            $this->redirect(Configure::read('app.slugHelper')->getFeedbackList());
        }

        $feedbacksTable = TableRegistry::getTableLocator()->get('Feedbacks');
        $customersTable = TableRegistry::getTableLocator()->get('Customers');

        $feedbacks = $feedbacksTable->find('all',
            conditions: [
                'DATE_FORMAT(Feedbacks.approved, \'%Y-%m-%d\') <> \'1970-01-01\'',
                'Customers.active' => APP_ON,
            ],
            contain: [
                'Customers.AddressCustomers',
            ],
            order: [
                'Feedbacks.approved' => 'DESC',
            ],
        )->toArray();

        $preparedFeedbacks = [
            'customers' => [],
            'manufacturers' => [],
        ];
        foreach($feedbacks as &$feedback) {
            $manufacturer = $customersTable->getManufacturerByCustomerId($feedback->customer_id);
            if (!empty($manufacturer)) {
                $feedback->manufacturer = $manufacturer;
                $feedback->privatized_name = $feedbacksTable->getManufacturerPrivacyType($feedback);
                if ($manufacturer->active == APP_ON) {
                    $preparedFeedbacks['manufacturers'][] = $feedback;
                }
            } else {
                $feedback->privatized_name = $feedbacksTable->getCustomerPrivacyType($feedback);
                $preparedFeedbacks['customers'][] = $feedback;
            }
        }
        $this->set('feedbacks', $preparedFeedbacks);

        $this->set('title_for_layout', __('Feedback'));
    }

}

<?php
declare(strict_types=1);

namespace App\Controller;

use App\Services\OrderCustomerService;
use App\Services\OutputFilter\OutputFilterService;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use hisorange\BrowserDetect\Parser as Browser;
use Cake\Http\Response;

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
class AppController extends Controller
{
#
    public bool $protectEmailAddresses = false;
    public mixed $identity = null;
    public bool $formProtectionEnabled = true;

    public function initialize(): void
    {

        parent::initialize();

        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Flash', [
            'clear' => true
        ]);
        $this->loadComponent('String');

        $this->paginate = [
            'limit' => 300000,
            'maxLimit' => 300000
        ];
    }

    public function beforeFilter(EventInterface $event): void
    {

        $identity = $this->getRequest()->getAttribute('identity');
        $this->identity = $identity;
        $this->set('identity', $identity);

        $orderCustomerService = new OrderCustomerService();
        $this->set('orderCustomerService', $orderCustomerService);

        if (!$this->getRequest()->is('json') && !$orderCustomerService->isOrderForDifferentCustomerMode()) {
            $this->loadComponent('FormProtection');
        }

        $isMobile = false;
        if (PHP_SAPI !== 'cli') {
            /** @phpstan-ignore-next-line */
            $isMobile = Browser::isMobile() && !Browser::isTablet();
        }
        $this->set('isMobile', $isMobile);

        parent::beforeFilter($event);
    }

    public function afterFilter(EventInterface $event): void
    {
        parent::afterFilter($event);

        $newOutput = $this->response->getBody()->__toString();
        if ($this->protectEmailAddresses) {
            $newOutput = OutputFilterService::protectEmailAdresses($newOutput);
        }
        
        if (Configure::check('app.outputStringReplacements')) {
            $newOutput = OutputFilterService::replace($newOutput, Configure::read('app.outputStringReplacements'));
        }
        $this->response = $this->response->withStringBody($newOutput);
    }

    public function getPreparedReferer(): string
    {
        return htmlspecialchars_decode($this->getRequest()->getData('referer'));
    }

    public function setCurrentFormAsFormReferer(): void
    {
        $this->set('referer', $this->getRequest()->getUri()->getPath());
    }

    public function setFormReferer(): void
    {
        $this->set('referer', !empty($this->getRequest()->getData('referer')) ? $this->getRequest()->getData('referer') : $this->referer());
    }

    /**
     * can be used for returning exceptions as json
     * try {
     *      $this->foo->bar();
     *  } catch (Exception $e) {
     *      return $this->sendAjaxError($e);
     *  }
     */
    protected function sendAjaxError($error): Response
    {
        if ($this->getRequest()->is('json')) {
            $this->setResponse($this->getResponse()->withStatus(500));
            $response = [
                'status' => APP_OFF,
                'msg' => $error->getMessage()
            ];
            $this->set(compact('response'));
            $this->render('/Error/errorjson');
        }
        return $this->response;
    }

}

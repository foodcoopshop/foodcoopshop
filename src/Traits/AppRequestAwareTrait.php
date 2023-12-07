<?php
declare(strict_types=1);

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
namespace App\Traits;

use Cake\Core\Configure;

trait AppRequestAwareTrait
{

    public $appRequest;

    public function setAppRequest($request)
    {
        $this->appRequest = $request;
    }

    public function isLoggedIn()
    {
        return $this->appRequest->getAttribute('identity') !== null;
    }

        // TODO REFACTOR AUTH
        // move in customer entity
    protected function isAdmin(): bool
    {
        return $this->isLoggedIn() && $this->appRequest->getAttribute('identity')->isAdmin();
    }

    public function getLoggedUser($field = null)
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        if ($field === null) {
            return $this->appRequest->getAttribute('identity');
        }

        return $this->appRequest->getAttribute('identity')->get($field);
    }

    public function isOrderForDifferentCustomerMode()
    {
        return $this->appRequest->getSession()->read('Auth.orderCustomer');
    }

    public function isSelfServiceModeByUrl()
    {
        $result = $this->appRequest->getPath() == '/' . __('route_self_service');
        if (!empty($this->appRequest->getQuery('redirect'))) {
            $result |= preg_match('`' . '/' . __('route_self_service') . '`', $this->appRequest->getQuery('redirect'));
        }
        return $result;
    }

    public function isSelfServiceModeByReferer()
    {
        $result = false;
        $serverParams = $this->appRequest->getServerParams();
        $requestUriAllowed = [
            '/' . __('route_cart') . '/ajaxAdd/',
            '/' . __('route_cart') . '/ajaxRemove/'
        ];
        if (isset($serverParams['HTTP_REFERER'])) {
            $result = preg_match('`' . preg_quote(Configure::read('App.fullBaseUrl')) . '/' . __('route_self_service') . '`', $serverParams['HTTP_REFERER']);
        }
        if (!in_array($serverParams['REQUEST_URI'], $requestUriAllowed)) {
            $result = false;
        }
        return $result;
    }    

}
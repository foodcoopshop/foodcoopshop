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
namespace App\Services\Traits;

trait RequestAwareTrait
{

    public $request;

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function isLoggedIn()
    {
        return $this->request->getAttribute('identity') !== null;
    }

    public function getLoggedUser($field = null)
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        if ($field === null) {
            return $this->request->getAttribute('identity');
        }

        return $this->request->getAttribute('identity')->get($field);
    }

    public function isOrderForDifferentCustomerMode()
    {
        return $this->request->getSession()->read('Auth.orderCustomer');
    }

    public function isSelfServiceModeByUrl()
    {
        $result = $this->request->getPath() == '/' . __('route_self_service');
        if (!empty($this->request->getQuery('redirect'))) {
            $result |= preg_match('`' . '/' . __('route_self_service') . '`', $this->request->getQuery('redirect'));
        }
        return $result;
    }

}
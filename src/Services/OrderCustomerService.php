<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services;

use Cake\Core\Configure;
use Cake\Routing\Router;

class OrderCustomerService
{

    public function isOrderForDifferentCustomerMode(): bool
    {
        return Router::getRequest()->getSession()->check('OrderIdentity');
    }

    public function isSelfServiceMode(): bool
    {
        return $this->isSelfServiceModeByUrl() || $this->isSelfServiceModeByReferer();
    }

    public function isSelfServiceModeByUrl(): bool
    {
        $result = Router::getRequest()->getPath() == '/' . __('route_self_service');
        if (!empty(Router::getRequest()->getQuery('redirect'))) {
            $result = (bool) preg_match('`' . '/' . __('route_self_service') . '`', Router::getRequest()->getQuery('redirect'));
        }
        return $result;
    }

    public function isSelfServiceModeByReferer(): bool
    {
        $result = false;
        $serverParams = Router::getRequest()->getServerParams();
        $requestUriAllowed = [
            '/' . __('route_cart') . '/ajaxAdd/',
            '/' . __('route_cart') . '/ajaxRemove/'
        ];
        if (isset($serverParams['HTTP_REFERER'])) {
            $result = preg_match(
                '`' . preg_quote(Configure::read('App.fullBaseUrl')) . '/' . __('route_self_service') . '`',
                $serverParams['HTTP_REFERER'],
            );
            $result = (bool) $result;
        }
        if (!in_array($serverParams['REQUEST_URI'], $requestUriAllowed)) {
            $result = false;
        }
        return $result;
    }

}
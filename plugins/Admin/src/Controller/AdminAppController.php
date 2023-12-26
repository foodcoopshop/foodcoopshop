<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Controller\AppController;

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

class AdminAppController extends AppController
{

    protected $ActionLog;

    public function isAuthorized($user)
    {
        return $this->identity->isLoggedIn();
    }

    public function setReferer()
    {
        $this->set('referer', ! empty($this->getRequest()->getData('referer')) ? $this->getRequest()->getData('referer') : $this->referer());
    }

}

<?php
namespace Admin\Controller;

use Cake\ORM\Query;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class CronjobsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin();
    }

    public function index()
    {
        $this->Cronjobs = $this->getTableLocator()->get('Cronjobs');
        $cronjobs = $this->Cronjobs->find('available');

        $cronjobs->contain([
            'CronjobLogs' => function (Query $q) {
                $q->orderDesc('CronjobLogs.created');
                return $q;
            }
        ]);

        $this->set('cronjobs', $cronjobs);
        $this->set('title_for_layout', __d('admin', 'Cronjobs'));
    }

}

<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Authorization\IdentityInterface;
use Cake\ORM\TableRegistry;

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
class PagesPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        switch ($request->getParam('action')) {
            case 'detail':
                $pageId = (int) $request->getParam('idAndSlug');
                $pagesTable = TableRegistry::getTableLocator()->get('Pages');
                $page = $pagesTable->find('all',
                    conditions: [
                        'Pages.id_page' => $pageId,
                        'Pages.active' => APP_ON,
                    ]
                )->first();
                if (!empty($page) && $identity === null && $page->is_private) {
                    return false;
                }
                break;
            case 'discourseSso':
                if ($identity === null) {
                    return false;
                }
                break;
        }

        return true;

    }

}
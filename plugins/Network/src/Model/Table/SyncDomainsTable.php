<?php
declare(strict_types=1);

namespace Network\Model\Table;

use App\Model\Table\AppTable;
use Cake\Validation\Validator;
use Cake\Utility\Hash;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SyncDomainsTable extends AppTable
{

    public function validationDefault(Validator $validator): Validator
    {
        $validator->add('domain', 'hostname', [
            'rule' => ['custom', HOSTNAME_REGEX],
            'message' => __d('network', 'The_domain_may_only_consist_of_https://_and_the_hostname_(no_trailing_slash).')
        ]);
        $validator->notEmptyString('domain', 'Bitte gib eine Domain ein, sie muss mit https:// beginnen.');
        $validator->add('domain', 'https', [
            'rule' => ['custom', HTTPS_REGEX],
            'message' => __d('network', 'The_domain_needs_to_start_with_https://.')
        ]);
        $validator->add('domain', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => __d('network', 'The_domain_already_exists.')
        ]);
        return $validator;
    }

    public function getSyncDomains($minStatus = APP_OFF)
    {
        $syncDomains = $this->find('all', conditions: [
            'SyncDomains.active >= ' . $minStatus
        ]);
        return $syncDomains;
    }

    public function getActiveSyncDomains()
    {
        return $this->getSyncDomains(APP_ON);
    }

    public function getActiveSyncDomainHosts()
    {
        $syncDomains = $this->getActiveSyncDomains()->toArray();
        if (empty($syncDomains)) {
            return [];
        }
        $syncDomains = Hash::extract($syncDomains, '{n}.domain');
        $syncDomainHosts = array_map(function ($syncDomain) {
            return parse_url($syncDomain, PHP_URL_HOST);
        }, $syncDomains);
        return $syncDomainHosts;
    }

    public function isAllowedEditManufacturerOptionsDropdown($identity)
    {

        $isAllowed = false;
        if ($identity->isSuperadmin()) {
            $isAllowed = true;
        }

        if ($identity->isManufacturer()) {
            $manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
            $isAllowed = $manufacturersTable->getOptionVariableMemberFee(
                $identity->getManufacturerVariableMemberFee()
            ) == 0;
        }

        if ($isAllowed) {
            $syncDomains = $this->getActiveSyncDomains();
            $isAllowed &= $syncDomains->count() > 0;
        }

        return $isAllowed;
    }

    public function getActiveManufacturerSyncDomains($enabledSyncDomains)
    {

        if (is_null($enabledSyncDomains)) {
            return [];
        }

        $activeSyncDomains = $this->getActiveSyncDomains();
        $preparedDomains = [];
        $enabledSyncDomainsAsArray = explode(',', $enabledSyncDomains);
        foreach ($activeSyncDomains as $activeSyncDomain) {
            if (in_array($activeSyncDomain->id, $enabledSyncDomainsAsArray)) {
                $preparedDomains[] = $activeSyncDomain;
            }
        }

        return $preparedDomains;

    }

    public function getForDropdown()
    {
        $syncDomains = $this->find('all',
        conditions: [
            'SyncDomains.active' => APP_ON
        ],
        fields: ['SyncDomains.id', 'SyncDomains.domain']);
        $result = [];
        foreach ($syncDomains as $syncDomain) {
            $result[$syncDomain->id] = $syncDomain->domain;
        }
        return $result;
        
    }
}

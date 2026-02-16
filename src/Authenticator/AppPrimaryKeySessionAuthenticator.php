<?php
declare(strict_types=1);

namespace App\Authenticator;

use ArrayAccess;
use Authentication\Authenticator\PrimaryKeySessionAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppPrimaryKeySessionAuthenticator extends PrimaryKeySessionAuthenticator
{

    /**
     * Authenticate a user using session data.
     * 
     * Handles migration from old sessions that stored the full entity
     * to new sessions that store only the primary key integer.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to authenticate with.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $sessionKey = $this->getConfig('sessionKey');
        /** @var \Cake\Http\Session $session */
        $session = $request->getAttribute('session');

        $sessionData = $session->read($sessionKey);
        if (!$sessionData) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        }

        // Old sessions stored the full entity object instead of just the ID.
        // Extract the primary key and rewrite the session to the new format.
        if ($sessionData instanceof ArrayAccess || is_array($sessionData)) {
            $idField = $this->getConfig('idField');
            $userId = $sessionData[$idField] ?? null;
            if ($userId === null) {
                $session->delete($sessionKey);
                return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
            }
            $session->write($sessionKey, $userId);
        }

        return parent::authenticate($request);
    }

}

<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services;

class RemoteFileService
{
    public static function exists(string $remoteFile, $allowedHosts = []): bool
    {

        self::verifyAllowedHosts($allowedHosts, $remoteFile);

        $ch = curl_init($remoteFile);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseCode == 200){
            return true;
        }

        return false;

    }

    private static function verifyAllowedHosts($allowedHosts, $remoteFile): void
    {
        if (empty($allowedHosts)) {
            throw new \Exception('allowedHosts must be set');
        } else {
            $host = parse_url($remoteFile, PHP_URL_HOST);
            if (!in_array($host, $allowedHosts)) {
                throw new \Exception('invalid host');
             }
        }
    }

}

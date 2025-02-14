<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use Cake\TestSuite\EmailTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class BackupDatabaseCommandTest extends AppCakeTestCase
{

    public function testBackup(): void
    {

        $backupDir = ROOT . DS . 'files_private' . DS . 'db-backups';
        $this->purgeFolderWithGitignoreFile($backupDir);

        $this->exec('backup_database test');

        $files = scandir($backupDir);
        $found = false;
        foreach ($files as $file) {
            if (preg_match('/foodcoopshop-test(.*).bz2/', $file)) {
                $filesize = filesize($backupDir . DS . $file);
                $this->assertGreaterThan(0, $filesize);
                $found = true;
            }
        }
        $this->assertTrue($found);
        $this->purgeFolderWithGitignoreFile($backupDir);

    }

}

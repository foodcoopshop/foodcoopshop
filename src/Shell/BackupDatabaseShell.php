<?php
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

namespace App\Shell;

use Cake\Mailer\Mailer;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\I18n\Number;

class BackupDatabaseShell extends AppShell
{

    public function main()
    {
        parent::main();

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '256M');

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');

        $this->startTimeLogging();

        $dbConfig = ConnectionManager::getConfig('default');

        $backupdir = ROOT . DS . 'files_private' . DS . 'db-backups';
        $filename = 'db-backup-' . date('Y-m-d_H-i-s', time()) . '.sql';

        if (! is_dir($backupdir)) {
            $this->out(' ', 1);
            $this->out('Will create "' . $backupdir . '" directory!');
            if (mkdir($backupdir, 0755, true)) {
                $this->out('Directory created!');
            }
        }

        $configFile = TMP . 'mysql.txt';
        $configFileObject = new File($configFile);
        $configFileContent = '[mysqldump]
host=%host%
user=%user%
password="%password%"
';
        $configFileContent = str_replace(['%host%', '%user%', '%password%'], [$dbConfig['host'], $dbConfig['username'], $dbConfig['password']], $configFileContent);
        if (isset($dbConfig['port'])) {
            $configFileContent .= 'port=' . $dbConfig['port'];
        }

        $configFileObject->write($configFileContent);

        $cmdString = Configure::read('app.mysqlDumpCommand');
        $cmdString .= " --defaults-file=" . $configFile . " --allow-keywords --add-drop-table --ignore-table=" . $dbConfig['database'] . ".queued_jobs --complete-insert --no-tablespaces --quote-names " . $dbConfig['database'] . " > " . $backupdir . DS . $filename;
        exec($cmdString);

        $configFileObject->delete();

        // START zip and file sql file
        $zip = new \ZipArchive();
        $zipFilename = str_replace('.sql', '.zip', $backupdir . DS . $filename);
        $zip->open($zipFilename, \ZipArchive::CREATE);
        $zip->addFile($backupdir . DS . $filename, $filename); // 2nd param for no folders in zip file
        $zip->close();
        unlink($backupdir . DS . $filename);
        // END zip and delete sql file

        $message = __('Database_backup_successful') . ' ('.Number::toReadableSize(filesize($zipFilename)).').';

        // email zipped file via Mailer (to avoid queue's max 16MB mediumtext limit of AppMailer)
        $email = new Mailer(false);
        $email->setProfile('debug');
        $email->setTo(Configure::read('app.hostingEmail'))
            ->setSubject($message . ': ' . Configure::read('app.cakeServerName'))
            ->setAttachments([
              $zipFilename
            ])
            ->send();

        $this->out($message);

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_backup_database', 0, 0, '', $message . '<br />' . $this->getRuntime());
        $this->out($this->getRuntime());

        return true;

    }
}

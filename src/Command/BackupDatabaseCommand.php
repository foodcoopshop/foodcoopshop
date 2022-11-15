<?php
declare(strict_types=1);

namespace App\Command;

use App\Controller\Component\StringComponent;
use Cake\Mailer\Mailer;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Number;
use Ifsnop\Mysqldump as IMysqldump;

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
class BackupDatabaseCommand extends AppCommand
{

    public $ActionLog;

    public function execute(Arguments $args, ConsoleIo $io)
    {

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '256M');

        $this->startTimeLogging();

        $dbConfig = ConnectionManager::getConfig('default');

        $backupdir = ROOT . DS . 'files_private' . DS . 'db-backups';
        $preparedHostWithoutProtocol = Configure::read('app.htmlHelper')->getHostWithoutProtocol(Configure::read('App.fullBaseUrl'));
        $preparedHostWithoutProtocol = str_replace('www.', '', $preparedHostWithoutProtocol);
        $preparedHostWithoutProtocol = StringComponent::slugify($preparedHostWithoutProtocol);
        $filename = $backupdir . DS . $preparedHostWithoutProtocol . '-' . date('Y-m-d_H-i-s', time()) . '.bz2';

        if (! is_dir($backupdir)) {
            $io->out(' ', 1);
            $io->out('Will create "' . $backupdir . '" directory!');
            if (mkdir($backupdir, 0755, true)) {
                $io->out('Directory created!');
            }
        }

        $dsnString = "mysql:host=". $dbConfig['host'].";dbname=".$dbConfig['database'];
        if (isset($dbConfig['port'])) {
            $dsnString .= ";port=".$dbConfig['port'];
        }

        $settings = [
            'default-character-set' => IMysqldump\Mysqldump::UTF8MB4,
            'add-drop-table' => true,
            'compress' => IMysqldump\Mysqldump::BZIP2,
            'exclude-tables' => [
                'queued_jobs',
            ],
        ];
        $dump = new IMysqldump\Mysqldump($dsnString, $dbConfig['username'], $dbConfig['password'], $settings);
        $dump->start($filename);

        $message = __('Database_backup_successful') . ' ('.Number::toReadableSize(filesize($filename)).').';

        // email zipped file via Mailer (to avoid queue's max 16MB mediumtext limit of AppMailer)
        $email = new Mailer(false);
        $email->setProfile('debug');
         $email->setTo(Configure::read('app.hostingEmail'))
            ->setSubject($message . ': ' . Configure::read('App.fullBaseUrl'))
            ->setAttachments([
                $filename
            ])
            ->send();
        $io->out($message);

        $this->stopTimeLogging();

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('cronjob_backup_database', 0, 0, '', $message . '<br />' . $this->getRuntime());
        $io->out($this->getRuntime());

        return static::CODE_SUCCESS;

    }

}
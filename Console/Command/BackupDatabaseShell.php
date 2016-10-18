<?php
/**
 * BackupDatabaseShell
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class BackupDatabaseShell extends AppShell
{

    public $uses = array(
        'CakeActionLog'
    );

    public function main()
    {
        parent::main();
        
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '256M');
        
        $this->startTimeLogging();
        
        $this->initSimpleBrowser(); // for loggedUserId
                                    
        App::uses('ConnectionManager', 'Model');
        $dbConfig = ConnectionManager::getDataSource('default')->config;
        
        // tables whose data should not be dumped
        $ignoredTables = array(
            $dbConfig['prefix'] . 'connections',
            $dbConfig['prefix'] . 'connections_page',
            $dbConfig['prefix'] . 'connections_source',
            $dbConfig['prefix'] . 'page_viewed',
            $dbConfig['prefix'] . 'pagenotfound',
            $dbConfig['prefix'] . 'guest',
            $dbConfig['prefix'] . 'order_invoice',
            $dbConfig['prefix'] . 'order_invoice_payment',
            $dbConfig['prefix'] . 'sekeyword',
            $dbConfig['prefix'] . 'search_word',
            $dbConfig['prefix'] . 'search_index'
        );
        
        $backupdir = APP . DS . 'files_private' . DS . 'db-backups';
        $filename = 'db-backup-' . date('Y-m-d_H-i-s', time()) . '.sql';
        
        if (! is_dir($backupdir)) {
            $this->out(' ', 1);
            $this->out('Will create "' . $backupdir . '" directory!');
            if (mkdir($backupdir, 0755, true)) {
                $this->out('Directory created!');
            }
        }
        
        $ignoredTableString = ' ';
        foreach ($ignoredTables as $ignoredTable) {
            $ignoredTableString .= '--ignore-table=' . $dbConfig['database'] . '.' . $ignoredTable . ' ';
        }
        
        $cmdString = Configure::read('app.mysqlDumpCommand');
        $cmdString .= " -u " . $dbConfig['login'] . " -p" . $dbConfig['password'] . " --allow-keywords " . $ignoredTableString . " --add-drop-table --complete-insert --quote-names " . $dbConfig['database'] . " > " . $backupdir . DS . $filename;
        exec($cmdString);
        
        // START zip and file sql file
        $zip = new ZipArchive();
        $zipFilename = str_replace('.sql', '.zip', $backupdir . DS . $filename);
        $zip->open($zipFilename, ZipArchive::CREATE);
        $zip->addFile($backupdir . DS . $filename, $filename); // 2nd param for no folders in zip file
        $zip->close();
        unlink($backupdir . DS . $filename);
        // END zip and delete sql file
        
        $message = 'Datenbank-Backup erfolgreich.';
        
        // email zipped file
        App::uses('CakeEmail', 'Network/Email');
        $Email = new CakeEmail(Configure::read('debugEmailConfig'));
        $Email->to(Configure::read('app.hostingEmail'))
            ->subject($message . ': ' . Configure::read('app.cakeServerName'))
            ->attachments(array(
            $zipFilename
        ))
            ->send();
        
        $this->out($message);
        
        $this->stopTimeLogging();
        
        $this->CakeActionLog->customSave('cronjob_backup_database', $this->browser->getLoggedUserId(), 0, '', $message . '<br />' . $this->getRuntime());
        $this->out($this->getRuntime());
    }
}
?>
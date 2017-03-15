<?php

App::uses('Component', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class DbMigrationComponent extends Component {
    
    /*
     * The owning controller
     */
    protected $controller = null;

    /*
     * Do the database migrations
     *
     * Database migrations works with a configuration table entry as a numeric
     * version number. The DB version is shown in the admin interface as a
     * read-only setting. There is a complementary "last update try" number
     * added to the configuration table, which is marked inactive and thus
     * hidden from the admin settings interface. Executed DB migrations are
     * logged in the action log, but there is no user stated.
     *
     * If there is no configuration table entry with the DB version, the start
     * of the auto-migrations is assumed and update to version 0 is executed.
     * This update is executed alone, then
     *
     * Initial version is 0, versions are unsigned ints afterwards. Gaps are
     * tolerated, but not recommended. Any SQL file having a numeric-only file
     * name in migrations folder [See getDbMigrationsFolder()] and an 'sql'
     * extension will be used in numeric sequence for updating [see
     * getDbMigrationsVersions()].
     *
     * There is a lot of affort to take to prevent endless looping on update
     * failures. And because DataSource::query() doesn't report errors to the
     * caller, but kills execution instead, there is even more to do. So
     * prevention of endless loops on failing updates is implemented like this:
     * - To prevent updating set a non-numeric DB version in configuration
     * - Any unrecoverable error that does no "Oooops" uses the above
     * - SQL Errors are caught by setting an inactive DB update marker in the
     *   configuration and comparing to the real DB version. On mismatch do set
     *   a non-numeric DB version
     *
     * @return  bool    true -> did DB update, false -> not done, maybe failed
     */
    public function doDbMigrations($controller)
    {
        $db = Configure::read('app.db_config_FCS_DB_VERSION');
        if (strlen($db) == 0) {  // the DB version config value doesn't exist
            $avail = array('0'); // do the very first DB migration
        }
        else if (!is_numeric($db)) {
            // on a previous fail, do not retry but inform user
            $controller->AppSession->setFlashError('DB update error');
            return false;
        }
        else {
            $avail = $this->getDbMigrationsVersions($db);
        }
        unset($db);

        if (empty($avail)) {
            return false;
        }

        $this->controller = $controller;

        if ($avail[0] !== '0') {
            $conf = $controller->Configuration->find('first', array(
                'conditions' => array(
                    'Configuration.name' => 'FCS_DB_VERSION'
                )
            ));
            $tried = $controller->Configuration->find('first', array(
                'conditions' => array(
                    'Configuration.name' => 'FCS_DB_UPDATE'
                )
            ));

            // catch unsuccessful update attempt
            if ($conf['Configuration']['value'] != $tried['Configuration']['value']) {
                $this->logDbMigrationsFailure(
                    $conf['Configuration']['value'],
                    $tried['Configuration']['value']
                );

                // prevent endless looping on unrecoverable error
                $conf['Configuration']['value'] = 'SQL not executed ' . $tried['Configuration']['value'];
                $controller->Configuration->save($conf, array('validate' => false));

                // inform user
                $controller->AppSession->setFlashError('DB update error');
                return false;
            }
        }
        else {
            $conf = false;
            $tried = false;
        }

        foreach ($avail as $migration) {

            // note the initial version before doing the actual update
            if (is_array($conf)) {
                $from = $conf['Configuration']['value'];
            }
            else {
                $from = '---';
            }

            // note the destination version before doing the actual update
            if (is_array($tried)) {
                $tried['Configuration']['value'] = $migration;
                $controller->Configuration->save($tried, array('validate' => false));
            }

            $file = new File(Configure::read('app.folder.migrations') . DS . $migration . '.sql');

            if (!$file->readable()) {
                if (is_array($conf)) {
                    $this->logDbMigrationsFailure(
                        $conf['Configuration']['value'],
                        $migration
                    );

                    // prevent endless looping on unrecoverable error
                    $conf['Configuration']['value'] = 'Cannot Read File ' . $migration;
                    $controller->Configuration->save($conf, array('validate' => false));
                }
                else {
                    $this->logDbMigrationsFailure(
                        '---',
                        $migration
                    );
                }

                // inform user
                $controller->AppSession->setFlashError('DB update error');
                return false;
            }

            $sql = $file->read();
            $file->close();
            unset($file);

            // Doing schema update as one transaction prevents from partially
            // executed updates. They are rolled back automatically on errors.
            // Adding the DB version update into the transaction allows for
            // execution control as query() doesn't report errors.
            $sql = 'START TRANSACTION;'
                . PHP_EOL
                . $sql
                . PHP_EOL
                . 'UPDATE `fcs_configuration` SET `value` = \''
                . $migration
                . '\' WHERE `fcs_configuration`.`name` = \'FCS_DB_VERSION\';'
                . PHP_EOL
                . 'COMMIT;'
                . PHP_EOL
                ;

            $controller->Configuration->query($sql);

            // now try to get the updated version number
            $conf = $controller->Configuration->find('first', array(
                'conditions' => array(
                    'Configuration.name' => 'FCS_DB_VERSION'
                )
            ));

            if (is_array($conf)) {
                // catch unsuccessful update attempt
                if ($conf['Configuration']['value'] != $migration) {
                    $this->logDbMigrationsFailure(
                        $conf['Configuration']['value'],
                        $migration
                    );

                    // prevent endless looping on unrecoverable error
                    $conf['Configuration']['value'] = 'SQL not executed ' . $migration;
                    $controller->Configuration->save($conf, array('validate' => false));

                    // inform user
                    $controller->AppSession->setFlashError('DB update error');
                    return false;
                }
            }
            else {
                $this->logDbMigrationsFailure($from, $migration);

                // inform user
                $controller->AppSession->setFlashError('DB update error');
                return false;
            }

            Cache::clear();
            $this->logDbMigrationsSuccess($from, $migration);
        }

        // inform user
        $controller->AppSession->setFlashMessage(
            sprintf('Die Datenbank wurde soeben auf Version %1$s aktualisiert.', (int)$migration)
        );
        return true;
    }

    /*
     * Get the list of database migrations to execute
     *
     * @return  array   list of existing numeric versions to execute
     */
    protected function getDbMigrationsVersions($activeVersion)
    {
        $result = array();
        $activeVersion = (int)$activeVersion;
        $dir = new Folder(Configure::read('app.folder.migrations'));
        $files = $dir->find('^[0-9]+\.sql$');
        unset($dir);

        foreach ($files as $key => $file) {
            $thisVersion = (int)basename($file, '.sql');
            if ($thisVersion > $activeVersion) {
                $result[] = $thisVersion;
            }
        }
        unset($files, $key, $file, $thisVersion);

        sort($result, SORT_NUMERIC);
        return $result;
    }

    /*
     * Log DB update failure
     */
    protected function logDbMigrationsFailure($activeVersion, $triedVersion)
    {
        $this->controller->loadModel('CakeActionLog');
        $this->controller->CakeActionLog->customSave(
            'superadmin_deploy_failed',
            0,  // user id illegal
            0,
            '',
            sprintf(
                'Die Datenbank konnte nicht von "Version %1$s" aktualisiert werden auf <i>"Version %2$s"</i>',
                $activeVersion,
                $triedVersion
            )
        );
    }

    /*
     * Log DB update success
     */
    protected function logDbMigrationsSuccess($activeVersion, $triedVersion)
    {
        $this->controller->loadModel('CakeActionLog');
        $this->controller->CakeActionLog->customSave(
            'superadmin_deploy_successful',
            0,  // user id illegal
            0,
            '',
            sprintf(
                'Die Datenbank wurde von "Version %1$s" aktualisiert auf <i>"Version %2$s"</i>',
                $activeVersion,
                $triedVersion
            )
        );
    }
}
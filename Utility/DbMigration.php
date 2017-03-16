<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class DbMigration {
    /*
     * The stdOut method. Must accept a single string to inform user about something.
     */
    protected $stdOut = null;

    /*
     * The stdErr method. Must accept a single string to inform user about something.
     */
    protected $stdErr = null;

    /*
     * The stdLog method. Must accept a string for log type and a string to write into log.
     */
    protected $stdLog = null;

    /*
     * The findConf method. Must accept a string for the config name and return an array of table values.
     */
    protected $findConf = null;

    /*
     * The saveConf method. Must accept an array of table values.
     */
    protected $saveConf = null;

    /*
     * The query method. Must accept a SQL string.
     */
    protected $query = null;

/**
 * Constructor.
 *
 * @param Callable $stdOut The stdOut method
 */
    public function __construct($stdOut = null, $stdErr = null, $stdLog = null, $findConf = null, $saveConf = null, $query = null) {
        $this->stdOut = $stdOut;
        $this->stdErr = $stdErr;
        $this->stdLog = $stdLog;
        $this->findConf = $findConf;
        $this->saveConf = $saveConf;
        $this->query = $query;
    }

    protected function toStdOut($string = '') {
        if (is_callable($this->stdOut)) {
            return call_user_func($this->stdOut, $string);
        }
        return false;
    }

    protected function toStdErr($string = '') {
        if (is_callable($this->stdErr)) {
            return call_user_func($this->stdErr, $string);
        }
        return false;
    }

    protected function toStdLog($type = '', $string = '') {
        if (is_callable($this->stdLog)) {
            return call_user_func($this->stdLog, $type, $string);
        }
        return false;
    }

    protected function doFindConf($string = '') {
        if (is_callable($this->findConf)) {
            return call_user_func($this->findConf, $string);
        }
        return array();
    }

    protected function doSaveConf(array $conf = array()) {
        if (is_callable($this->saveConf)) {
            return call_user_func($this->saveConf, $conf);
        }
        return false;
    }

    protected function doQuery($string = '') {
        if (is_callable($this->query)) {
            return call_user_func($this->query, $string);
        }
        return false;
    }

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
     * @return  int    -1 -> update failed, DB now unusable, prevent from usage
     *                 -2 -> nothing done, but DB unusable, prevent from usage
     *                 0  -> nothing done, go on
     *                 1  -> update successful, restart
     */
    public function doDbMigrations()
    {
        $db = Configure::read('app.db_config_FCS_DB_VERSION');
        if (strlen($db) == 0) {  // the DB version config value doesn't exist
            $avail = array('0'); // do the very first DB migration
        }
        else if (!is_numeric($db)) {
            // on a previous fail, do not retry but inform user
            $this->toStdErr('DB update error');
            return -2;
        }
        else {
            $avail = $this->getDbMigrationsVersions($db);
        }
        unset($db);

        if (empty($avail)) {
            return 0;
        }

        if ($avail[0] !== '0') {
            $conf = $this->doFindConf('FCS_DB_VERSION');
            $tried = $this->doFindConf('FCS_DB_UPDATE');

            // catch unsuccessful update attempt
            if ($conf['Configuration']['value'] != $tried['Configuration']['value']) {
                $this->logDbMigrationsFailure(
                    $conf['Configuration']['value'],
                    $tried['Configuration']['value']
                );

                // prevent endless looping on unrecoverable error
                $conf['Configuration']['value'] = 'SQL not executed ' . $tried['Configuration']['value'];
                $this->doSaveConf($conf);

                // inform user
                $this->toStdErr('DB update error');
                return -1;
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
                $this->doSaveConf($tried);
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
                    $this->doSaveConf($conf);
                }
                else {
                    $this->logDbMigrationsFailure(
                        '---',
                        $migration
                    );
                }

                // inform user
                $this->toStdErr('DB update error');
                return -1;
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

            $this->doQuery($sql);

            // now try to get the updated version number
            $conf = $this->doFindConf('FCS_DB_VERSION');

            if (is_array($conf)) {
                // catch unsuccessful update attempt
                if ($conf['Configuration']['value'] != $migration) {
                    $this->logDbMigrationsFailure(
                        $conf['Configuration']['value'],
                        $migration
                    );

                    // prevent endless looping on unrecoverable error
                    $conf['Configuration']['value'] = 'SQL not executed ' . $migration;
                    $this->doSaveConf($conf);

                    // inform user
                    $this->toStdErr('DB update error');
                    return -1;
                }
            }
            else {
                $this->logDbMigrationsFailure($from, $migration);

                // inform user
                $this->toStdErr('DB update error');
                return -1;
            }

            Cache::clear();
            $this->logDbMigrationsSuccess($from, $migration);
        }

        // inform user
        $this->toStdOut(
            sprintf('Die Datenbank wurde soeben auf Version %1$s aktualisiert.', (int)$migration)
        );
        return 1;
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
        $this->toStdLog(
            'superadmin_deploy_failed',
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
        $this->toStdLog(
            'superadmin_deploy_successful',
            sprintf(
                'Die Datenbank wurde von "Version %1$s" aktualisiert auf <i>"Version %2$s"</i>',
                $activeVersion,
                $triedVersion
            )
        );
    }
}

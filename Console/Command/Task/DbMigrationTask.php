<?php

App::uses('DbMigration', 'Utility');

class DbMigrationTask extends Shell {
    /*
     * The DbMigration utility instance
     */
    protected $dbMigration = null;

    public function execute (Shell $shell)
    {
        $shell->loadModel('Configuration');
        $shell->loadModel('CakeActionLog');

        $this->dbMigration = new DbMigration(
            function ($string = '') use (&$shell) { // $stdOut
                return $shell->out($string);
            },
            function ($string = '') use (&$shell) { // $stdErr
                return $shell->out('<error>' . $string . '</error>');
            },
            function ($type = '', $string = '') use (&$shell) { // $stdLog
                return $shell->CakeActionLog->customSave($type, 0, 0, '', $string);
            },
            function ($string = '') use (&$shell) { // $findConf
                return $shell->Configuration->find('first', array(
                    'conditions' => array(
                        'Configuration.name' => $string
                    )
                ));
            },
            function (array $conf = array()) use (&$shell) { // $saveConf
                $shell->Configuration->save($conf, array('validate' => false));
            },
            function ($string = '') use (&$shell) { // $query
                $shell->Configuration->query($string);
            }
        );

    /*
     * Do the database migrations
     */
        return $this->dbMigration->doDbMigrations();
    }
}

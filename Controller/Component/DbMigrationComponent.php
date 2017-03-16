<?php

App::uses('Component', 'Controller');
App::uses('DbMigration', 'Utility');

class DbMigrationComponent extends Component {
    /*
     * The DbMigration utility instance
     */
    protected $dbMigration = null;

    public function initialize(Controller $controller)
    {
        $controller->loadModel('Configuration');
        $controller->loadModel('CakeActionLog');

        $this->dbMigration = new DbMigration(
            function ($string = '') use (&$controller) { // $stdOut
                return $controller->AppSession->setFlashMessage($string);
            },
            function ($string = '') use (&$controller) { // $stdErr
                return $controller->AppSession->setFlashError($string);
            },
            function ($type = '', $string = '') use (&$controller) { // $stdLog
                return $controller->CakeActionLog->customSave($type, 0, 0, '', $string);
            },
            function ($string = '') use (&$controller) { // $findConf
                return $controller->Configuration->find('first', array(
                    'conditions' => array(
                        'Configuration.name' => $string
                    )
                ));
            },
            function (array $conf = array()) use (&$controller) { // $saveConf
                $controller->Configuration->save($conf, array('validate' => false));
            },
            function ($string = '') use (&$controller) { // $query
                $controller->Configuration->query($string);
            }
        );
    }

    /*
     * Do the database migrations
     */
    public function doDbMigrations()
    {
        return $this->dbMigration->doDbMigrations();
    }
}

<?php

App::uses('Component', 'Controller');
App::uses('DbMigration', 'Utility');

/**
 * DbMigrationComponent
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Michael Kramer
 * @copyright     Copyright (c) Michael Kramer, http://k-pd.de
 * @link          https://www.foodcoopshop.com
 */
class DbMigrationComponent extends Component
{
    /*
     * The DbMigration utility instance
     */
    protected $dbMigration = null;

    public function initialize(Controller $controller)
    {
        $controller->loadModel('Configuration');
        $controller->loadModel('CakeActionLog');

        $this->dbMigration = new DbMigration(
            function ($string = '') use (&$controller) {
            // $stdOut
                return $controller->Flash->success($string);
            },
            function ($string = '') use (&$controller) {
            // $stdErr
                return $controller->Flash->error($string);
            },
            function ($type = '', $string = '') use (&$controller) {
            // $stdLog
                return $controller->CakeActionLog->customSave($type, 0, 0, '', $string);
            },
            function ($string = '') use (&$controller) {
            // $findConf
                return $controller->Configuration->find('first', array(
                    'conditions' => array(
                        'Configuration.name' => $string
                    )
                ));
            },
            function (array $conf = array()) use (&$controller) {
            // $saveConf
                $controller->Configuration->save($conf, array('validate' => false));
            },
            function ($string = '') use (&$controller) {
            // $query
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

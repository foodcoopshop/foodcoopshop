<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use App\Utility\DbMigration;

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

    public function initialize(array $config)
    {
        $controller = $this->getController;
        $controller->loadModel('Configuration');
        $controller->loadModel('ActionLog');

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
                return $controller->ActionLog->customSave($type, 0, 0, '', $string);
            },
            function ($string = '') use (&$controller) {
            // $findConf
                return $controller->Configuration->find('first', [
                    'fields' => ['Configuration.id_configuration', 'Configuration.name', 'Configuration.value'],
                    'conditions' => [
                        'Configuration.name' => $string
                    ]
                ]);
            },
            function (array $conf = []) use (&$controller) {
            // $saveConf
                $controller->Configuration->save($conf, ['validate' => false]);
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

<?php

namespace App\Queue\Task;

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait UpdateActionLogTrait
{
    public $ActionLog;

    public function updateActionLogFailure($actionLogId, $identifier, $jobId, $errorMessage)
    {

        $this->ActionLog = $this->loadModel('ActionLogs');

        $search = 'data-identifier="'.$identifier.'"';
        $now = new FrozenTime();
        $now = $now->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
        $replace = 'title="' . $now . ' / JobId: ' . $jobId . ' / ' . h($errorMessage) . '"';

        $query = 'UPDATE ' . $this->ActionLog->getTable() . ' SET text = REPLACE(text, :search, :replace) WHERE id = :actionLogId';
        $params = [
            'actionLogId' => $actionLogId,
            'search' => $search,
            'replace' => $search . $replace,
        ];
        $this->ActionLog->getConnection()->prepare($query)->execute($params);

    }

    public function updateActionLogSuccess($actionLogId, $identifier, $jobId)
    {

        $this->ActionLog = $this->loadModel('ActionLogs');

        $search = 'not-ok" data-identifier="'.$identifier.'"';
        $now = new FrozenTime();
        $now = $now->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'));
        $replace = 'ok" title="' . $now . ' / JobId: ' . $jobId . '"';

        $query = 'UPDATE ' . $this->ActionLog->getTable() . ' SET text = REPLACE(text, :search, :replace) WHERE id = :actionLogId';
        $params = [
            'actionLogId' => $actionLogId,
            'search' => $search,
            'replace' => $replace,
        ];
        $this->ActionLog->getConnection()->prepare($query)->execute($params);

    }

}
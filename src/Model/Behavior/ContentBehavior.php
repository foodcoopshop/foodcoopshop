<?php

App::uses('ModelBehavior', 'Model');

/**
 * ContentBehavior
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
class ContentBehavior extends ModelBehavior
{

    public function beforeSave(Model $model, $options = [])
    {
        $this->saveDateFields($model);
        parent::beforeSave($model, $options);
    }

    public function saveDateFields(Model $model)
    {
        if ((is_null($model->id) || empty($model->id)) && $model->hasField('date_add')) {
            $model->data[$model->name]['date_add'] = Configure::read('AppConfig.timeHelper')->getCurrentDateForDatabase();
        }
        if ($model->hasField('date_upd')) {
            $model->data[$model->name]['date_upd'] = Configure::read('AppConfig.timeHelper')->getCurrentDateForDatabase();
        }
    }

    /**
     * call manually if no data is saved (before save is only triggered if at least one
     * field is changed (assumption)
     *
     * @param Model $model
     */
    public function updateDateUpd(Model $model)
    {
        $model->saveField('date_upd', Configure::read('AppConfig.timeHelper')->getCurrentDateForDatabase());
    }
}

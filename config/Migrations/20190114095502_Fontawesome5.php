<?php
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class Fontawesome5 extends AbstractMigration
{
    public function change()
    {

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $actionLogs = $this->ActionLog->find('all', [
            'conditions' => [
                'ActionLogs.type' => 'cronjob_send_invoices'
            ]
        ]);
        
        $regex = '/\<ul class="jquery-ui-icon"\>\<li class="ui-state-default ui-corner-all"\>\<a href="(.*)" target="_blank"\>\<img src="\/node_modules\/famfamfam-silk\/dist\/png\/arrow_right.png\?(.*)" alt=""\/\>\<\/a\>\<\/li\>\<\/ul\>/siU';
        $replacement = '<a href="$1" class="btn btn-outline-light" target="_blank"><i class="fas fa-arrow-right ok"></i></a>';
        foreach($actionLogs as $actionLog) {
            $newActionLogText = preg_replace($regex, $replacement, $actionLog->text);
            $data2save = [
                'text' => $newActionLogText
            ];
            $this->ActionLog->save(
                $this->ActionLog->patchEntity(
                    $actionLog,
                    $data2save
                )
            );
        }
        
    }
}
